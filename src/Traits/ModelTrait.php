<?php

namespace Smart\Gii\Traits;

use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Types\Types;
use Illuminate\Database\Eloquent\Model;

Trait ModelTrait
{
    /**
     * 数据库中的数据类型和php语法中定义的类型的对应关系
     */
    protected $phpTypes = [
        Types::ARRAY => 'string', //'array';
        Types::ASCII_STRING => 'string', //'ascii_string';
        Types::BIGINT => 'int', //'bigint';
        Types::BINARY => 'string', //'binary';
        Types::BLOB => 'string', //'blob';
        Types::BOOLEAN => 'string', //'boolean';
        Types::DATE_MUTABLE => 'string', //'date';
        Types::DATE_IMMUTABLE => 'string',// 'date_immutable';
        Types::DATEINTERVAL => 'string', //'dateinterval';
        Types::DATETIME_MUTABLE => 'string',// 'datetime'
        Types::DATETIME_IMMUTABLE => 'string',// 'datetime_immutable';
        Types::DATETIMETZ_MUTABLE => 'string',// 'datetimetz';
        Types::DATETIMETZ_IMMUTABLE => 'string', // 'datetimetz_immutable';
        Types::DECIMAL => 'float', //'decimal';
        Types::FLOAT => 'float', //'float';
        Types::GUID => 'string', //'guid';
        Types::INTEGER => 'int', //'integer';
        Types::JSON => 'string', //'json';
        Types::OBJECT => 'string', //'object';
        Types::SIMPLE_ARRAY => 'string', //'simple_array';
        Types::SMALLINT => 'int', //'smallint';
        Types::STRING => 'string', //'string';
        Types::TEXT => 'string', //'text';
        Types::TIME_MUTABLE => 'string', //'time';
        Types::TIME_IMMUTABLE => 'string',// 'time_immutable';];
    ];

    /**
     * 数据库中的数据类型转化成php数据类型
     * @param Type $type
     * @return string
     */
    protected function convertToPhpType(Type $type)
    {
        return $this->phpTypes[$type->getName()] ?? ucfirst($type->getName());
    }

    /**
     * @var Model
     */
    public $modelInstance;

    /**
     * @return AbstractSchemaManager
     * @throws Exception
     */
    protected function getSchema()
    {
        $connection = $this->modelInstance->getConnection();
        $tableSchema = $connection->getDoctrineSchemaManager();

        $tableSchema->getDatabasePlatform()
            ->registerDoctrineTypeMapping('enum', 'string');
        $tableSchema->getDatabasePlatform()
            ->registerDoctrineTypeMapping('tinyint', 'integer');

        return $tableSchema;
    }

    /**
     * @return Column[]
     * @throws Exception
     */
    protected function getTableColumns()
    {
        return $this->getSchema()->listTableColumns($this->modelInstance->getTable());
    }


}
