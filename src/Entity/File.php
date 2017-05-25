<?php
namespace App\Entity;

/**
* @Entity @Table(name="files")
**/
class File
{
    /** @Id @Column(type="integer") @GeneratedValue **/
    protected $id;

    /** @Column(type="string") **/
    protected $originalname;

    /** @Column(type="string") **/
    protected $newname;

    /** @Column(type="integer") **/
    protected $size;

    /** @Column(type="string") **/
    protected $path;

    public function getId()
    {
        return $this->id;
    }

    public function getOriginalName()
    {
        return $this->originalname;
    }

    public function setOriginalName($originalname)
    {
        $this->originalname = $originalname;

        return $this;
    }
    public function getNewName()
    {
        return $this->newname;
    }

    public function setNewName($newname)
    {
        $this->newname = $newname;

        return $this;
    }

    public function getSize()
    {
        return $this->size;
    }

    public function setSize($size)
    {
        $this->size = $size;

        return $this;
    }

    public function getPath()
    {
        return $this->path;
    }

    public function setPath($path)
    {
        $this->path = $path;
        
        return $this;
    }
}