<?php

namespace Smart\Gii\Http\Controllers;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Routing\Controller;
use Illuminate\View\View;
use Smart\Gii\Http\Repository\SdkRepository;
use Smart\Gii\Http\Repository\TesterRepository;
use Smart\Gii\Http\request\SdkRequestForm;

class TesterController extends Controller
{
    /**
     * @var SdkRepository
     */
    public $repository;

    /**
     * TesterController constructor.
     * @param TesterRepository $repository
     */
    public function __construct(TesterRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @return View
     */
    public function index()
    {
        return view('gii::tester');
    }

    /**
     * 预览
     * @param SdkRequestForm $request
     * @throws FileNotFoundException
     * @return array[]
     */
    public function preview(SdkRequestForm $request)
    {
        return [
            'files' => $this->repository->checkFiles(
                $request->input('namespace')
            ),
        ];
    }

    /**
     * 生成
     * @param SdkRequestForm $request
     * @return array
     */
    public function generate(SdkRequestForm $request)
    {
        return [
            'sdk' => $this->repository->runCommand($request->input('namespace')),
            'zip' => $this->repository->generateSdkZip(),
        ];
    }
}
