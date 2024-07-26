<?php

use App\App;

require_once __DIR__ . "/../vendor/autoload.php";

$app = new App();
$app->run();
echo json_encode($app->toArray());


