<?php
namespace App\Controller;

use Doctrine\ORM\EntityManager;
use Slim\Views\Twig as View;

class SearchController
{
    protected $em;

    protected $sphinxSearch;

    protected $view;

    public function __construct(EntityManager $em, \PDO $sphinxSearch, View $view)
    {
        $this->em = $em;
        $this->sphinxSearch = $sphinxSearch;
        $this->view = $view;
    }

    public function searchAction($request, $response, $args = [])
    {
        $em = $this->em;
        $sphinxSearch = $this->sphinxSearch;

        $q = (isset($request->getQueryParams()['q'])) ? $request->getQueryParams()['q'] : '';

        $files = array();

        if (!empty($q)) {
            $query = $sphinxSearch->prepare("SELECT * FROM rt_files, index_files WHERE MATCH (:search) ORDER BY id DESC");
            $query->bindValue(':search', $q);
            $query->execute();
            $results = $query->fetchAll();

            foreach ($results as $result) {
                $files[] = $em->getRepository('App\Entity\File')->find($result['id']);
            }      
        }

        return $this->view->render($response, 'search.phtml', [
            'q' => $q,
            'files' => $files
        ]);
    }

    public function suggestAction($request, $response, $args = [])
    {

        $pdo = $this->sphinxSearch;

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
    }
}