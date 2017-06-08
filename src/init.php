<?php
require_once(__DIR__ . '/../vendor/autoload.php');
require_once(__DIR__ . '/../vendor/james-heinrich/getid3/getid3/getid3.php');

use Slim\Container;

use Doctrine\ORM\Tools\Setup;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\EntityManager;

use Slim\Views\PhpRenderer as View;
use Slim\Csrf\Guard as Csrf;

use App\Types\Ltree;

Type::addType('ltree', 'App\Types\Ltree');

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

    $entityManager->getConnection()->getDatabasePlatform()->registerDoctrineTypeMapping('ltree', 'ltree');

    return $entityManager;
};

$container['SphinxConnection'] = function ($c) {    
    $pdo = new \PDO('mysql:host=127.0.0.1;port=9306');
    $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

    return $pdo;
};

$container['View'] = function ($c) {
    return new View(__DIR__ . '/../templates');
};

$container['csrf'] = function ($c) {
    return new Csrf;
};

$container['getID3'] = function ($c) {
    return new getID3;
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