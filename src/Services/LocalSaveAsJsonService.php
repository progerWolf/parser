<?php

namespace App\Services;

use JetBrains\PhpStorm\Pure;

class LocalSaveAsJsonService
{
    /*
     * Перемення данных для сохранения в файл
     */
    protected array $data;


    /*
     * Сеттер данных в $this->data
     */
    public function setData(array $data, int|null $element = null): void
    {
        if ($element === null) {
            $this->data = $data;
        } else {
            $this->data = $data[$element];
        }
    }

    /**
     * Метод сохронения данные
     */
    public function save()
    {
        $data = json_encode(['data' => $this->data]);
        file_put_contents($this->filePath(), $data);
    }

    /*
     * Метод создания получения польного пути до файла
     */
    #[Pure] protected function filePath(): string
    {
        return __DIR__ . '/../../storage/' . $this->generateFileName();
    }

    /**
     * Метод генерируюший имя файла
     */
    protected function generateFileName(): string
    {
        return 'items-' . date('Y-m-d H:i:s') . '.json';
    }

}
