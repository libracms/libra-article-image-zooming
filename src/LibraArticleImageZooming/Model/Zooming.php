<?php

/*
 * eJoom.com
 * This source file is subject to the new BSD license.
 */

namespace LibraArticleImageZooming\Model;

use DOMDocument;
use DOMXPath;

/**
 * Description of Zooming
 *
 * @author duke
 */
class Zooming
{
    protected $imagesRootDir    = '/stories';
    protected $thumbnailDirName = '_thumbnails';
    protected $imagesDirName    = 'images';
    protected $basePath = null;
    protected $class = 'zoom';

    protected $content;
    protected $selector = 'a.zoom > img';
    protected $xpath = '//a[@class="zoom"]/img';

    /**
     * for detect charset properly
     * @var string
     */
    protected $htmlMeta = '<head><meta http-equiv="content-type" content="text/html; charset=utf-8" /></head>';

    public function setOptions($config = array())
    {

    }

    /**
     * Divide path to name and extension
     * Append number of image
     * If present suffix also prepend it
     *
     * @param string $imagePath
     * @param id $suffix
     * @param null|int $count
     * @return string
     */
    public function formUniquePath($imagePath, $count, $suffix = null)
    {
        $imagePathName = substr($imagePath, 0, -4);
        $imageExt = substr($imagePath, -3, 3);
        $prefix = "_$count";
        if ($suffix !== null) {
            $prefix = "_$suffix" . $prefix;
        }
        $imagePath = $imagePathName . $prefix . '.' . $imageExt;
        return $imagePath;
    }

    /**
     *
     * @param string $src
     * @param int $width
     * @param int $height
     * @param id $suffix
     * @param int $count
     * @return string|boolean
     */
    public function createThumbnail($src, $width = null, $height = null, $count = null, $suffix = null)
    {
        $src = urldecode($src);
        if (preg_match('%https?://(?P<path>.*)%', $src, $matches)) {
            $origPath = $src;
            $thumbSrc = $this->imagesRootDir . '/' . $this->thumbnailDirName . '/' . $matches['path'];
        } else {
            list($basePath,$imagePath) = explode($this->imagesRootDir . '/' . $this->imagesDirName, $src);
            $thumbSrc = $this->imagesRootDir . '/' . $this->thumbnailDirName . $imagePath;;
            $origPath = 'public' . $this->imagesRootDir . '/' . $this->imagesDirName . $imagePath;
        }
        if ($count !== null) {
            $thumbSrc = $this->formUniquePath($thumbSrc, $count, $suffix);
        }
        $thumbPath = 'public' . $thumbSrc;
        $origSize = getimagesize($origPath);
        if ($origSize[0] == $width && $origSize[1] == $height) {
            return false; //the same width hence do nothing
        }
        if (!file_exists($thumbPath)) {
            mkdir(dirname($thumbPath), 0777, true);
            $image = new SimpleImage();
            $image->load($origPath);
            $image->resize($width, $height);
            $image->save($thumbPath);
        } else {
            $size = getimagesize($thumbPath);
            if ($size[0] != $width || $size[1] != $height) {
                $image = new SimpleImage();
                $image->load($origPath);
                $image->resize($width, $height);
                $image->save($thumbPath);
            }
        }
        return $thumbSrc;
    }

    /**
     * Strip a tags with zoomed image from content
     * @param $content
     * @return bool
     */
    public function revert($content)
    {
        $dom = new DOMDocument(null, 'utf-8');
        $dom->loadHTML($this->htmlMeta . $content);
        $domXpath = new DOMXPath($dom);
        $images = $domXpath->query($this->xpath);
        if ($images->length == 0) return false;
        foreach ($images as $img) {
            $a = $img->parentNode;
            $img->setAttribute('src', $a->getAttribute('href'));
            $img->setAttribute('class', $this->class);//@TODO: need add class zoom
            $a->parentNode->replaceChild($img, $a);
        }
        $body = $dom->getElementsByTagName('body')->item(0);
        $newContent = $dom->saveXML($body);
        $newContent = str_replace('<body>', '', $newContent);
        $newContent = str_replace('</body>', '', $newContent);
        return $newContent;
    }

    /**
     *
     * @param string $content
     * @param string $suffix For generation unique thumbnail
     * @return boolean
     */
    public function convert($content, $suffix = null)
    {
        $dom = new DOMDocument(null, 'utf-8');
        $dom->loadHTML($this->htmlMeta . $content);
        $images = $dom->getElementsByTagName('img');
        if ($images->length == 0) return false;
        foreach ($images as $imgNumber => $img) {
            //test for containing class 'zoom'
            //$class = $img->getAttribute('class');
            //if (!preg_match("/\s?$this->class\s?/", $class)) continue;
            if ($img->parentNode->nodeName == 'a') continue;  //don't do if it has a link already

            $src = $img->getAttribute('src');
            $style = $img->getAttribute('style');
            preg_match('/width\s*:\s*(?P<width>\d+)px/', $style, $matches);//width: 200px; height: 289px;
            if (empty($matches)) continue;
            $width  = $matches['width'];
            preg_match('/height\s*:\s*(?P<height>\d+)px/', $style, $matches);
            if (empty($matches)) continue;
            $height = $matches['height'];
            //if not set any parameter don't touch
            if (!$width && !$height) continue;

            $newSrc = $this->createThumbnail($src, $width, $height, $imgNumber, $suffix);
            if (false === $newSrc) continue; //some error or no need thumbnail
            $imgNew = clone $img;
            $imgNew->setAttribute('src', $newSrc);
            //$imgNew->setAttribute('class', str_replace($this->class, '', $class));
            $a = $dom->createElement('a');
            $a->appendChild($imgNew);
            $a->setAttribute('href', $src);
            $a->setAttribute('class', $this->class);
            $a->setAttribute('data-fancybox-group', 'gallery');
            $a->setAttribute('title', $img->getAttribute('title'));
            $img->parentNode->replaceChild($a, $img);
        }
        $body = $dom->getElementsByTagName('body')->item(0);
        $newContent = $dom->saveXML($body);
        $newContent = str_replace('<body>', '', $newContent);
        $newContent = str_replace('</body>', '', $newContent);
        return $newContent;
    }

}
