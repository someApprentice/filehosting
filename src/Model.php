<?php
namespace App;

class Model
{

    public static function generatePath()
    {
        $path = "files/" . mb_substr(uniqid(), 9);

        mkdir($path);

        return $path;
    }

    public static function generateNewNameForFile($file)
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
}