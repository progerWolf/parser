<?php

namespace App\Services;

use JetBrains\PhpStorm\Pure;

class LocalSaveAsJsonService
{
    private array $data;

    public function setData(array $data, int|null $element = null): void
    {
        if ($element === null) {
            $this->data = $data;
        } else {
            $this->data = $data[$element];
        }
    }

    public function save()
    {
        $data = json_encode(['data' => $this->data]);
        file_put_contents($this->filePath(), $data);
    }

    #[Pure] private function filePath(): string
    {
        return __DIR__ . '/../../storage/' . $this->generateFileName();
    }

    private function generateFileName(): string
    {
        return 'items-' . date('Y-m-d H:i:s') . '.json';
    }

}
