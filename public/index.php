<?php
require(__DIR__ . '/../src/init.php');

use Slim\App; 

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

    //path generation should be in model
    $last = $em->getRepository('App\Entity\File')->findOneBy([], ['id' => 'DESC']);

    if ($last) {
        $query = $em->createQuery("SELECT COUNT(f) FROM App\Entity\File f WHERE f.path = :path");
        $query->setParameter('path', $last->getPath());
        $count = $query->getSingleScalarResult();

        if ($count == 1000) {
            $path = (string) rand();

            mkdir("files/{$path}");
        } else {
            $path = $last->getPath();
        }
    } else {
        $path = (string) rand();

        mkdir("files/{$path}");
    }

    $file = $request->getUploadedFiles()['file'];

    $error = $file->getError();

    if ($error == 0) {
        $name = $file->getClientFilename();
        $size = $file->getSize();

        if (file_exists(__DIR__ . "/files/{$path}/{$name}")) {
            $path = (string) rand();

            mkdir("files/{$path}");
        }

        $file->moveTo(__DIR__ . "/files/$path/$name");

        $file = new File();
        $file->setName($name);
        $file->setSize($size);
        $file->setPath($path);

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

$app->run();