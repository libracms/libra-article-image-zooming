<?php

/*
 * eJoom.com
 * This source file is subject to the new BSD license.
 */

namespace LibraArticleImageZooming\Model;

/**
 * Description of Zooming
 *
 * @author duke
 */
class Zooming
{
    public static $imagesRootDir    = '/images/stories';
    public static $thumbnailDirName = '_thumbnails';
    public static $imagesDirName    = 'images';
    public static $basePath = null;

    public static function prepare()
    {
        $filename = 'public/images/stories/_thumbnails';
        if (!file_exists($filename)) mkdir($filename);
    }

    public static function createThumnail($src, $width = null, $height = null)
    {
        list($basePath,$imagePath) = explode(static::$imagesRootDir . '/' . static::$imagesDirName, $src);
        $thumbSrc = static::$imagesRootDir . '/' . static::$thumbnailDirName . $imagePath;;
        $thumbPath = 'public' . $thumbSrc;
        $origPath = 'public' . static::$imagesRootDir . '/' . static::$imagesDirName . $imagePath;
        //or simpler:         $origPath = 'public' . $src;
        if (!file_exists($thumbPath)) {
            $image = new SimpleImage();
            $image->load($origPath);
            $image->resize($width, $height);
            $image->save($thumbPath);
            return $thumbSrc;
        }
        return false;
    }

    public static function zooming($content)
    {
        static::prepare();
        $dom = new \DOMDocument();
        $dom->loadHTML($content);
        $imgs = $dom->getElementsByTagName('img');
        foreach ($imgs as $img) {
            $src = $img->getAttribute('src');
            $style = $img->getAttribute('style');
            preg_match('/width\s*:\s*(?P<width>\d+)px/', $style, $matches);//width: 200px; height: 289px;
            $width  = $matches['width'];
            preg_match('/height\s*:\s*(?P<height>\d+)px/', $style, $matches);
            $height = $matches['height'];
            $newSrc = static::createThumnail($src, $width, $height);
            if ($newSrc) $img->setAttribute('src', $newSrc);
            if ($img->parentNode->tagName == 'a') continue;
            $a = $dom->createElement('a');
            $a->setAttribute('href', $src);
            $a->appendChild(clone $img);
            $img->parentNode->replaceChild($a, $img);
        }
        $body = $dom->getElementsByTagName('body')->item(0);
        $newContent = $dom->saveXML($body);
        $newContent = str_replace('<body>', '', $newContent);
        $newContent = str_replace('</body>', '', $newContent);
        //"/images/stories/images/Untitled1.jpg"
        return $newContent;
    }

}
