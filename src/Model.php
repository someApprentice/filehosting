<?php
namespace App;

use App\Entity\File;

class Model
{
    const FILE_UPLOAD_OK = 0;

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

    public static function generateThumbnail(File $file)
    {
        $image = new \Imagick(__DIR__ . "/../public/{$file->getPath()}/{$file->getNewName()}");

        $thumbnailPath = $file->getThumbnail();

        if ($image->getImageFormat() == 'GIF') {
            $image = $image->coalesceImages();

            foreach ($image as $frame) {
                $frame->thumbnailImage(540, 0);
            }

            $image = $image->deconstructImages();
            $image->writeImages(__DIR__ . "/../public/{$thumbnailPath}", true);
        } else {
            $image->thumbnailImage(540, 0);

            $image->writeImage(__DIR__ . "/../public/{$thumbnailPath}");
        }

    }
}