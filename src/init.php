<?php
require_once(__DIR__ . '/../vendor/autoload.php');
require_once(__DIR__ . '/../vendor/james-heinrich/getid3/getid3/getid3.php');

use Slim\Container;

use Doctrine\ORM\Tools\Setup;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\EntityManager;

use Slim\Views\Twig as View;
use Slim\Views\TwigExtension;
use Slim\Csrf\Guard as Csrf;

use App\Types\Ltree;

use App\Controller\IndexController;
use App\Controller\DownloadController;
use App\Controller\SearchController;

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
    $view = new View(__DIR__ . '/../templates');

    $basePath = rtrim(str_ireplace('index.php', '', $c['request']->getUri()->getBasePath()), '/');
    $view->addExtension(new TwigExtension($c['router'], $basePath));

    return $view;
};

$container['csrf'] = function ($c) {
    return new Csrf;
};

$container['getID3'] = function ($c) {
    return new getID3;
};

$container['IndexController'] = function($c) {
    return new IndexController($c['EntityManager'], $c['SphinxConnection'], $c['getID3'], $c['csrf'], $c['View']);
};

$container['DownloadController'] = function($c) {
    return new DownloadController($c['EntityManager'], $c['csrf'], $c['View']);
};

$container['SearchController'] = function($c) {
    return new SearchController($c['EntityManager'], $c['SphinxConnection'], $c['View']);
};

$container['errorHandler'] = function ($c) {
    return function ($request, $response, $exception) use ($c) {
        return $c->get('View')->render($response, 'error.html', [])->withStatus(500);
    };
};

$container['notFoundHandler'] = function ($c) {
    return function ($requset, $responce) use ($c) {
        return $c->get('View')->render($responce, '404.html', [])->withStatus(404);
    };
};