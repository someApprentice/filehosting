<?php
namespace App\Entity;

use App\Entity\File;

/**
* @Entity @Table(name="comments")
**/
class Comment
{
    /** @Id @Column(type="integer") @GeneratedValue **/
    protected $id;

    /**
    * @ManyToOne(targetEntity="App\Entity\File", inversedBy="comments")
    * @JoinColumn(name="file",  referencedColumnName="id")
    **/
    protected $file;

    /** @Column(type="string") **/
    protected $author;

    /** @Column(type="datetimetz") **/
    protected $date;

    /** @Column(type="text") **/
    protected $content;

    /** @Column(type="ltree", nullable=true) **/
    protected $tree;

    /** @Column(type="integer") **/
    protected $depth = 0;

    public function getId()
    {
        return $this->id;
    }

    public function getFile()
    {
        return $this->file;
    }

    public function setFile(File $file)
    {
        $this->file = $file;

        return $this;
    }

    public function getAuthor()
    {
        return $this->author;
    }

    public function setAuthor(string $author)
    {
        $this->author = $author;

        return $this;
    }

    public function getDate()
    {
        return $this->date;
    }

    public function setDate()
    {
        $this->date = new \Datetime("now");

        return $this;
    }

    public function getContent()
    {
        return $this->content;
    }

    public function setContent(string $content)
    {
        $this->content = $content;

        return $this;
    }

    public function getTree()
    {
        return $this->tree;
    }

    public function setTree(string $tree)
    {
        $this->tree = $tree;
    }

    public function getDepth()
    {
        return $this->depth;
    }

    public function setDepth(int $depth)
    {
        $this->depth = $depth;

        return $this;
    }
}