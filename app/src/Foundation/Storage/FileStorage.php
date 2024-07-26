<?php

namespace App\Foundation\Storage;

class FileStorage implements StorageInterface
{
    private string $path;

    public function getData(): array
    {
        if (!file_exists($this->path)) {
            return [];
        }

        $file = fopen($this->path, 'r');
        $data = fread($file, filesize($this->path));
        fclose($file);

        return json_decode($data, true);
    }

    public function saveData($data): void
    {
        $json = json_encode($data);
        $file = fopen($this->path, 'w');

        fwrite($file, $json);
        fclose($file);
    }

    public function configure(): void
    {
        $this->path = __DIR__ . '/../../../storage/state.json';
    }
}