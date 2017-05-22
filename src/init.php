<?php
require(__DIR__ . '/../vendor/autoload.php');

use Slim\Container;

use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;

use Slim\Views\PhpRenderer as View;
use Slim\Csrf\Guard as Csrf;

$config = [
    'settings' => [
        'displayErrorDetails' => true,
        'addContentLengthHeader' => true
    ],

    'db' => parse_ini_file(__DIR__ . '/../config/config.ini')
];

$container = new Container($config);

$container['EntityManager'] = function ($c) {
    $paths = array(__DIR__ . "/Entity/");
    $isDevMode = false;

    $config = $c['db'];

    $metaConfig = Setup::createAnnotationMetadataConfiguration($paths, $isDevMode);
    $entityManager = EntityManager::create($config, $metaConfig);

    return $entityManager;
};

$container['View'] = function ($c) {
    return new View(__DIR__ . '/../templates');
};

$container['csrf'] = function ($c) {
    return new Csrf;
};


//for future implemetation
$container['errorHandler'] = function ($c) {
    return function ($request, $response, $exception) use ($c) {
        return $c['response']->withStatus(500)
                             ->withHeader('Content-Type', 'text/html')
                             ->write('Something went wrong!');
    };
};

$container['notFoundHandler'] = function ($c) {
    return function ($requset, $responce) use ($c) {
        return $c['response']->withStatus(404)->withHeader('Content-Type', 'text-html')->write('Page not found');
    };
};