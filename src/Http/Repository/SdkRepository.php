<?php

namespace Smart\Gii\Http\Repository;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Facades\Artisan;
use Smart\Common\Helpers\Zip;
use Smart\Common\Services\DocService;
use Smart\Common\Services\SdkRestNameService;
use Smart\Gii\Console\SdkCreator;
use Smart\Gii\Console\SdkRestCreator;
use Smart\Gii\Services\SdkCreatorService;
use Smart\Gii\Services\SdkRestCreatorService;

/**
 * Class SdkRepository
 */
class SdkRepository extends BaseRepository
{
    /**
     * @var
     */
    protected $key;

    /**
     * @param $package
     * @throws FileNotFoundException
     * @return array
     */
    public function checkFiles($package)
    {
        return array_merge(
            $this->checkSdkMainClasses($package),
            $this->checkSdkRestClasses($package)
        );
    }

    /**
     * @return string|string[]
     */
    public function generateSdkZip()
    {
        $inputPath = $this->getPath();
        $basePath = $this->getBasePath();
        $filename = str_replace(
            $basePath,
            '',
            realpath($inputPath . '/../') . date('/Ymd') . '.' . basename($inputPath) . '.sdk.zip'
        );
        Zip::zipDir(
            $inputPath,
            $basePath . $filename
        );

        return $filename;
    }

    /**
     * @param $zipName
     * @return bool
     */
    public function isExistZip($zipName)
    {
        return file_exists($this->getBasePath() . $zipName);
    }

    /**
     * @param $package
     * @return array
     */
    public function runCommand($package)
    {
        return array_merge(
            $this->runSdkMainClassesCommand($package),
            $this->runSdkRestClassesCommand($package)
        );
    }

    /**
     * @param $package
     * @return array
     */
    public function runSdkRestClassesCommand($package)
    {
        $service = new DocService();
        $data = [];
        foreach ($routes = $service->validRoutes() as $route) {
            Artisan::call(
                app(SdkRestCreator::class)->getName(),
                [
                    'name' => $class = $this->getRestName($route, $package),
                    'action' => $route->getActionName(),
                    '--path' => $this->getPath(),
                    '--force' => true,
                ]
            );

            try {
                $data[] = [
                    'isDone' => true,
                    'file' => $this->getFilePathByClassName($class),
                ];

                continue;
            } catch (\ReflectionException $e) {
            } catch (FileNotFoundException $e) {
            }
            $data[] = [
                'isDone' => false,
                'file' => $class,
            ];
        }

        return $data;
    }

    /**
     * 执行系统命令
     * @param $package
     * @return array
     */
    public function runSdkMainClassesCommand($package)
    {
        return SdkCreatorService::classes($package, function ($class, $stub) use ($package) {
            Artisan::call(
                app(SdkCreator::class)->getName(),
                [
                    'name' => $class,
                    'package' => $package,
                    '--path' => $this->getPath(),
                    '--force' => true,
                ]
            );

            try {
                return [
                    'isDone' => true,
                    'file' => $this->getFilePathByClassName($class),
                ];
            } catch (\ReflectionException $e) {
            } catch (FileNotFoundException $e) {
            }

            return [
                'isDone' => false,
                'file' => $class,
            ];
        });
    }

    /**
     * @param $class
     * @throws FileNotFoundException
     * @return false|string
     */
    public function getFilePathByClassName($class)
    {
        $file = $this->getPath() . '/' . str_replace('\\', '/', $class) . '.php';
        if (file_exists($file)) {
            return $file;
        }

        throw new FileNotFoundException();
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->getBasePath() . date('/Ym/') . $this->key();
    }

    /**
     * @return string
     */
    public function getBasePath()
    {
        return app()->storagePath() . '/cache';
    }

    /**
     * 某个类是否被存在
     * @param $class
     * @return bool
     */
    public function isExistClass($class)
    {
        try {
            include_once $this->getFilePathByClassName($class);

            return class_exists($class);
        } catch (\ErrorException $e) {
            return false;
        } catch (FileNotFoundException $e) {
            return false;
        }
    }

    /**
     * @param $package
     * @return array
     */
    protected function checkSdkMainClasses($package)
    {
        return SdkCreatorService::classes($package, function ($class, $stub) use ($package) {
            $exist = $this->isExistClass($class);

            $newClassContent = (new SdkCreatorService())
                ->setClassName($class)
                ->setPackage($package)
                ->getFileContent();

            return [
                'file' => $class . '.php',
                'action' => $action = $this->getAction($exist, $this->hasSameContent($class, $newClassContent)),
                'checked' => 'create' == $action,
                'isExistClass' => $exist,
            ];
        });
    }

    /**
     * @param $package
     * @throws FileNotFoundException
     * @return array
     */
    protected function checkSdkRestClasses($package)
    {
        $service = new DocService();
        $classes = [];
        foreach ($routes = $service->validRoutes() as $route) {
            $class = $this->getRestName($route, $package);
            $exist = $this->isExistClass($class);

            $newClassContent = (new SdkRestCreatorService())
                ->setClassName($class)
                ->setRoute($route)
                ->getFileContent();

            $classes[] = [
                'file' => $class . '.php',
                'action' => $action = $this->getAction($exist, $this->hasSameContent($class, $newClassContent)),
                'checked' => 'create' == $action,
                'isExistClass' => $exist,
            ];
        }

        return $classes;
    }

    /**
     * @param $route
     * @param $package
     * @return string|string[]
     */
    protected function getRestName($route, $package)
    {
        $nameService = new SdkRestNameService($route);
        $class = $package . '\Rest\\' . $nameService->generateApiName();

        return str_replace('\\\\', '\\', $class);
    }

    /**
     * @param $existedClass
     * @param $newClassContent
     * @return bool
     */
    protected function hasSameContent($existedClass, $newClassContent)
    {
        if (!$this->isExistClass($existedClass)) {
            return false;
        }

        try {
            $new = md5($newClassContent);
            $old = md5_file($this->getFilePathByClassName($existedClass));

            return $new == $old;
        } catch (FileNotFoundException $e) {
            echo '没有找到模板文件';

            return false;
        } catch (\ReflectionException $e) {
            echo '没有找到类文件';

            return false;
        }
    }

    /**
     * @return int
     */
    protected function key()
    {
        if ($this->key) {
            return $this->key;
        }
        $this->key = time();

        return $this->key;
    }
}
