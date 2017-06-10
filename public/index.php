<?php
require_once(__DIR__ . '/../src/init.php');

use Slim\App;

use App\Model;

use App\Entity\File;
use App\Entity\Comment;

session_start();

$app = new App($container);

$app->get('/', 'IndexController:indexAction')->add($container->get('csrf'));
$app->post('/', 'IndexController:uploadAction')->add($container->get('csrf'));

$app->get('/download/{id}', 'DownloadController:downloadPage')->add($container->get('csrf'))->setName('download');
$app->post('/download/{id}', 'DownloadController:commentAction')->add($container->get('csrf'));
$app->get('/dwnld/{id}', 'DownloadController:downloadAction')->setName('dwnld');

$app->get('/search', 'SearchController:searchAction');
$app->get('/suggest', 'SearchController:suggestAction');

$app->run();