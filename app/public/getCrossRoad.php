<?php

use App\App;

require_once __DIR__ . "/../vendor/autoload.php";
try {

    $app = new App();
    $app->run();
    echo json_encode($app->toArray());

} catch (\Throwable $exception) {
    $whoops = new \Whoops\Run();
    $whoops->allowQuit(false);
    $whoops->writeToOutput(false);
    $whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler());
    $html = $whoops->handleException($exception);
    echo $html;
}

