<?php

use App\App;

require_once __DIR__ . '/../vendor/autoload.php';

$app = new App();

if (empty($_POST)) {
    $app->setSettings($_POST);
}
