<?php


namespace Smart\Gii\Services;

use Barryvdh\Reflection\DocBlock;
use Barryvdh\Reflection\DocBlock\Context;
use Barryvdh\Reflection\DocBlock\Serializer as DocBlockSerializer;
use Barryvdh\Reflection\DocBlock\Tag;
use Doctrine\DBAL\Exception;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use phpDocumentor\Reflection\Types\ContextFactory;
use ReflectionClass;
use ReflectionException;
use ReflectionObject;
use Smart\Gii\Traits\ModelTrait;

class ModelFixerServices
{
    use ModelTrait;

    protected const RELATION_TYPES = [
        'hasMany' => HasMany::class,
        'hasManyThrough' => HasManyThrough::class,
        'hasOneThrough' => HasOneThrough::class,
        'belongsToMany' => BelongsToMany::class,
        'hasOne' => HasOne::class,
        'belongsTo' => BelongsTo::class,
        'morphOne' => MorphOne::class,
        'morphTo' => MorphTo::class,
        'morphMany' => MorphMany::class,
        'morphToMany' => MorphToMany::class,
        'morphedByMany' => MorphToMany::class,
    ];

    protected $modelClass;

    /**
     * @var ReflectionClass
     */
    protected $reflection;

    /**
     * @var Filesystem
     */
    protected $file;

    /**
     * ModelResolverService constructor.
     * @param string $modelClass
     * @throws ReflectionException
     * @throws BindingResolutionException
     */
    public function __construct(string $modelClass)
    {
        $this->modelClass = $modelClass;
        $this->reflection = new ReflectionClass($modelClass);
        $this->file = new Filesystem();
        $this->modelInstance();

    }

    /**
     * 是否完全重置注释内容
     * @var bool
     */
    protected $isReset = false;

    /**
     * @param bool $isReset
     * @return $this
     */
    public function setIsReset(bool $isReset = false)
    {
        $this->isReset = $isReset;
        return $this;
    }


    public function fix()
    {
        //根据phpdoc对象重新设置model的类注释
        return $this->setModelFileContent(
            $this->getNewComment()
        );
    }

    protected $originComment;

    /**
     * 获取原注释内容
     * @return string
     */
    public function getOriginComment()
    {
        if (!$this->originComment) {
            $this->originComment = (string)$this->reflection->getDocComment();
        }
        return $this->originComment;
    }

    /**
     * 获取新的注释内容
     * @return string
     * @throws BindingResolutionException
     * @throws Exception
     * @throws ReflectionException
     */
    public function getNewComment()
    {
        //将所有需要设置的property统一整理
        $this->setProperties();

        //获取一个可写的phpdoc对象
        $phpdoc = $this->getPhpDocForWrite();

        //将properties写入phpdoc
        $this->writeDocumentWithProperties($phpdoc);

        $serializer = new DocBlockSerializer();

        return $serializer->getDocComment($phpdoc);

    }

    /**
     * 设置类属性
     * @throws BindingResolutionException
     * @throws Exception
     * @throws ReflectionException
     */
    protected function setProperties()
    {
        $this->setPropertiesFromTable();
        $this->setPropertiesFromMethods();
    }

    /**
     * 获取一个可写的phpdoc对象
     * @return DocBlock
     */
    protected function getPhpDocForWrite()
    {

        $namespace = $this->reflection->getNamespaceName();
        $classname = $this->reflection->getShortName();

        if ($this->isReset) {
            return new DocBlock($classname, new Context($namespace));
        }

        return new DocBlock($this->reflection, new Context($namespace));
    }

