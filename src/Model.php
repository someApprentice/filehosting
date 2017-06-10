<?php
namespace App;

class Model
{
    public static function generatePathFor(string $newname)
    {
        $path = mb_substr(uniqid(), 9);

        if (file_exists(__DIR__ . "/../public/files/$path/$newname")) {
            $path = self::generatePathForFile($file);
        }

        return $path;
    }

    public static function generateNewName($file)
    {
        $originalName = $file->getClientFileName();
        $newName = uniqid();

        $matches = array();

        $notAllowedExtensions = array(
            'php',
            'html',
            'phtml'
        );

        if (preg_match('/^(.+\.)(\w+)$/u', $originalName, $matches)) {
            $extension = $matches[2];

            if (in_array($extension, $notAllowedExtensions)) {
                $extension = 'txt';
            }

            $newName .= ".{$extension}";
        }

        return $newName;
    }

    public static function generateThumbnail(string $path)
    {
        $image = new \Imagick(__DIR__ . "/../public/$path");

        $thumbnailPath = preg_replace('/^files/', 'thumbnails', $path);

        if ($image->getImageFormat() == 'GIF') {
            $image = $image->coalesceImages();

            foreach ($image as $frame) {
                $frame->thumbnailImage(540, 0);
            }

            $image = $image->deconstructImages();
            $image->writeImages(__DIR__ . "/../public/$thumbnailPath", true);
        } else {
            $image->thumbnailImage(540, 0);

            $image->writeImage(__DIR__ . "/../public/$thumbnailPath");
        }

    }

    public static function isImage(string $type)
    {
        $imageTypes = array(
            'image/gif',
            'image/jpeg',
            'image/png',
            'image/bmp'
        );

        if (in_array($type, $imageTypes)) {
            return true;
        }

        return false;
    }

    public static function isAudio(string $type)
    {
        $audioTypes = array(
            'audio/mpeg',
            'audio/ogg',
            'audio/wav'
        );

        if (in_array($type, $audioTypes)) {
            return true;
        }

        return false;
    }

    public static function isVideo(string $type)
    {
        $audioTypes = array(
            'video/mp4',
            'video/webm',
            'video/ogg'
        );

        if (in_array($type, $audioTypes)) {
            return true;
        }

        return false;
    }
}