<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\CreateFont;
use App\Models\Enums\FontTypesEnum;
use App\Services\FontService;
use App\Transformers\FontTransformer;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Translation\Exception\NotFoundResourceException;

class FontController extends BaseController
{
    protected FontService $fontService;
    //protected $pdfTemplateService;

    public function __construct(FontService $fontService)
    {
        parent::__construct();
        $this->fontService = $fontService;
    }

    /**
     * @param CreateFont $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(CreateFont $request)
    {
        if ($this->fontService->getByName($request->name)) {
            throw new BadRequestHttpException('Font already exists');
        }

        if ($request->font_type == FontTypesEnum::TTF) {
            $filename = $request->get('name').'.ttf';
            Storage::disk('fonts')->put($filename, base64_decode($request->get('ttf_file')));
            $this->fontService->create(['name' => $request->get('name'), 'filename' => $filename, 'font_type' => FontTypesEnum::TTF]);

            return response()->json(['success' => true]);
        }

        $filename = $request->get('name').'.php';
        Storage::disk('fonts')->put($filename, base64_decode($request->get('php_file')));
        Storage::disk('fonts')->put($request->get('z_file_name').'.z', base64_decode($request->get('z_file')));
        $this->fontService->create(['name' => $request->get('name'), 'filename' => $filename]);

        return response()->json(['success' => true]);
    }

    /**
     * @return array|\Illuminate\Http\JsonResponse
     */
    public function index()
    {
        return $this->successResponse(new Collection(
            $this->fontService->all(),
            new FontTransformer()
        ));
    }

    /**
     * @param Request $request
     * @return array|\Illuminate\Http\JsonResponse
     */
    public function getByName(Request $request)
    {
        if (! $font = $this->fontService->getByName($request->get('name'))) {
            throw new NotFoundResourceException('Font is not found', 404);
        }

        return $this->successResponse(new Item($font, new FontTransformer()));
    }
}
