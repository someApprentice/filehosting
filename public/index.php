<?php
require_once(__DIR__ . '/../src/init.php');

use Slim\App; 

use App\Model;

use App\Entity\File;

session_start();

$app = new App($container);

$app->get('/', function ($request, $response) {
    $em = $this->get('EntityManager');

    $files = $em->getRepository('App\Entity\File')->findBy([], ['id' => 'DESC'], 100);

    $csrfNameKey = $this->csrf->getTokenNameKey();
    $csrfValueKey = $this->csrf->getTokenValueKey();
    $csrfName = $request->getAttribute($csrfNameKey);
    $csrfValue = $request->getAttribute($csrfValueKey);

    return $this->get('View')->render($response, 'index.phtml', [
        'router' => $this->get('router'),
        'files' => $files,

        'csrfNameKey' => $csrfNameKey,
        'csrfValueKey' => $csrfValueKey,
        'csrfName' => $csrfName,
        'csrfValue' => $csrfValue
    ]);
})->add($container->get('csrf'));

//fat controller
$app->post('/', function ($request, $response) {
    $em = $this->get('EntityManager');
    $pdo = $this->get('SphinxConnection');

    $getID3  = $this->get('getID3');

    $file = $request->getUploadedFiles()['file'];

    $error = $file->getError();

    if ($error == 0) {
        $originalName = $file->getClientFilename();
        $newName = Model::generateNewNameForFile($file);
        $size = $file->getSize();
        $path = Model::generatePath();
        $mimetype = $file->getClientMediaType();

        mkdir("files/$path");

        $file->moveTo(__DIR__ . "/files/$path/$newName");

        $info = $getID3->analyze(__DIR__ . "/files/$path/$newName");

        $file = new File();
        $file->setOriginalName($originalName);
        $file->setNewName($newName);
        $file->setSize($size);
        $file->setPath("files/$path");
        $file->setMimeType($mimetype);
        $file->setInfo($info);

        if (Model::isImage($mimetype)) {
            mkdir("thumbnails/$path");

            Model::generateThumbnail("files/$path/$newName");

            $file->setThumbnail("thumbnails/$path/$newName");
        }

        $em->persist($file);
        $em->flush();

        $file = $em->getRepository('App\Entity\File')->findOneBy(['path' => "files/$path", 'newname' => $newName]);

        $st = $pdo->prepare("INSERT INTO rt_files (id, originalname) VALUES (:id, :originalname)");
        $st->bindValue(':id', $file->getId());
        $st->bindValue(':originalname', $file->getOriginalName());
        $st->execute();

        return $response->withHeader('Location', '/');
    } else {
        return $this->get('View')->render($response, 'index.phtml', [
            'error' => $error
        ]);
    }
})->add($container->get('csrf'));

$app->get('/download/{id}', function ($request, $response, $args) {
    $em = $this->get('EntityManager');

    $file = $em->getRepository('App\Entity\File')->find($args['id']);
    
    return $this->get('View')->render($response, 'download.phtml', [
        'router' => $this->get('router'),
        'file' => $file
    ]);
})->setName('download');

$app->get('/dwnld/{id}', function($request, $response, $args) {
    $em = $this->get('EntityManager');

    $file = $em->getRepository('App\Entity\File')->find($args['id']);

    header("Content-disposition: attachment; filename={$file->getOriginalName()}");
    header("Content-Type: {$file->getMimeType()}");

    readfile(__DIR__ . "/{$file->getPath()}/{$file->getNewName()}");
})->setName('dwnld');

$app->get('/search', function($request, $response, $args) {
    $em = $this->get('EntityManager');
    $pdo = $this->get('SphinxConnection');

    $q = (isset($request->getQueryParams()['q'])) ? $request->getQueryParams()['q'] : '';

    $files = array();

    $rt_query = $pdo->prepare("SELECT * FROM rt_files WHERE MATCH (:search) ORDER BY id DESC");
    $rt_query->bindValue(':search', $q);
    $rt_query->execute();
    $rt_results = $rt_query->fetchAll();

    $index_query = $pdo->prepare("SELECT * FROM index_files WHERE MATCH (:search) ORDER BY id DESC");
    $index_query->bindValue(':search', $q);
    $index_query->execute();
    $index_results = $index_query->fetchAll();

    $results = array_merge($rt_results, $index_results);

    foreach ($results as $result) {
        $files[] = $em->getRepository('App\Entity\File')->find($result['id']);
    }

    return $this->get('View')->render($response, 'search.phtml', [
        'router' => $this->get('router'),
        'q' => $q,
        'files' => $files
    ]);
});

$app->run();