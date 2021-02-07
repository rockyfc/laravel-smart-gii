<?php

namespace Smart\Gii\Services;

use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\Index;
use Doctrine\DBAL\Types\Type;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\DB;
use Smart\Gii\Helpers\Dump;

/**
 * 生成一个model类
 *
 * @author Rocky<softfc@163.com>
 */
class ModelCreatorService extends BaseService
{
    /**
     * The filesystem instance.
     *
     * @var Filesystem
     */
    protected $files;

    /**
     * 要创建的model类名
     * @var string
     */
    protected $className;

    /**
     * model的父类
     * @var string
     */
    protected $baseClass;

    /**
     * @var string
     */
    protected $table;

    /**
     * @var string
     */
    protected $connectionName;

    /**
     * @var AbstractSchemaManager
     */
    protected $tableSchema;

    /**
     * @param $modelClassName
     * @param $baseModelClass
     * @param $table
     * @param string $connectionName
     */
    public function __construct($modelClassName, $baseModelClass, $table, $connectionName = 'mysql')
    {
        $this->files = new Filesystem();
        $this->className = $modelClassName;
        $this->baseClass = $baseModelClass;
        $this->table = $table;
        $this->connectionName = $connectionName;
    }

    /**
     * 获取要创建的文件内容
     * @throws FileNotFoundException
     * @return string
     */
    public function getFileContent()
    {
        return $this->sortImports(
            $this->buildClass($this->className)
        );
    }

    /**
     * 替换模板文件中的{{ guarded }}标签
     *
     * @param $stub
     * @return $this
     */
    protected function replaceGuarded(&$stub)
    {
        $column = array_values($this->getTablePrimaryKey() ?? []);

        $tableColumns = array_keys($this->getTableColumns());
        if (in_array(Model::UPDATED_AT, $tableColumns)) {
            $column[] = Model::UPDATED_AT;
        }
        if (in_array(Model::CREATED_AT, $tableColumns)) {
            $column[] = Model::CREATED_AT;
        }

        $str = Dump::value($column, '    ');

        $stub = str_replace(['{{ guarded }}', '{{guarded}}'], $str, $stub);

        return $this;
    }

    /**
     * 替换模板文件中的{{ timestamps }}标签
     *
     * @param $stub
     * @return $this
     */
    protected function replaceTimestamps(&$stub)
    {
        $str = 'false';
        $tableColumns = array_keys($this->getTableColumns());
        if (in_array(Model::UPDATED_AT, $tableColumns)
            and in_array(Model::CREATED_AT, $tableColumns)) {
            $str = 'true';
        }

        $stub = str_replace(['{{ timestamps }}', '{{timestamps}}'], $str, $stub);

        return $this;
    }

    /**
     * @param string $name
     * @throws FileNotFoundException
     * @return string
     */
    protected function buildClass($name)
    {
        $stub = $this->files->get($this->getStub());

        return $this->replaceNamespace($stub)
            ->replaceTableName($stub)
            ->replaceConnection($stub)
            ->replaceProperties($stub)
            ->replaceAttributes($stub)
            //->replaceAttributesConvert($stub)
            ->replaceRules($stub)
            ->replaceGuarded($stub)
            ->replaceTimestamps($stub)
            ->replacePrimaryKey($stub)
            ->replaceAttributesLabels($stub)
            ->replaceBaseClass($stub)
            ->replaceClass($stub);
    }

    /**
     * Replace the namespace for the given stub.
     *
     * @param string $stub
     * @return $this
     */
    protected function replaceNamespace(&$stub)
    {
        $searches = [
            ['DummyNamespace', 'DummyRootNamespace', 'NamespacedDummyUserModel'],
            ['{{ namespace }}', '{{ rootNamespace }}', '{{ namespacedUserModel }}'],
            ['{{namespace}}', '{{rootNamespace}}', '{{namespacedUserModel}}'],
        ];

        foreach ($searches as $search) {
            $stub = str_replace(
                $search,
                [$this->getNamespace($this->className), $this->rootNamespace(), $this->userProviderModel()],
                $stub
            );
        }

        return $this;
    }

