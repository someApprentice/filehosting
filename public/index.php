<?php
require_once(__DIR__ . '/../src/init.php');

use Slim\App; 

use App\Model;

use App\Entity\File;

session_start();

$app = new App($container);

$app->add($container->get('csrf'));

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
});

//fat controller
$app->post('/', function ($request, $response) {
    $em = $this->get('EntityManager');

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

        $file = new File();
        $file->setOriginalName($originalName);
        $file->setNewName($newName);
        $file->setSize($size);
        $file->setPath("files/$path");
        $file->setMimeType($mimetype);

        if (Model::isImage($mimetype)) {
            $image = new \Imagick(__DIR__ . "/files/$path/$newName");

            mkdir("thumbnails/$path");

            if ($mimetype == 'image/gif') {
                $image = $image->coalesceImages();

                foreach ($image as $frame) {
                    $frame->thumbnailImage(540, 0);
                }

                $image = $image->deconstructImages();
                $image->writeImages(__DIR__ . "/thumbnails/$path/$newName", true);
            } else {
                $frame->thumbnailImage(540, 0);

                $image->writeImage(__DIR__ . "/thumbnails/$path/$newName");
            }

            $file->setThumbnail("thumbnails/$path/$newName");
        }

        $em->persist($file);
        $em->flush();

        return $response->withHeader('Location', '/');
    } else {
        return $this->get('View')->render($response, 'index.phtml', [
            'error' => $error
        ]);
    }
});

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

$app->run();