    /**
     * 把所有已经统计好的properties写入phpdoc
     * @param DocBlock $phpdoc
     * @return DocBlock
     */
    protected function writeDocumentWithProperties(DocBlock $phpdoc)
    {
        $properties = [];

        foreach ($phpdoc->getTags() as $tag) {
            $name = $tag->getName();

            if (!in_array($name, $this->properties)) {
                $phpdoc->deleteTag($tag);
                continue;
            }

            if ($name == 'property' || $name == 'property-read' || $name == 'property-write') {
                $properties[] = $tag->getVariableName();
            }
        }

        foreach ($this->properties as $name => $property) {
            $name = "\$$name";

            if (in_array($name, $properties)) {
                continue;
            }

            if ($property['read'] && $property['write']) {
                $attr = 'property';
            } elseif ($property['write']) {
                $attr = 'property-write';
            } else {
                $attr = 'property-read';
            }

            $tagLine = trim("@{$attr} {$property['type']} {$name} {$property['comment']}");

            $tag = Tag::createInstance($tagLine, $phpdoc);
            $phpdoc->appendTag($tag);

        }

        return $phpdoc;

    }


    /**
     * 从数据表获取property
     * @throws Exception
     */
    protected function setPropertiesFromTable()
    {
        foreach ($this->getTableColumns() as $column) {
            $this->setProperty(
                $column->getName(),
                $this->convertToPhpType($column->getType()),
                true,
                true,
                $column->getComment(),
                !$column->getNotnull()
            );
        }
    }


    /**
     * 从方法中获取property
     * @throws BindingResolutionException
     * @throws ReflectionException
     */
    protected function setPropertiesFromMethods()
    {
        $methods = get_class_methods($this->modelInstance());

        sort($methods);

        foreach ($methods as $method) {

            //Magic get<name>Attribute
            if ($this->isGetAttributeMethod($method)) {
                $this->setPropertiesFromGetAttributeMethods($method);
                continue;
            }

            //Magic set<name>Attribute
            if ($this->isSetAttributeMethod($method)) {
                $this->setPropertiesFromSetAttributeMethods($method);
                continue;
            }

            //Magic Relation Method
            if ($this->isRelationMethod($method)) {
                $this->setPropertiesFromRelationMethod($method);
                continue;
            }

        }

    }

    /**
     * 判断是否是 get<name>Attribute 方法
     * @param string $method
     * @return bool
     */
    protected function isGetAttributeMethod(string $method)
    {
        return (
            Str::startsWith($method, 'get')
            && Str::endsWith($method, 'Attribute')
            && $method !== 'getAttribute'
        );
    }

    /**
     * 判断是否是Scope方法
     * @param string $method
     * @return bool
     */
    protected function isScopeMethod(string $method)
    {
        return (
            Str::startsWith($method, 'scope')
            && $method !== 'scopeQuery'
        );
    }

    /**
     * 判断是否是 set<name>Attribute 方法
     * @param string $method
     * @return bool
     */
    protected function isSetAttributeMethod(string $method)
    {
        return (
            Str::startsWith($method, 'set')
            && Str::endsWith($method, 'Attribute')
            && $method !== 'setAttribute'
        );

    }


    /**
     * 从 get<name>Attribute 方法中设置属性
     * @param $method
     * @return bool
     * @throws BindingResolutionException
     * @throws ReflectionException
     */
    protected function setPropertiesFromGetAttributeMethods(string $method)
    {

        if (!$this->isGetAttributeMethod($method)) {
            return false;
        }

        $name = Str::snake(substr($method, 3, -9));

        if (empty($name)) {
            return false;
        }

        $model = $this->modelInstance();
        $reflection = new \ReflectionMethod($model, $method);
        $type = $this->getReturnType($reflection);
        $type = $this->getTypeInModel($model, $type);
        $this->setProperty($name, $type, true, null);

        return true;

    }

    /**
     * 从 set<name>Attribute 方法中设置属性
     * @param $method
     * @return bool
     */
    protected function setPropertiesFromSetAttributeMethods($method)
    {

        //Magic set<name>Attribute
        if (!$this->isSetAttributeMethod($method)) {
            return false;
        }

        $name = Str::snake(substr($method, 3, -9));
        if (empty($name)) {
            return false;
        }

        $this->setProperty($name, null, null, true);
        return true;
    }