    /**
     * Get the full namespace for a given class, without the class name.
     *
     * @param string $name
     * @return string
     */
    protected function getNamespace($name)
    {
        return trim(implode('\\', array_slice(explode('\\', $name), 0, -1)), '\\');
    }

    /**
     * Get the root namespace for the class.
     *
     * @return string
     */
    protected function rootNamespace()
    {
        return app()->getNamespace();
    }

    /**
     * Get the model for the default guard's user provider.
     *
     * @return null|string
     */
    protected function userProviderModel()
    {
        $config = app()['config'];

        $provider = $config->get('auth.guards.' . $config->get('auth.defaults.guard') . '.provider');

        return $config->get("auth.providers.{$provider}.model");
    }

    /**
     * 替换模板文件中的"{{ attributesLabels }}"标签
     *
     * @param $stub
     * @return $this
     */
    protected function replaceAttributesLabels(&$stub)
    {
        $columns = $this->getTableColumns();
        $attributes = [];
        foreach ($columns as $column) {
            $attributes[$column->getName()] = $column->getComment();
        }

        $str = $this->arrayToString($attributes, $prefix = '    ');
        $stub = str_replace(['{{ attributesLabels }}', '{{attributesLabels}}'], $str, $stub);

        return $this;
    }

    /**
     * 替换掉模板里面的"{{ baseClassName }}" 和"{{ baseClass }}"标签
     *
     * @param $stub
     * @return $this
     */
    protected function replaceBaseClass(&$stub)
    {
        $stub = str_replace(['{{ baseClass }}', '{{baseClass}}'], $this->baseClass, $stub);

        $modelClassName = str_replace($this->getNamespace($this->baseClass) . '\\', '', $this->baseClass);
        $stub = str_replace(['{{ baseClassName }}', '{{baseClassName}}'], $modelClassName, $stub);

        return $this;
    }

    /**
     * 替换模板文件中的"{{ attributesConvert }}"标签
     *
     * @param $stub
     * @return $this
     * @deprecated
     */
    protected function replaceAttributesConvert(&$stub)
    {
        $columns = $this->getTableColumns();
        $attributes = [];
        foreach ($columns as $column) {
            $name = $this->spellCheck($column->getName());
            $attributes[$name] = $column->getName();
        }

        $str = $this->arrayToString($attributes, $prefix = '    ');
        $stub = str_replace(['{{ attributesConvert }}', '{{attributesConvert}}'], $str, $stub);

        return $this;
    }

    /**
     * 拼写检查
     *
     * @param $word
     * @return string|string[]
     */
    protected function spellCheck($word)
    {
        $name = $this->toUnderScore($word);

        $link = pspell_new('en');
        if (true == pspell_check($link, $name)) {
            return $name;
        }
        $array = pspell_suggest($link, $name);
        if (!$array) {
            return $name;
        }

        $name = strtolower($name);
        foreach ($array as $k => $word) {
            $word = strtolower($word);
            if (preg_match('/ /', $word) and str_ireplace(' ', '', $word) === $name) {
                return str_ireplace(' ', '_', $word);
            }
        }

        return $name;
    }

    /**
     * 替换模板中得"{{ primaryKey }}"标签
     *
     * @param $stub
     * @return $this
     */
    protected function replacePrimaryKey(&$stub)
    {
        $pk = $this->getTablePrimaryKey();
        $str = '';
        if (is_array($pk)) {
            if (1 == count($pk)) {
                $str = $pk[0];
            } else {
                $str = '';
                foreach ($pk as $val) {
                    $str .= "'" . $val . "',";
                }
                $str = '[' . trim($str, ',') . ']';
            }
        }
        $stub = str_replace(['{{ primaryKey }}', '{{primaryKey}}'], $str, $stub);

        return $this;
    }

