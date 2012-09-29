<?php

/*
 * eJoom.com
 * This source file is subject to the new BSD license.
 */

namespace LibraArticleImageZooming\Model;

use DOMDocument;
use DOMXPath;
use phpQuery;
use phpQueryObject;

/**
 * Description of Zooming
 *
 * @author duke
 */
class Zooming
{
    protected $imagesRootDir    = '/images/stories';
    protected $thumbnailDirName = '_thumbnails';
    protected $imagesDirName    = 'images';
    protected $basePath = null;
    protected $class = 'zoom';

    protected $content;
    protected $selector = 'a.zoom > img';
    protected $xpath = '//a[@class="zoom"]/img';


    public function setOptions($config = array())
    {

    }

    public function prepare()
    {
        $filename = 'public/images/stories/_thumbnails';
        if (!file_exists($filename)) mkdir($filename);
    }

    public function createThumbnail($src, $width = null, $height = null)
    {
        list($basePath,$imagePath) = explode($this->imagesRootDir . '/' . $this->imagesDirName, $src);
        $thumbSrc = $this->imagesRootDir . '/' . $this->thumbnailDirName . $imagePath;;
        $thumbPath = 'public' . $thumbSrc;
        $origPath = 'public' . $this->imagesRootDir . '/' . $this->imagesDirName . $imagePath;
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

    /**
     * Strip a tags with zoomed image from content
     * @param $content
     * @return bool
     */
    public function revert($content)
    {
        $dom = new DOMDocument;
        $dom->loadHTML($content);
        $domXpath = new DOMXPath($dom);
        $images = $domXpath->query($this->xpath);
        if ($images->length == 0) return false;

        //$anchors = $dom->getElementsByTagName('a');
        //$anchors = $domXpath->query('//a');
        //$images2 = $domXpath->query('a');
        //if ($anchors->length == 0) return false;
        //foreach ($anchors as $a) {
        foreach ($images as $img) {
            //if ($a->getAttribute('class') != $this->class) continue;
            //$img = clone $a->firstChild;
            $a = $img->parentNode;
            $img->setAttribute('src', $a->getAttribute('href'));
            //$img->setAttribute('class', $this->class);
            $a->parentNode->replaceChild($img, $a);
        }
        $body = $dom->getElementsByTagName('body')->item(0);
        $newContent = $dom->saveXML($body);
        $newContent = str_replace('<body>', '', $newContent);
        $newContent = str_replace('</body>', '', $newContent);
        return $newContent;
    }

    public function convert($content)
    {
        //$aa = $this->convert2($content);
        $this->prepare();
        $dom = new DOMDocument();
        $dom->loadHTML($content);
        $images = $dom->getElementsByTagName('img');
        if ($images->length == 0) return false;
        foreach ($images as $img) {
            //test for containing class 'zoom'
            $class = $img->getAttribute('class');
            if (!preg_match("/\s?$this->class\s?/", $class)) continue;
            
            $src = $img->getAttribute('src');
            $style = $img->getAttribute('style');
            preg_match('/width\s*:\s*(?P<width>\d+)px/', $style, $matches);//width: 200px; height: 289px;
            $width  = $matches['width'];
            preg_match('/height\s*:\s*(?P<height>\d+)px/', $style, $matches);
            $height = $matches['height'];
            $newSrc = $this->createThumbnail($src, $width, $height);
            if ($newSrc) $img->setAttribute('src', $newSrc);
            if ($img->parentNode->tagName == 'a') continue;  //don't do if it has a link already
            $a = $dom->createElement('a');
            $a->setAttribute('href', $src);
            $a->setAttribute('class', $this->class);
            $a->appendChild(clone $img);
            $img->parentNode->replaceChild($a, $img);
        }
        $body = $dom->getElementsByTagName('body')->item(0);
        $newContent = $dom->saveXML($body);
        $newContent = str_replace('<body>', '', $newContent);
        $newContent = str_replace('</body>', '', $newContent);
        //"/images/stories/images/Untitled1.jpg"
        return $newContent;
        //<p>&#13; setete<a href="/images/stories/images/Untitled1.jpg" class="zoom"><img alt="" src="/images/stories/images/Untitled1.jpg" style="width: 200px; height: 289px;"/></a></p>
    }

    public static function zooming($content)
    {
        static::prepare();
        $dom = new DOMDocument();
        $dom->loadHTML($content);
        $imgs = $dom->getElementsByTagName('img');
        if ($imgs->length == 0) return false;
        foreach ($imgs as $img) {
            $src = $img->getAttribute('src');
            $style = $img->getAttribute('style');
            preg_match('/width\s*:\s*(?P<width>\d+)px/', $style, $matches);//width: 200px; height: 289px;
            $width  = $matches['width'];
            preg_match('/height\s*:\s*(?P<height>\d+)px/', $style, $matches);
            $height = $matches['height'];
            $newSrc = static::createThumbnail($src, $width, $height);
            if ($newSrc) $img->setAttribute('src', $newSrc);
            if ($img->parentNode->tagName == 'a') continue;  //don't do if it has a link already
            $a = $dom->createElement('a');
            $a->setAttribute('href', $src);
            $a->setAttribute('class', $this->class);
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
