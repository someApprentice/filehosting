<?php
namespace App;

class Model
{
    public static function generateRandomString($length)
    {
        $string = '';
        $characters =  '0123456789abcdefghijklmnopqrstuvwxyz';

        for ($i = 0; $i < $length; $i++) {
            $string .= $characters[mt_rand(0, strlen($characters) - 1)];
        }

        return $string;
    }

    public static function generatePath()
    {
        $path = "files/" . Model::generateRandomString(4);

        mkdir($path);

        return $path;
    }

    public static function generateNewNameForFile($file)
    {
        $originalName = $file->getClientFileName();
        $newName = Model::generateRandomString(8);

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