    /**
     * 替换掉模板里面的"{{ class }}"标签
     *
     * @param string $stub
     * @return string
     */
    protected function replaceClass($stub)
    {
        $class = str_replace($this->getNamespace($this->className) . '\\', '', $this->className);

        return str_replace(['DummyClass', '{{ class }}', '{{class}}'], $class, $stub);
    }

    /**
     * 替换掉模板里面的"{{ rules }}"标签
     *
     * @param $stub
     * @return $this
     */
    protected function replaceRules(&$stub)
    {
        $columns = $this->getTableColumns();

        $rules = [];
        foreach ($columns as $column) {
            if (true == $column->getAutoincrement()) {
                continue;
            }
            $rule = [];
            if (true == $column->getNotnull()) {
                $rule[] = 'required';
            }

            if (in_array($column->getType()->getName(), [Type::TEXT, Type::STRING])) {
                $rule[] = 'string';
            }

            if (in_array($column->getType()->getName(), [Type::INTEGER, Type::BIGINT, Type::SMALLINT])) {
                $rule[] = 'integer';
            }

            if (in_array($column->getType()->getName(), [Type::DECIMAL, Type::FLOAT])) {
                $rule[] = 'numeric';
            }

            if (Type::DATETIME == $column->getType()->getName()) {
                $rule[] = 'datetime';
            }

            if (Type::DATE == $column->getType()->getName()) {
                $rule[] = 'date';
            }

            if ('email' === strtolower($column->getName())) {
                $rule[] = 'email:rfc,dns';
            }

            if (Type::BOOLEAN == $column->getType()->getName()) {
                $rule[] = 'boolean';
            }

            if (Type::STRING == $column->getType()->getName()) {
                $rule[] = 'max:' . $column->getLength();
            }

            //$rules[$column->getName()] = implode('|', $rule);
            $rules[$column->getName()] = $rule;
        }

        $str = $this->rulesArrayToString($rules);
        $stub = str_replace('{{ rules }}', $str, $stub);

        return $this;
    }

    /**
     * 将rules规则转化成可输出的字符串
     * @param $rules
     * @param string $prefix
     * @return string
     */
    protected function rulesArrayToString($rules, $prefix = '    ')
    {
        $str = "[\n";
        foreach ($rules as $name => $val) {
            $str .= $prefix . "        '" . $name . "' => [";
            foreach ($val as $k => $v) {
                $str .= "'" . $v . "', ";
            }
            $str = rtrim($str, ', ');
            $str .= "],\n";
        }
        $str .= $prefix . "    ]\n";

        return rtrim($str, ",\n");
    }

    /**
     * 覆盖模板里的"{{ attributes }}"标签
     *
     * @param $stub
     * @return $this
     */
    protected function replaceAttributes(&$stub)
    {
        $columns = $this->getTableColumns();

        $attributes = [];
        foreach ($columns as $column) {
            if (true == $column->getAutoincrement()) {
                continue;
            }

            if (null === $column->getDefault() or Type::DATETIME == $column->getType()->getName()) {
                $value = null;
            } else {
                $value = $column->getDefault();
            }
            $attributes[$column->getName()] = ['value' => $value, 'type' => $column->getType()->getName()];
        }

        $str = $this->arrayToString($attributes);
        $stub = str_replace('{{ attributes }}', $str, $stub);

        return $this;
    }

    /**
     * 替换模板中的"{{ table }}"标签
     *
     * @param $stub
     * @return $this
     */
    protected function replaceTableName(&$stub)
    {
        $stub = str_replace(['{{ table }}', '{{table}}'], $this->table, $stub);

        return $this;
    }

    /**
     * 替换模板中的"{{ connection }}"标签
     *
     * @param $stub
     * @return $this
     */
    protected function replaceConnection(&$stub)
    {
        $stub = str_replace(['{{ connection }}', '{{connection}}'], $this->connectionName, $stub);

        return $this;
    }

