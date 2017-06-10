<?php
namespace App\Controller;

use Doctrine\ORM\EntityManager;
use Slim\Csrf\Guard;
use Slim\Views\Twig as View;

use App\Entity\File;
use App\Entity\Comment;

class DownloadController
{
    protected $em;

    protected $csrf;

    protected $view;

    public function __construct(EntityManager $em, Guard $csrf, View $view)
    {
        $this->em = $em;
        $this->csrf = $csrf;
        $this->view = $view;
    }

    public function downloadPage($request, $response, $args = [])
    {
        $em = $this->em;

        $file = $em->getRepository('App\Entity\File')->find($args['id']);

        $csrfNameKey = $this->csrf->getTokenNameKey();
        $csrfValueKey = $this->csrf->getTokenValueKey();
        $csrfName = $request->getAttribute($csrfNameKey);
        $csrfValue = $request->getAttribute($csrfValueKey);
        
        return $this->view->render($response, 'download.phtml', [
            'file' => $file,

            'csrfNameKey' => $csrfNameKey,
            'csrfValueKey' => $csrfValueKey,
            'csrfName' => $csrfName,
            'csrfValue' => $csrfValue
        ]);
    }

    public function commentAction($request, $response, $args= [])
    {
        $em = $this->em;

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
    }

    public function downloadAction($request, $response, $args = [])
    {
        $em = $this->em;

        $file = $em->getRepository('App\Entity\File')->find($args['id']);

        header("Content-disposition: attachment; filename={$file->getOriginalName()}");
        header("Content-Type: {$file->getMimeType()}");

        readfile(__DIR__ . "/../../public/{$file->getPath()}/{$file->getNewName()}");
    }
}