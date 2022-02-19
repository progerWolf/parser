<?php

namespace App\Http\Controllers;

use Laminas\Diactoros\Response\JsonResponse;

class Controller
{
    protected function jsonResponse(array $data, int|string $code = 200): JsonResponse
    {
        if (isset($data['code'])) {
            $code = $data['code'];
        }
        return new JsonResponse($data, $code);
    }
}
