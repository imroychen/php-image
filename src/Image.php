<?php

namespace iry\image;

class Image
{
    static private $_libName = 'Gd';

    static function setLib($lib=''){
        if(empty($lib)){//自动计算使用什么库
            $lib = function_exists('imagecreate')?'Gd':'Magick';
        }else {
            $lib = ucfirst(strtolower($lib));
        }
        self::$_libName = $lib;
    }

    static function getSize($file){
        return Base::getSize($file);
    }

    static function getImageInfo($file){
        return Base::getImageInfo($file);
    }

    /**
     * @param string $file
     * @return Base|Gd|ImageMagick
     */
    static function src($file){
        $className = __NAMESPACE__.'\\'.self::$_libName;
        return new $className($file);
    }


    static function thum($from,$to,$width,$height){
        self::src($from)->resize($width,$height)->save($to);
    }

    static function imgWatermark($from,$to,$watermarkImg,$position){
        self::src($from)->watermark($watermarkImg,$position)->save($to);
    }

    static function textWatermark($from,$to,$text,$position,$color='#ffffff',$font=''){
        self::src($from)->addText($text,$position,$color,$font)->save($to);
    }
}