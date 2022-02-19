<?php

namespace App\Services;

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
        if (file_exists($this->filePath())){
            $fileData = file_get_contents($this->filePath());
            $fileData = json_decode($fileData, true);
            $fileData['data'][] = $this->data;
            $data = json_encode($fileData);
        } else {
            $data = json_encode(['data' => $this->data]);
        }
        file_put_contents($this->filePath(), $data);
    }

    private function filePath(): string
    {
        return __DIR__ . '/../../storage/' . 'items.json';
    }

}
