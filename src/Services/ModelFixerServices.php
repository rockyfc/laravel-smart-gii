<?php

namespace Smart\Gii\Services;

use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Doctrine\DBAL\Schema\Column;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\DocBlockFactory;
use phpDocumentor\Reflection\Types\ContextFactory;

class ModelFixerServices
{
    /**
     * model类名
     * @var string
     */
    protected $modelClass;

    /**
     * model类的注释
     * @var string
     */
    protected $modelClassComment;

    /**
     * @var DocBlock
     */
    protected $docblock;

    /**
     * @var \ReflectionClass
     */
    protected $reflector;

    /**
     * @var Column
     */
    protected $tableColumns;

    /**
     * @var AbstractSchemaManager
     */
    protected $tableSchema;

    /**
     * @var Model
     */
    protected $modelInstance;

    public function __construct(string $modelClass)
    {
        $this->modelClass = $modelClass;

        $this->reflector = new \ReflectionClass($modelClass);
        $this->modelClassComment = $this->reflector->getDocComment();

        $factory = DocBlockFactory::createInstance();
        $this->docblock = $factory->create(
            $this->modelClassComment,
            (new ContextFactory())->createFromReflector($this->reflector)
        );
    }

    public function getFileContent()
    {
        $a = new \ReflectionObject($this->getModelInstance());
        //print_r($this->reflector->getMethods());exit;
        //$a = $this->reflector->getMethod('cate')->getReturnType();
        //$a = $this->reflector->getMethod('cate');
        //$a->isInternal();
        /*echo $a->getName();
        return;*/
        print_r(get_class_methods($a));
        exit;
        //var_dump($a->isUserDefined());exit;
        return $this->replace();
    }

    /**
     * 获取model的内容
     * @return string
     */
    public function content()
    {
        return file_get_contents(
            $this->modelPath()
        );
    }

    /**
     * @return false|string
     */
    public function modelPath()
    {
        $ref = new \ReflectionObject($this->getModelInstance());

        return $ref->getFileName();
    }

    protected function allProperties()
    {
        foreach ($this->docblock->getTagsByName('property') as $property) {
            if (!($property instanceof \phpDocumentor\Reflection\DocBlock\Tags\Property)) {
                continue;
            }

            if ($property->getType() instanceof \phpDocumentor\Reflection\Types\Object_) {
                continue;
            }
        }
    }

    protected function replace()
    {
        return $this->replaceContent(
            $this->content()
        );
    }

    protected function replaceContent(string $content)
    {
        $content = trim($content, "\n");
        $pattern = '/\/\*\*([\s\S]*?)\*\/\s*?class /';

        return preg_replace_callback($pattern, function ($matches) {
            print_r($matches);
        }, $content);
    }

    protected function replaceProperty(string $propertyContent)
    {
        return '';
    }

    /**
     * 判断当前的某个属性是否在table中存在
     * @param $property
     * @return bool
     */
    protected function isExistPropertyInTable($property)
    {
        $columns = $this->getTableColumns();

        return isset($columns[$property]);
    }

    /**
     * 获取数据表的Schema信息
     *
     * @return array|Column[]
     */
    protected function getTableColumns()
    {
        if (!$this->tableColumns) {
            $this->tableColumns = $this->getSchema()->listTableColumns(
                $this->getModelInstance()->getTable()
            );
        }

        return $this->tableColumns;
    }

    /**
     * @return mixed|Model
     */
    protected function getModelInstance()
    {
        if (!$this->modelInstance) {
            $this->modelInstance = new $this->modelClass();
        }

        return $this->modelInstance;
    }

    protected function getConnectionName()
    {
        return $this->getModelInstance()->getConnectionName();
    }

    /**
     * 获取\Doctrine\DBAL\Schema\AbstractSchemaManager 实例
     *
     * @return mixed
     */
    protected function getSchema()
    {
        if (null == $this->tableSchema) {
            $connection = DB::connection($this->getConnectionName());
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
}
