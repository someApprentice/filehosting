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

    /** @Column(type="string") **/
    protected $mimetype;

    /** @Column(type="string", nullable=true) **/
    protected $thumbnail;

    /** @Column(type="json_array") **/
    protected $info;

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

    public function getMimeType()
    {
        return $this->mimetype;
    }

    public function setMimeType($mimetype)
    {
        $this->mimetype = $mimetype;

        return $this;
    }

    public function getThumbnail()
    {
        return $this->thumbnail;
    }

    public function setThumbnail($thumbnail)
    {
        $this->thumbnail = $thumbnail;

        return $this;
    }

    public function getInfo()
    {
        return $this->info;
    }

    public function setInfo($info)
    {
        $this->info = $info;

        return $this;
    }
}