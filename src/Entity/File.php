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

    /** @Column(type="datetimetz") **/
    protected $date;

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

    /** @OneToMany(targetEntity="App\Entity\Comment", mappedBy="file") @OrderBy({"tree" = "ASC", "date" = "ASC"})**/
    protected $comments;

    public function getId()
    {
        return $this->id;
    }

    public function getOriginalName()
    {
        return $this->originalname;
    }

    public function setOriginalName(string $originalname)
    {
        $this->originalname = $originalname;

        return $this;
    }
    public function getNewName()
    {
        return $this->newname;
    }

    public function setNewName(string $newname)
    {
        $this->newname = $newname;

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

    public function getSize()
    {
        return $this->size;
    }

    public function setSize(int $size)
    {
        $this->size = $size;

        return $this;
    }

    public function getPath()
    {
        return $this->path;
    }

    public function setPath(string $path)
    {
        $this->path = $path;
        
        return $this;
    }

    public function getMimeType()
    {
        return $this->mimetype;
    }

    public function setMimeType(string $mimetype)
    {
        $this->mimetype = $mimetype;

        return $this;
    }

    public function getThumbnail()
    {
        return $this->thumbnail;
    }

    public function setThumbnail(string $thumbnail)
    {
        $this->thumbnail = $thumbnail;

        return $this;
    }

    public function getInfo()
    {
        return json_decode($this->info);
    }

    public function setInfo(string $info)
    {
        $this->info = $info;

        return $this;
    }

    public function getComments()
    {
        return $this->comments;
    }

    public function isImage()
    {
        $imageTypes = array(
            'image/gif',
            'image/jpeg',
            'image/png',
            'image/bmp'
        );

        if (in_array($this->mimetype, $imageTypes)) {
            return true;
        }

        return false;
    }

    public function isAudio()
    {
        $audioTypes = array(
            'audio/mp3',
            'audio/mpeg',
            'audio/ogg',
            'audio/wav'
        );

        if (in_array($this->mimetype, $audioTypes)) {
            return true;
        }

        return false;
    }

    public function isVideo()
    {
        $audioTypes = array(
            'video/mp4',
            'video/webm',
            'video/ogg'
        );

        if (in_array($this->mimetype, $audioTypes)) {
            return true;
        }

        return false;
    }
}