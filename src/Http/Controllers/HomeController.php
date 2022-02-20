<?php

namespace App\Http\Controllers;

use App\Http\Services\AutoService;
use App\Services\LocalSaveAsJsonService;
use Laminas\Diactoros\ServerRequest;
use Laminas\Diactoros\Response\JsonResponse;

class HomeController extends Controller
{
    /**
     * Метод рализируюший парсинг данных из указанного сервиса
     * @queryParam query Искомая строка. Example: query=17177
     *
     * @param ServerRequest $request
     * @return JsonResponse
     */
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

        $saveJsonService->setData($items);
        $saveJsonService->save();

        return $this->jsonResponse($items);
    }
}