    /**
     * 替换模板文件中的"{{ properties }}"标签为具体内容
     *
     * @param mixed $stub
     * @return string
     */
    protected function replaceProperties(&$stub)
    {
        $columns = $this->getTableColumns();

        $str = "/**\n";
        foreach ($columns as $col) {
            $type = trim($this->formatToPhpType($col->getType()), '\\');
            $str .= " * @property {$type} $" . $col->getName() . " {$col->getComment()}\n";
        }
        $str .= ' */';
        $stub = str_replace(['{{ properties }}', '{{properties}}'], $str, $stub);

        return $this;
    }

    /**
     * 获取\Doctrine\DBAL\Schema\AbstractSchemaManager 实例
     *
     * @return mixed
     */
    protected function getSchema()
    {
        if (null == $this->tableSchema) {
            $connection = DB::connection($this->connectionName);
            $this->tableSchema = $connection->getDoctrineSchemaManager();

            try {
                $this->tableSchema->getDatabasePlatform()
                    ->registerDoctrineTypeMapping('enum', 'string');
                $this->tableSchema->getDatabasePlatform()
                    ->registerDoctrineTypeMapping('tinyint', 'integer');
            } catch (DBALException $e) {
            }
        }

        return $this->tableSchema;
    }

    /**
     * 获取数据表的Schema信息
     *
     * @return array|Column[]
     */
    protected function getTableColumns()
    {
        return $this->getSchema()->listTableColumns($this->table);
    }

    /**
     * 获取数据表的索引信息
     *
     * @return Index[]
     */
    protected function getTableIndexes()
    {
        return $this->getSchema()->listTableIndexes($this->table);
    }

    /**
     * 获取表主键
     * @return array|string[]
     */
    protected function getTablePrimaryKey()
    {
        $indexes = $this->getTableIndexes();
        foreach ($indexes as $index) {
            if ($index->isPrimary()) {
                return $index->getColumns();
            }
        }
    }

    /**
     * 将数组转化成带有一定格式的字符串
     *
     * @param array $array
     * @param string $prefix
     * @return string
     */
    protected function arrayToString(array $array, $prefix = '')
    {
        $str = "[\r\n";
        foreach ($array as $k => $val) {
            if (null === $val or (is_array($val) and null === $val['value'])) {
                $str .= $prefix . "        '" . $k . "' => " . "null,\r\n";

                continue;
            }

            if (is_string($val)) {
                $str .= $prefix . "        '" . $k . "' => '" . $val . "',\r\n";

                continue;
            }

            if (is_array($val) and Type::STRING == $val['type']) {
                $str .= $prefix . "        '" . $k . "' => '" . $val['value'] . "',\r\n";

                continue;
            }

            if (is_array($val) and in_array($val['type'], [
                Type::INTEGER,
                Type::BIGINT,
                Type::SMALLINT,
                Type::FLOAT,
                Type::DECIMAL,
            ])) {
                $str .= $prefix . "        '" . $k . "' => " . $val['value'] . ",\r\n";

                continue;
            }
        }
        $str .= $prefix . '    ]';

        return $str;
    }

    /**
     * 获取模板文件地址
     *
     * @return string
     */
    protected function getStub()
    {
        return $this->resolveStubPath('/stubs/api.model.stub');
    }

    /**
     * Resolve the fully-qualified path to the stub.
     *
     * @param string $stub
     * @return string
     */
    protected function resolveStubPath($stub)
    {
        return file_exists($customPath = app()->basePath(trim($stub, '/')))
            ? $customPath
            : __DIR__ . '/../..' . $stub;
    }

    /**
     * 驼峰命名转下划线命名
     * @param $str
     * @return string
     */
    private function toUnderScore($str)
    {
        $dstr = preg_replace_callback('/([A-Z]+)/', function ($matchs) {
            return '_' . strtolower($matchs[0]);
        }, $str);

        return trim(preg_replace('/_{2,}/', '_', $dstr), '_');
    }
}
