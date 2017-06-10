<?php
namespace App\Controller;

require_once(__DIR__ . '/../../vendor/james-heinrich/getid3/getid3/getid3.php');

use Doctrine\ORM\EntityManager;
use Slim\Csrf\Guard;
use Slim\Views\Twig as View;

use App\Model;

use App\Entity\File;

class IndexController
{
    protected $em;

    protected $sphinxSearch;

    protected $getID3;

    protected $csrf;

    protected $view;

    public function __construct(EntityManager $em, \PDO $sphinxSearch, \getID3 $getID3, Guard $csrf, View $view)
    {
        $this->em = $em;
        $this->sphinxSearch = $sphinxSearch;
        $this->getID3 = $getID3;
        $this->csrf = $csrf;
        $this->view = $view;
    }

    public function indexAction($request, $response, $args = [])
    {
        $em = $this->em;

        $files = $em->getRepository('App\Entity\File')->findBy([], ['id' => 'DESC'], 100);

        $csrfNameKey = $this->csrf->getTokenNameKey();
        $csrfValueKey = $this->csrf->getTokenValueKey();
        $csrfName = $request->getAttribute($csrfNameKey);
        $csrfValue = $request->getAttribute($csrfValueKey);

        return $this->view->render($response, 'index.phtml', [
            'files' => $files,

            'csrfNameKey' => $csrfNameKey,
            'csrfValueKey' => $csrfValueKey,
            'csrfName' => $csrfName,
            'csrfValue' => $csrfValue
        ]);
    }

    public function uploadAction($request, $response, $args = [])
    {
        $em = $this->em;
        $sphinxSearch = $this->sphinxSearch;

        $getID3  = $this->getID3;

        $file = $request->getUploadedFiles()['file'];

        $error = $file->getError();

        if ($error == Model::FILE_UPLOAD_OK) {
            $originalName = $file->getClientFilename();
            $newName = Model::generateNewName($file);
            $size = $file->getSize();
            $path = Model::generatePathFor($newName);
            $mimetype = $file->getClientMediaType();

            mkdir(__DIR__ . "/../../public/files/$path");

            $file->moveTo(__DIR__ . "/../../public/files/$path/$newName");

            $info = $getID3->analyze(__DIR__ . "/../../public/files/$path/$newName");

            $file = new File();
            $file->setOriginalName($originalName);
            $file->setNewName($newName);
            $file->setDate();
            $file->setSize($size);
            $file->setPath("files/$path");
            $file->setMimeType($mimetype);
            $file->setInfo(json_encode($info, JSON_UNESCAPED_UNICODE));

            if ($file->isImage($mimetype)) {
                mkdir(__DIR__ . "/../../public/thumbnails/$path");

                $file->setThumbnail("thumbnails/$path/$newName");

                Model::generateThumbnail($file);
            }

            $em->persist($file);
            $em->flush();

            $file = $em->getRepository('App\Entity\File')->findOneBy(['path' => "files/$path", 'newname' => $newName]);

            $st = $sphinxSearch->prepare("INSERT INTO rt_files (id, originalname) VALUES (:id, :originalname)");
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
    }
}