    /**
     * 判断是否是orm关系方法
     * @param string $method
     * @return bool
     * @throws BindingResolutionException
     * @throws ReflectionException
     */
    protected function isRelationMethod(string $method)
    {
        if (method_exists('Illuminate\Database\Eloquent\Model', $method)
            || Str::startsWith($method, 'get')) {
            return false;
        }

        $model = $this->modelInstance();

        //Use reflection to inspect the code, based on Illuminate/Support/SerializableClosure.php
        $reflection = new \ReflectionMethod($model, $method);

        if ($returnType = $reflection->getReturnType()) {
            $type = $returnType instanceof \ReflectionNamedType ? $returnType->getName() : (string)$returnType;
        } else {
            // php 7.x type or fallback to docblock
            $type = (string)$this->getReturnTypeFromDocBlock($reflection);
        }

        $code = $this->getMethodContent($method);
        foreach ($this->getRelationTypes() as $relation => $impl) {
            $search = '$this->' . $relation . '(';
            if (!stripos($code, $search) and !(ltrim($impl, '\\') === ltrim((string)$type, '\\'))) {
                continue;
            }

            //Resolve the relation's model to a Relation object.
            $methodReflection = new \ReflectionMethod($model, $method);
            if ($methodReflection->getNumberOfParameters()) {
                continue;
            }

            $relationObj = $this->getRelationFromMethod($method);

            if ($relationObj instanceof Relation) {
                return true;
            }

        }
        return false;
    }


    /**
     * 从orm关系方法中获取property并设置
     * @param string $method
     * @throws BindingResolutionException
     * @throws ReflectionException
     */
    protected function setPropertiesFromRelationMethod(string $method)
    {

        $model = $this->modelInstance();
        $relationObj = $this->getRelationFromMethod($method);

        $relatedModelClass = get_class($relationObj->getRelated());
        if (strpos(get_class($relationObj), 'Many') !== false) {
            $collection = $this->getCollectionClass($relatedModelClass);
            $this->setProperty($method, $collection . '|' . '\\' . $relatedModelClass . '[]', true, null);
            $this->setProperty(Str::snake($method) . '_count', 'int|null', true, false);

            return;
        }

        if (strpos(get_class($relationObj), 'morphTo') !== false) {

            exit('暂未实现对morphTo ORM关系的解析');
            // Model isn't specified because relation is polymorphic
            $this->setProperty($method,
                $this->getClassNameInDestinationFile($model, Model::class) . '|\Eloquent',
                true, null);

            return;
        }


        //Single model is returned
        $this->setProperty(
            $method, '\\' . $relatedModelClass, true, null, '',
            $this->isRelationNullable($relationObj)
        );

        return;


    }


    /**
     * 获取指定的方法的实现代码
     * @param string $method
     * @return false|string
     * @throws BindingResolutionException
     * @throws ReflectionException
     */
    protected function getMethodContent(string $method)
    {

        $model = $this->modelInstance();
        //Use reflection to inspect the code, based on Illuminate/Support/SerializableClosure.php
        $reflection = new \ReflectionMethod($model, $method);

//        if ($returnType = $reflection->getReturnType()) {
//            $type = $returnType instanceof \ReflectionNamedType ? $returnType->getName() : (string)$returnType;
//        } else {
//            // php 7.x type or fallback to docblock
//            $type = (string)$this->getReturnTypeFromDocBlock($reflection);
//        }

        $file = new \SplFileObject($reflection->getFileName());
        $file->seek($reflection->getStartLine() - 1);

        $code = '';
        while ($file->key() < $reflection->getEndLine()) {
            $code .= $file->current();
            $file->next();
        }
        $code = trim(preg_replace('/\s\s+/', '', $code));
        $begin = strpos($code, 'function(');

        return substr($code, $begin, strrpos($code, '}') - $begin + 1);

    }


