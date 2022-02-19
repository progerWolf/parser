<?php

namespace App\Http\Controllers;

use App\Http\Services\AutoService;
use App\Services\LocalSaveAsJsonService;
use Laminas\Diactoros\ServerRequest;
use Laminas\Diactoros\Response\JsonResponse;

class HomeController extends Controller
{
    public function index(ServerRequest $request): JsonResponse
    {
        $autorus = new AutoService();
        $saveJsonService = new LocalSaveAsJsonService();

        $params = $request->getQueryParams();

        if (!isset($params['query']) || empty($params['query'])) {
            return $this->jsonResponse(['message' => 'Query param must be filled', 'code' => 400]);
        }

        $autorus->setQuery($params['query']);
        $items = $autorus->getItems();

        if (!isset($items['code'])) {
            $saveJsonService->setData($items, 0);
            $saveJsonService->save();
        }

        return $this->jsonResponse($items);
    }
}
