<?php

namespace App\Foundation\Storage;

interface StorageInterface
{
    public function getData();

    public function saveData(array $data);

    public function configure(): void;
}