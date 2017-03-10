<?php

/* Load Required Files */
require __DIR__ . '/../lib/vendor/autoload.php';
require "BotResponse.php";

/* Boot Up Apps*/

$dotenv = new Dotenv\Dotenv(__DIR__);
$dotenv->load();

$configs =  [
	'settings' => [
        'displayErrorDetails' => true
        ]
    ];
$app = new Slim\App($configs);



/* Routes */
$app->get('/', function ($request, $response) {

    echo "KLM Project";

});

$app->post('/', function ($request, $response) {

    $response = new Main();
    $handler = $response->mainBot();

    return $handler;
});

$app->run();