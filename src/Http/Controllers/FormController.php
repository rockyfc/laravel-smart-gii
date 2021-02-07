<?php

namespace Smart\Gii\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\View\View;
use Smart\Gii\Http\Repository\FormRepository;
use Smart\Gii\Http\request\FormRequestForm;

class FormController extends Controller
{
    /**
     * @var FormRepository
     */
    public $repository;

    /**
     * FormController constructor.
     * @param FormRepository $repository
     */
    public function __construct(FormRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @return View
     */
    public function index()
    {
        return view('gii::form');
    }

    /**
     * 预览
     * @param FormRequestForm $request
     * @return array[]
     */
    public function preview(FormRequestForm $request)
    {
        return [
            'files' => [
                $this->repository->checkFile(
                    $request->input('form'),
                    $request->input('model')
                ),
            ],
        ];
    }

    /**
     * 生成
     * @param FormRequestForm $request
     * @return array
     */
    public function generate(FormRequestForm $request)
    {
        $file = $this->repository->runCommand(
            $class = $request->input('form'),
            $request->input('model')
        );

        if (!$file) {
            return [[
                'isDone' => false,
                'file' => $class,
            ]];
        }

        return [[
            'isDone' => (bool)$file,
            'file' => $file,
        ]];
    }
}
