<?php

namespace Smart\Gii\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\View\View;
use Smart\Gii\Http\Repository\CurdRepository;
use Smart\Gii\Http\request\CurdRequestForm;

class CurdController extends Controller
{
    /**
     * @var CurdRepository
     */
    public $repository;

    /**
     * CurdController constructor.
     * @param CurdRepository $repository
     */
    public function __construct(CurdRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @return View
     */
    public function index()
    {
        return view('gii::curd');
    }

    /**
     * 自动联想
     * @param $model
     * @return array
     */
    public function guessByModel($model)
    {
        return [
            'classes' => $this->repository->guessByModel($model),
        ];
    }

    /**
     * 自动联想
     * @param $controller
     * @return array
     */
    public function guessByCtrl($controller)
    {
        return [
            'classes' => $this->repository->guessByController($controller),
        ];
    }

    /**
     * 预览
     * @param CurdRequestForm $request
     * @return array[]
     */
    public function preview(CurdRequestForm $request)
    {
        return [
            'files' => $this->repository->checkFile(
                $request->input('ctrl'),
                $request->input('baseCtrl'),
                $request->input('form'),
                $request->input('resource'),
                $request->input('model'),
                $request->input('repository')
            ),
        ];
    }

    /**
     * 生成
     * @param CurdRequestForm $request
     * @return array
     */
    public function generate(CurdRequestForm $request)
    {
        return $this->repository->runCommand(
            [
                'ctrl' => $request->input('ctrl'),
                'baseCtrl' => $request->input('baseCtrl'),
                'form' => $request->input('form'),
                'resource' => $request->input('resource'),
                'model' => $request->input('model'),
                'repository' => $request->input('repository'),
            ],
            $request->input('classes')
        );
    }
}
