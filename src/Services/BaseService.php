<?php

namespace Smart\Gii\Services;

use Doctrine\DBAL\Types\Type;

class BaseService
{
    /**
     * @param Type $type
     * @return string
     */
    protected function formatToPhpType(Type $type)
    {
        if (in_array($type->getName(), [
            Type::BINARY,
            Type::BLOB,
            Type::DATE_IMMUTABLE,
            Type::DATEINTERVAL,
            Type::DATETIME_IMMUTABLE,
            Type::DATETIMETZ_IMMUTABLE,
            Type::JSON,
            Type::OBJECT,
            Type::STRING,
            Type::TEXT,
            Type::DATETIME,
            Type::DATE,
        ])) {
            return 'string';
        }

        if (in_array($type->getName(), [
            Type::BIGINT,
            Type::INTEGER,
            Type::SMALLINT,
        ])) {
            return 'int';
        }

        if (in_array($type->getName(), [
            Type::DECIMAL,
            Type::FLOAT,
        ])) {
            return 'float';
        }

        return ucfirst($type->getName());
    }

    /**
     * Alphabetically sorts the imports for the given stub.
     *
     * @param string $stub
     * @return string
     */
    protected function sortImports($stub)
    {
        if (preg_match('/(?P<imports>(?:use [^;]+;$\n?)+)/m', $stub, $match)) {
            $imports = explode("\n", trim($match['imports']));

            sort($imports);

            return str_replace(trim($match['imports']), implode("\n", $imports), $stub);
        }

        return $stub;
    }
}
