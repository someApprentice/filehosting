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
        if ($file->isImage()) {
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
        } else {
            throw new \Exception("File is not image");
            
        }
    }

    public static function generateThumbnailFromRaw(string $raw, string $path)
    {
        $image = new \Imagick();

        $image->readImageBlob($raw);

        $image->writeImage(__DIR__ . "/../public/{$path}");
    }

    public static function fillAudioInfoFromGetID3(array $analyze)
    {
        $info = array();

        $allowedInfoKeys = array(
            'dataformat',
            'sample_rate',
            'bitrate',
            'channelmode'
        );

        $allowedTagKeys = array(
            'artist',
            'album',
            'title',
            'track_number',
            'year'
        );

        foreach ($analyze['audio'] as $key => $value) {
            if (in_array($key, $allowedInfoKeys)) {
                $info['info'][$key] = $value;
            }
        }

        foreach ($analyze['tags']['id3v2'] as $key => $value) {
            if (in_array($key, $allowedTagKeys)) {
                $info['tags']['id3v2'][$key] = $value[0];
            }
        }

        return $info;
    }

    public static function fillImageInfoFromGetID3(array $analyze)
    {
        $info = array();

        $info['format'] = $analyze['fileformat'];
        $info['resolution_x'] = $analyze['video']['resolution_x']; 
        $info['resolution_y'] = $analyze['video']['resolution_y'];
    
        return $info;
    }

    public static function fillVideoInfoFromGetID3(array $analyze)
    {
        $info = array();

        $info['codec'] = $analyze['video']['fourcc'];
        $info['resolution_x'] = $analyze['video']['resolution_x'];
        $info['resolution_y'] = $analyze['video']['resolution_y'];
        $info['frame_rate'] = $analyze['video']['frame_rate'];
        $info['bitrate'] = $analyze['bitrate'];

        return $info;
    }
}