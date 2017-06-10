<?php
require_once(__DIR__ . '/../src/init.php');

use Slim\App;

use App\Model;

use App\Entity\File;
use App\Entity\Comment;

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
        $file->setInfo(json_encode($info, JSON_UNESCAPED_UNICODE));

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
        $files = $em->getRepository('App\Entity\File')->findBy([], ['id' => 'DESC'], 100);

        $csrfNameKey = $this->csrf->getTokenNameKey();
        $csrfValueKey = $this->csrf->getTokenValueKey();
        $csrfName = $request->getAttribute($csrfNameKey);
        $csrfValue = $request->getAttribute($csrfValueKey);

        return $this->get('View')->render($response, 'index.phtml', [
            'files' => $files,
            'error' => $error,

            'csrfNameKey' => $csrfNameKey,
            'csrfValueKey' => $csrfValueKey,
            'csrfName' => $csrfName,
            'csrfValue' => $csrfValue
        ]);
    }
})->add($container->get('csrf'));

$app->get('/download/{id}', function ($request, $response, $args) {
    $em = $this->get('EntityManager');

    $file = $em->getRepository('App\Entity\File')->find($args['id']);

    $csrfNameKey = $this->csrf->getTokenNameKey();
    $csrfValueKey = $this->csrf->getTokenValueKey();
    $csrfName = $request->getAttribute($csrfNameKey);
    $csrfValue = $request->getAttribute($csrfValueKey);
    
    return $this->get('View')->render($response, 'download.phtml', [
        'file' => $file,
        'model' => new Model,

        'csrfNameKey' => $csrfNameKey,
        'csrfValueKey' => $csrfValueKey,
        'csrfName' => $csrfName,
        'csrfValue' => $csrfValue
    ]);
})->add($container->get('csrf'))->setName('download');

//comment
$app->post('/download/{id}', function ($request, $response, $args) {
    $em = $this->get('EntityManager');

    $post = $request->getParsedBody();

    $post['author'] = (isset($post['author']) and is_scalar($post['author'])) ? $post['author'] : '';
    $post['content'] = (isset($post['content']) and is_scalar($post['content'])) ? $post['content'] : '';

    $file = $em->getRepository('App\Entity\File')->find($args['id']);

    $comment = new Comment();
    $comment->setFile($file);
    $comment->setAuthor($post['author']);
    $comment->setContent($post['content']);
    $comment->setDate();

    $em->persist($comment);
    $em->flush();

    $comment->setTree("{$file->getId()}.{$comment->getId()}");

    if (isset($post['parent']) and is_numeric($post['parent'])) {
        $parent = $em->getRepository('App\Entity\Comment')->findOneBy(['id' => $post['parent'],'file' => $file->getId()]);

        if ($parent) {
            $tree = $parent->getTree();

            $tree .= ".{$comment->getId()}";

            $comment->setTree($tree);
            $comment->setDepth($parent->getDepth() + 1);
        } else {
            throw new \Exception("No such parent");
        }
    }

    $em->persist($comment);
    $em->flush();    

    return $response->withHeader('Location', "/download/{$file->getId()}");
})->add($container->get('csrf'));

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

    if (!empty($q)) {
        $query = $pdo->prepare("SELECT * FROM rt_files, index_files WHERE MATCH (:search) ORDER BY id DESC");
        $query->bindValue(':search', $q);
        $query->execute();
        $results = $query->fetchAll();

        foreach ($results as $result) {
            $files[] = $em->getRepository('App\Entity\File')->find($result['id']);
        }      
    }

    return $this->get('View')->render($response, 'search.phtml', [
        'router' => $this->get('router'),
        'q' => $q,
        'files' => $files
    ]);
});

$app->get('/suggest', function($request, $response, $args) {
    $pdo = $this->get('SphinxConnection');

    $array = array();

    $q = (isset($request->getQueryParams()['term'])) ? $request->getQueryParams()['term'] : '';

    $aq = explode(' ',$q);
    if(strlen($aq[count($aq)-1])<3){
        $q = $q;
    }else{
        $q = $q.'*';
    }

    $query = $pdo->prepare("SELECT * FROM filescomplete WHERE MATCH (:search) ORDER BY id DESC");
    $query->bindValue(':search', $q);
    $query->execute();
    $results = $query->fetchAll();
    
    $unique_results = array();

    foreach ($results as $result) {
        if (!in_array($result['originalname'], $array)) {
            $array[] = $result['originalname'];
            $unique_results[] = array('label' => $result['originalname']);
        }
    }

    echo json_encode($unique_results);

    die();
});

$app->run();