<?php

namespace Smart\Gii\Console;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;

/**
 * 创建一个model类
 *
 * @author Rocky<softfc@163.com>
 */
class BaseCreator extends GeneratorCommand
{
    protected function getStub()
    {
        // TODO: Implement getStub() method.
    }

    protected function readJson($name)
    {
        $json = $this->laravel->basePath() . DIRECTORY_SEPARATOR . 'composer.json';
        $content = json_decode(file_get_contents($json), true);
        $map = $content['autoload']['psr-4'];
        foreach ($map as $rootNamespace => $path) {
            if (Str::startsWith($name, $rootNamespace)) {
                return [$path, $rootNamespace];
            }
        }
    }

    protected function getPath($name)
    {
        $name = $this->getNameInput();
        if (!(list($path, $rootNamespace) = $this->readJson($name))) {
            return parent::getPath($name);
        }

        $name = Str::replaceFirst($rootNamespace, '', $name);

        return $this->laravel->basePath() . '/' . $path . '/' . str_replace('\\', '/', $name) . '.php';
    }
}
