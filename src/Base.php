<?php

namespace iry\image;

abstract class Base
{
    private $_error = false;
    private $_errorMsg = '';

    static function getSize($img){
        $imageInfo = getimagesize($img);
        return [$imageInfo[0],$imageInfo[1]];
    }

    /**
     * @param $img
     * @return array|bool
     */

    static public function getImageInfo($img){
        $imageInfo = getimagesize($img);
        if ($imageInfo !== false) {
            $imageType = strtolower(substr(image_type_to_extension($imageInfo[2]), 1));
            $imageSize = filesize($img);
            return array(
                "width" => $imageInfo[0],
                "height" => $imageInfo[1],
                "type" => $imageType,
                "suf"=>'.'.($imageType==='jpeg'?'jpg':$imageType),
                "size" => $imageSize,
                "mime" => $imageInfo['mime']
            );
        } else {
            return false;
        }
    }

    protected function _setError($msg){
        $this->_error = true;
        $this->_errorMsg = $msg;
    }

    protected function _hasError(){
        return $this->_error;
    }
    public function getError(){
        return $this->_errorMsg;
    }

    /**
     * 缩放
     * @param int $width 宽
     * @param int $height 高
     * @return $this
     */

    abstract public function resize($width, $height);

    /**
     * 裁剪图片
     * ---------
     * 坐标
     * @param int $x x 坐标 支持"数字"和"*%"
     * @param int $y y 坐标 支持"数字"和"*%"
     * ----------
     * 尺寸
     * @param int $w WIDTH/裁剪后的宽 支持"数字"和"*%"
     * @param int $h HEIGHT/裁剪后的高 支持"数字"和"*%"
     *
     * @return $this
     */

    abstract public function crop ($x ,$y,$w ,$h);

    /**
     * 顺时针旋转图片
     * @param int $angle 旋转角度
     * @param int $bgColor 旋转后空余背景
     */
    abstract public function rotate ($angle,$bgColor='#ffffff');

    /**
     * 逆时针旋转图片
     * @param $angle
     * @param string $bgColor
     * @return mixed
     */
    public function contrarotate ($angle,$bgColor='#ffffff'){
        return $this->rotate($angle*-1,$bgColor);
    }

    /**
     * 写入文字
     * @param string $text 写入文字
     * @param $textSize
     * @param $position
     * @param $fontFile
     * @param string $color
     * @return string 保存后图片路径
     */

    abstract public function addText ($text ,$textSize ,$position,$fontFile,$color='#000000');

    /**
     * @param $waterFilePath
     * @param $position
     * @param string $w
     * @param string $h
     * @return mixed
     */

    abstract public function watermark ($waterFilePath ,$position,$w='100%',$h='100%');

    abstract public function stretch($width,$height,$eqRatio=false);


    /**
     * 保存
     * @param string $dest
     * @param bool $rmSrc
     * @return string filename
     */

    abstract public function save($dest,$rmSrc = false);

}