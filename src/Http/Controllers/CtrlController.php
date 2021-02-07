<?php

namespace Smart\Gii\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\View\View;
use Smart\Gii\Http\Repository\CtrlRepository;
use Smart\Gii\Http\request\CtrlRequestForm;

class CtrlController extends Controller
{
    /**
     * @var CtrlRepository
     */
    public $repository;

    /**
     * CtrlController constructor.
     * @param CtrlRepository $repository
     */
    public function __construct(CtrlRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @return View
     */
    public function index()
    {
        return view('gii::ctrl');
    }

    /**
     * 自动联想
     * @param $controller
     * @return array
     */
    public function classes($controller)
    {
        return [
            'classes' => $this->repository->guess($controller),
        ];
    }

    /**
     * 预览
     * @param CtrlRequestForm $request
     * @return array[]
     */
    public function preview(CtrlRequestForm $request)
    {
        return [
            'files' => [
                $this->repository->checkFile(
                    $request->input('ctrl'),
                    $request->input('baseCtrl'),
                    $request->input('form'),
                    $request->input('resource'),
                    $request->input('model'),
                    $request->input('repository')
                ),
            ],
        ];
    }

    /**
     * 生成
     * @param CtrlRequestForm $request
     * @return array
     */
    public function generate(CtrlRequestForm $request)
    {
        $file = $this->repository->runCommand(
            $class = $request->input('ctrl'),
            $request->input('baseCtrl'),
            $request->input('form'),
            $request->input('resource'),
            $request->input('model'),
            $request->input('repository')
        );

        if (!$file) {
            return [
                'files' => [[
                    'isDone' => false,
                    'file' => $class,
                ]],
                'code' => $this->repository->getSuggestRouteCode(),
            ];
        }

        return [
            'files' => [[
                'isDone' => (bool)$file,
                'file' => $file,
            ]],
            'code' => $this->repository->getSuggestRouteCode(),
        ];
    }
}
