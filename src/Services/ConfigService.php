<?php

namespace Smart\Gii\Services;


class ConfigService
{

    public static function key()
    {
        return 'smart-gii';
    }

    public static function config()
    {
        return config(self::key());
    }

    public static function domain()
    {
        return config(self::key() . '.domain');
    }

    public static function enabled()
    {
        return (bool)config(self::key() . '.enabled');
    }

    public static function prefix()
    {
        return config(self::key() . '.prefix');
    }

    public static function suffix()
    {
        return config(self::key() . '.suffix');
    }

    public static function classSuffix()
    {
        return config(self::key() . '.suffix.class');
    }

    public static function controllerSuffix()
    {
        return config(self::key() . '.suffix.class.controller');
    }

    public static function resourceSuffix()
    {
        return config(self::key() . '.suffix.class.resource');
    }

    public static function formRequestSuffix()
    {
        return config(self::key() . '.suffix.class.formRequest');
    }

    public static function modelSuffix()
    {
        return config(self::key() . '.suffix.class.model');
    }

    public static function repositorySuffix()
    {
        return config(self::key() . '.suffix.class.repository');
    }

    public static function middleware()
    {
        return config(self::key() . '.suffix.middleware');
    }


}