    /**
     * 从某个orm方法中获取关联关系
     * @param string $method
     * @return mixed
     */
    protected function getRelationFromMethod(string $method)
    {
        return Relation::noConstraints(function () use ($method) {
            try {
                return $this->modelInstance()->$method();
            } catch (\Throwable $e) {
                return null;
            }
        });
    }


    /**
     * 获取关联关系对象映射
     * @return array|string[]
     */
    protected function getRelationTypes(): array
    {
        return self::RELATION_TYPES;
    }

    /**
     * 获取model文件的内容
     * @return string
     * @throws FileNotFoundException
     */
    protected function getModelFileContent()
    {
        return $this->file->get($this->reflection->getFileName());
    }

    /**
     * 设置model文件的内容
     * @param string $newComment
     * @return bool
     * @throws FileNotFoundException
     */
    protected function setModelFileContent(string $newComment)
    {

        $originalDoc = $this->reflection->getDocComment();
        $content = $this->getModelFileContent();

        //如果原来有注释，则覆盖
        if ($originalDoc) {
            $newContent = str_replace($originalDoc, $newComment, $content);
            //$this->file->put($this->reflection->getFileName(), $newContent);
            $this->file->replace($this->reflection->getFileName(), $newContent);
            logger(file_get_contents($this->reflection->getFileName()));
            return true;
        }
        $pos = strpos($content, "class {$this->reflection->getShortName()}");
        //如果原来没有注释，则获取类的起始位置
        if ($pos !== false) {
            $content = substr_replace($content, $newComment . "\n", $pos, 0);
            //$this->file->put($this->reflection->getFileName(), $content);
            $this->file->replace($this->reflection->getFileName(), $content);
            return true;
        }

        return false;

    }


    protected $properties = [];

    /**
     * 设置model的属性
     * @param $name
     * @param $type
     * @param bool $isRead
     * @param bool $isWrite
     * @param string $comment
     * @param bool $nullable
     */
    protected function setProperty($name, $type, $isRead = false, $isWrite = false, $comment = '', $nullable = false)
    {
        if (!isset($this->properties[$name])) {
            $this->properties[$name] = [];
            $this->properties[$name]['type'] = 'mixed';
            $this->properties[$name]['read'] = false;
            $this->properties[$name]['write'] = false;
            $this->properties[$name]['comment'] = (string)$comment;
        }
        if ($type !== null) {
            if ($nullable) {
                $type .= '|null';
            }
            $this->properties[$name]['type'] = $type;
        }
        $this->properties[$name]['read'] = $isRead;
        $this->properties[$name]['write'] = $isWrite;
    }


    /**
     * 获取一个model对象
     * @return Model|mixed
     * @throws BindingResolutionException
     */
    public function modelInstance()
    {
        if (!$this->modelInstance) {
            $this->modelInstance = app()->make($this->modelClass);
        }

        return $this->modelInstance;
    }

    /**
     * 获取返回值类型
     * @param \ReflectionMethod $reflection
     * @return string|null
     */
    protected function getReturnType(\ReflectionMethod $reflection): ?string
    {
        $type = $this->getReturnTypeFromDocBlock($reflection);
        if ($type) {
            return $type;
        }

        return $this->getReturnTypeFromReflection($reflection);
    }


    /**
     * Get method return type based on it DocBlock comment
     *
     * @param \ReflectionMethod $reflection
     *
     * @return null|string
     */
    protected function getReturnTypeFromDocBlock(\ReflectionMethod $reflection)
    {
        $phpDocContext = (new ContextFactory())->createFromReflector($reflection);
        $context = new Context(
            $phpDocContext->getNamespace(),
            $phpDocContext->getNamespaceAliases()
        );
        $type = null;
        $phpdoc = new DocBlock($reflection, $context);

        if ($phpdoc->hasTag('return')) {
            $type = $phpdoc->getTagsByName('return')[0]->getType();
        }

        return $type;
    }

