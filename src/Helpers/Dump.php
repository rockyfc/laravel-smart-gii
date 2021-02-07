<?php

namespace Smart\Gii\Helpers;

class Dump
{
    /**
     * 将一个数组格式化成一个字符串
     * @param array $array
     * @param string $prefix
     * @param string $suffix
     * @return string
     */
    public static function value(array $array, $prefix = '', $suffix = '')
    {
        $str = "[\n";
        foreach ($array as $value) {
            $str .= $prefix . "    '" . $value . "',\n";
        }
        $str .= $prefix . ']' . $suffix;

        return $str;
    }

    /**
     * 将一个数组格式化成一个字符串
     *
     * @param array $array
     * @param string $prefix
     * @param string $suffix
     * @return string
     */
    public static function valueLine(array $array, $prefix = '', $suffix = '')
    {
        $str = $prefix . '[';
        foreach ($array as $value) {
            $str .= $prefix . "    '" . $value . "',";
        }
        $str .= $prefix . ']' . $suffix;

        return $str;
    }

    public static function keyAndValueArray(array $array, $prefix = '', $suffix = "\n")
    {
        $rows = ['['];
        foreach ($array as $key => $value) {
            if ($value === null) {
                $rows[] = $prefix . "    '" . $key . "' => null,";

                continue;
            }

            if ($value === '') {
                $rows[] = $prefix . "    '" . $key . "' => '',";

                continue;
            }

            if (floatval($value) == $value) {
                $rows[] = $prefix . "    '" . $key . "' => " . $value . ',';

                continue;
            }
            $rows[] = $prefix . "    '" . $key . "' => '" . $value . "',";

            continue;
        }
        $rows[] = $prefix . ']';

        return implode($suffix, $rows);
    }
}
