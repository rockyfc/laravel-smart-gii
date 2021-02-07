<?php

namespace Smart\Gii\Http\Repository;

use Illuminate\Support\Str;

class BaseRepository
{
    /**
     * 某个类是否被存在
     * @param $class
     * @return bool
     */
    public function isExistClass($class)
    {
        try {
            return class_exists($class);
        } catch (\ErrorException $e) {
            return false;
        }
    }

    /**
     * 根据类名获取类文件的路径
     * @param $class
     * @throws \ReflectionException
     * @return false|string
     */
    public function getFilePathByClassName($class)
    {
        return (new \ReflectionClass($class))->getFileName();
    }

    /**
     * @param bool $exist
     * @param bool $hasSameContent
     * @return string
     */
    public function getAction($exist = false, $hasSameContent = false)
    {
        if ($exist and $hasSameContent) {
            return 'unchanged';
        }

        if ($exist and !$hasSameContent) {
            return 'overwrite';
        }

        return 'create';
    }

    /**
     * 格式化类名称
     * @param $class
     * @param string $suffix
     * @return string
     */
    public function formatClass($class, $suffix = '')
    {
        $arr = explode('\\', $class);
        foreach ($arr as &$word) {
            $word = ucfirst($word);
        }
        $name = implode('\\', $arr);
        if (Str::endsWith($name, $suffix)) {
            return $name;
        }

        return $name . $suffix;
    }

    /**
     * 获取controller类的建议后缀
     * @return string
     */
    protected function getControllerSuffix()
    {
        return config('gii.suffix.class.controller');
    }

    /**
     * 获取resource类的建议后缀
     * @return string
     */
    protected function getResourceSuffix()
    {
        return config('gii.suffix.class.resource');
    }

    /**
     * 获取repository类的建议后缀
     * @return string
     */
    protected function getRepositorySuffix()
    {
        return config('gii.suffix.class.repository');
    }

    /**
     * 获取formRequest类的建议后缀
     * @return string
     */
    protected function getFormRequestSuffix()
    {
        return config('gii.suffix.class.formRequest');
    }

    /**
     * 获取model类的建议后缀
     * @return string
     */
    protected function getModelSuffix()
    {
        return config('gii.suffix.class.model');
    }

    /**
     * 猜测form
     * @param $namespace
     * @param $name
     * @param bool $exist
     * @return string
     */
    protected function guessForm($namespace, $name, $exist = true)
    {
        $name .= $this->getFormRequestSuffix();
        $class = $namespace . '\\Requests\\' . $name;
        if ($exist or $this->isExistClass($class)) {
            return $class;
        }

        $class = 'App\Http\Requests\\' . $name;
        if ($exist or $this->isExistClass($class)) {
            return $class;
        }

        if ($exist) {
            return 'App\Http\Requests\\' . $name;
        }

        return $namespace . '\Requests\\' . $name;
    }

    /**
     * 猜测controller
     * @param $namespace
     * @param $name
     * @param bool $exist 是否必须存在
     * @return string
     */
    protected function guessController($namespace, $name, $exist = true)
    {
        $name .= $this->getControllerSuffix();
        $class = $namespace . '\Controllers\\' . $name;
        if ($exist or $this->isExistClass($class)) {
            return $class;
        }

        return 'App\Http\Controllers\\' . $name;
    }

    /**
     * 猜测资源
     * @param $namespace
     * @param $name
     * @param bool $exist
     * @return string
     */
    protected function guessResource($namespace, $name, $exist = true)
    {
        $name .= $this->getResourceSuffix();
        $class = $namespace . '\\Resources\\' . $name;
        if ($exist or $this->isExistClass($class)) {
            return $class;
        }

        $class = 'App\Http\Resources\\' . $name;
        if ($exist or $this->isExistClass($class)) {
            return $class;
        }

        if ($exist) {
            return 'App\Http\Resources\\' . $name;
        }

        return $namespace . '\Resources\\' . $name;
    }

    /**
     * 猜测model
     * @param $namespace
     * @param $name
     * @param bool $exist
     * @return string
     */
    protected function guessModel($namespace, $name, $exist = true)
    {
        $class = 'App\Models\\' . $name;
        if ($exist or $this->isExistClass($class)) {
            return $class;
        }

        $class = 'App\Http\Models\\' . $name;
        if ($exist or $this->isExistClass($class)) {
            return $class;
        }

        $class = $namespace . '\\Models\\' . $name;
        if ($exist or $this->isExistClass($class)) {
            return $class;
        }

        return 'App\Models\\' . $name;
    }
}