    protected function getReturnTypeFromReflection(\ReflectionMethod $reflection): ?string
    {
        $returnType = $reflection->getReturnType();
        if (!$returnType) {
            return null;
        }

        $type = $returnType instanceof \ReflectionNamedType
            ? $returnType->getName()
            : (string)$returnType;

        if (!$returnType->isBuiltin()) {
            $type = '\\' . $type;
        }

        if ($returnType->allowsNull()) {
            $type .= '|null';
        }

        return $type;
    }


    /**
     * Get the parameters and format them correctly
     *
     * @param $method
     * @return array
     * @throws \ReflectionException
     */
    public function getParameters(\ReflectionMethod $method)
    {
        //Loop through the default values for parameters, and make the correct output string
        $paramsWithDefault = [];

        foreach ($method->getParameters() as $param) {
            $paramStr = '$' . $param->getName();
            if ($paramType = $this->getParamType($method, $param)) {
                $paramStr = $paramType . ' ' . $paramStr;
            }

            if ($param->isOptional() && $param->isDefaultValueAvailable()) {
                $default = $param->getDefaultValue();
                if (is_bool($default)) {
                    $default = $default ? 'true' : 'false';
                } elseif (is_array($default)) {
                    $default = '[]';
                } elseif (is_null($default)) {
                    $default = 'null';
                } elseif (is_int($default)) {
                    //$default = $default;
                } else {
                    $default = "'" . trim($default) . "'";
                }

                $paramStr .= " = $default";
            }

            $paramsWithDefault[] = $paramStr;
        }
        return $paramsWithDefault;
    }

    /**
     * @param object $model
     * @param string|null $type
     * @return string|null
     */
    protected function getTypeInModel(object $model, ?string $type): ?string
    {
        if ($type === null) {
            return null;
        }

        if (class_exists($type)) {
            $type = $this->getClassNameInDestinationFile($model, $type);
        }

        return $type;
    }

    protected function getClassNameInDestinationFile(object $model, string $className): string
    {
        $reflection = $model instanceof ReflectionClass
            ? $model
            : new ReflectionObject($model);

        $className = trim($className, '\\');

        $usedClassNames = $this->getUsedClassNames($reflection);
        return $usedClassNames[$className] ?? ('\\' . $className);
    }

    /**
     * @param ReflectionClass $reflection
     * @return string[]
     */
    protected function getUsedClassNames(ReflectionClass $reflection): array
    {
        $namespaceAliases = array_flip((new ContextFactory())->createFromReflector($reflection)->getNamespaceAliases());
        $namespaceAliases[$reflection->getName()] = $reflection->getShortName();

        return $namespaceAliases;
    }


    /**
     * Determine a model classes' collection type.
     *
     * @see http://laravel.com/docs/eloquent-collections#custom-collections
     * @param string $className
     * @return string
     */
    protected function getCollectionClass($className)
    {
        // Return something in the very very unlikely scenario the model doesn't
        // have a newCollection() method.
        if (!method_exists($className, 'newCollection')) {
            return '\Illuminate\Database\Eloquent\Collection';
        }

        /** @var Model $model */
        $model = new $className();
        return '\\' . get_class($model->newCollection());
    }

    /**
     * Check if the relation is nullable
     *
     * @param Relation $relationObj
     *
     * @return bool
     * @throws ReflectionException
     */
    protected function isRelationNullable(Relation $relationObj): bool
    {
        $reflectionObj = new ReflectionObject($relationObj);
        $relation = $reflectionObj->getShortName();

        if (in_array($relation, ['hasOne', 'hasOneThrough', 'morphOne'], true)) {
            $defaultProp = $reflectionObj->getProperty('withDefault');
            $defaultProp->setAccessible(true);

            return !$defaultProp->getValue($relationObj);
        }

        if (!$reflectionObj->hasProperty('foreignKey')) {
            return false;
        }

        $fkProp = $reflectionObj->getProperty('foreignKey');
        $fkProp->setAccessible(true);

        return isset($this->nullableColumns[$fkProp->getValue($relationObj)]);
    }
}
