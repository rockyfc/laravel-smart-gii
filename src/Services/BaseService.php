<?php

namespace Smart\Gii\Services;

use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Types\Types;

class BaseService
{
    /**
     * @param Type $type
     * @return string
     */
    protected function formatToPhpType(Type $type)
    {
        if (in_array($type->getName(), [
            Types::BINARY,
            Types::BLOB,
            Types::DATE_IMMUTABLE,
            Types::DATEINTERVAL,
            Types::DATETIME_IMMUTABLE,
            Types::DATETIMETZ_IMMUTABLE,
            Types::JSON,
            Types::OBJECT,
            Types::STRING,
            Types::TEXT,
        ])) {
            return 'string';
        }

        if (in_array($type->getName(), [
            Types::BIGINT,
            Types::INTEGER,
            Types::SMALLINT,
        ])) {
            return 'int';
        }

        if (in_array($type->getName(), [
            Types::DECIMAL,
            Types::FLOAT,
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
