<?php
/**
 * User: roy
 * Date: 2018/9/5
 * Time: 13:52
 */

namespace iry\image;

/**

 * Class Gd
 *  * 该库无需 Php GD 模块 支持
 *
 * @package iry\image
 * $img = new ImageMagick(图片路径)
 * $img->resize(宽,高,[质量 默认70%])->save(新的图片地址,[是否删除原图，默认否])
 * $img->resize(100,160,70)->crop($x,$y,$w,$h)->......->save('newimg.png')
 */

class Gd extends Base
{

    private $_src;

    private $_resultImg=[];

    /**
     * @var null
     */

    public function __construct($file)
    {
        $this->_src =$file;
        if($file && file_exists($file)){
            $info = getimagesize($file);
            $imageType = strtolower(substr(image_type_to_extension($info[2]), 1));
            switch($info[2])//取得格式
            {
                case 1:$img = imagecreatefromgif($file);break;
                case 2:$img = imagecreatefromjpeg($file);break;
                case 3:$img = imagecreatefrompng($file);break;
                default:$img = false;
            }
            $this->_setImg($img,$info[0],$info[1],$imageType);
        }

        return $this;
    }

    private function _getRGB($color) {
        $defalut = ['r' => 0, 'g' => 0, 'b' => 0];
        $color = strtolower(trim($color));
        if ( $color[0] == '#' ) {
            $color = substr( $color, 1,6 );
            if ( strlen( $color ) == 3 ) {
                list( $r, $g, $b ) = array( $color[0] . $color[0], $color[1] . $color[1], $color[2] . $color[2] );
            } else {
                $color = $color.'000000';
                list( $r, $g, $b ) = array( $color[0] . $color[1], $color[2] . $color[3], $color[4] . $color[5] );
            }

            $r = ['r'=>hexdec( $r ),'g'=>hexdec( $g ),'b'=>hexdec( $b )];
        }elseif (strpos($color,'rgb')===0){
            $_res = [];
            $color = preg_replace('/\s+/','',$color);
            preg_match('/rgba?\((\d+),(\d+),(\d+)[,\)]/',$color,$_res);
            if($_res){
                $r = ['r'=>intval($_res[1]),'g'=>intval($_res[2]),'b'=>intval($_res[3])];
            }else{
                $r = $defalut;
            }
        }else{
            $r = $defalut;
        }
        return $r;
    }

    private function _setImg($img,$w,$h,$type){
        $type = $type==='jpeg'?'jpg':$type;
        $this->_resultImg=[
            'img'=>$img,
            'w'=>$w,
            'h'=>$h,
            'type'=>$type
        ];
    }

    private function _getImg($field='*'){
        if($field==='*'){
            return $this->_resultImg;
        }else {
            return $this->_resultImg[$field];
        }
    }

    private function _processPercentage($value,$REV){
        //百分数转换
        if(is_string($value) && strstr($value,'%')){
            $v = (str_replace('%','',$value)*1)/100;
            return round($v*$REV,0);
        }
        return $value;
    }

    private function _createImage($width,$height,$type='jpg'){
        if ($type != 'gif' && function_exists('imagecreatetruecolor')) {
            $distImg = imagecreatetruecolor($width, $height);
        }else {
            $distImg = imagecreate($width, $height);
        }

        if ( $type == 'gif' || $type == 'png' ) {
            $srcImg = $this->_getImg('img');
            $transIndex = imagecolortransparent($srcImg);
            if ($transIndex >= 0) {
                $transColor  = imagecolorsforindex($srcImg, $transIndex);
                $transIndex  = imagecolorallocate($distImg, $transColor['red'], $transColor['green'], $transColor['blue']);
                imagefill($distImg, 0, 0, $transIndex);
                imagecolortransparent($distImg, $transIndex);
            }
            // Always make a transparent background color for PNGs that don't have one allocated already
            elseif ($type == 'png') {
                imagealphablending($distImg, false);
                $color = imagecolorallocatealpha($distImg, 0, 0, 0, 127);
                imagefill($distImg, 0, 0, $color);
                imagesavealpha($distImg, true);
            }
        }

        return $distImg;
    }

    /**
     * @param $img
     * @return self 新的实例
     */
    private function _new($img){
        $cls = __CLASS__;
        return new $cls($img);
    }

    /**
     * @param $position
     * @param $w
     * @param $h
     * @param $ctnW
     * @param $ctnH
     * @param int|int[] $move //自动偏移
     * @return array|mixed
     */

    private function _positionToXy($position,$w,$h,$ctnW,$ctnH,$move=[15,10]){
        $move = (is_integer($move)||is_numeric($move))?[$move,$move]:$move;
        if(is_array($position)){
            return $position;
        }

        $positionList = [
            'lt'=>[0,0],//顶端居左
            'rt'=>[$ctnW - $w,0],
            'lb'=>[0, $ctnH - $h],
            'rb'=>[$ctnW - $w, $ctnH - $h],

            'l'=>[0, ($ctnH - $h) / 2],
            'r'=>[$ctnW - $w, ($ctnH - $h) / 2],

            't'=>[$ctnW - $w,0],

            'b'=>[($ctnW - $w) / 2, $ctnH - $h],
            'c'=>[($ctnW - $w) / 2, ($ctnH - $h) / 2]
        ];
        //$posX = rand(0,($ground_w - $w));
        //$posY = rand(0,($ground_h - $h));

        $posXy = $positionList[$position];

        if(strstr($position,'r')){
            $posXy[0] -= $move[0];
        }elseif(strstr($position,'l')){
            $posXy[0] += $move[0];
        }

        if(strstr($position,'b')){
            $posXy[1] -= $move[1];
        }elseif(strstr($position,'t')){
            $posXy[1] += $move[1];
        }
        return $posXy;

    }

    /*
    private function _getImageInfo() {
        if(is_null($this->_fileInfo)) {
            $img = $this->_file;
            $this->_fileInfo = self::getImageInfo($img);
        }
        return $this->_fileInfo;
    }*/

    /**
     * 缩放
     * @param int $width 宽
     * @param int $height 高
     * @return $this
     */

    public function resize($width, $height){
        if($this->_hasError()) return $this;
        elseif($width==='100%' && $height==='100%') return $this;

        $src = $this->_getImg();
        $srcW = $src['w'];
        $srcH = $src['h'];
        $width = $width==='100%'?$srcW:$width;
        $height = $height==='100%'?$srcH:$height;

        $rate = min($width/$srcW,$height/$srcH);
        if($rate!==1) {
            //var_export([$width/$srcW,$height/$srcH]);
            $width = $srcW * $rate;
            $height = $srcH * $rate;

            $distImg = $this->_createImage($width, $height, $src['type']);
            imagecopyresampled($distImg, $src['img'], 0, 0, 0, 0, $width, $height, $srcW, $srcH);
            imagedestroy($src['img']);
            $this->_setImg($distImg, $width, $height, $src['type']);
        }
        return $this;
    }

    function stretch($width, $height,$eqRatio=false){
        if($this->_hasError()) return $this;


        $src = $this->_getImg();
        $srcW = $src['w'];
        $srcH = $src['h'];

        $width = $width==='100%'?$srcW:$width;
        $height = $height==='100%'?$srcH:$height;

        /*
        if($eqRatio) {
            $rate = max($width / $srcW, $height / $srcH);
            $width = $srcW * $rate;
            $height = $srcH * $rate;
        }
        */

        if($width!==$srcW || $height!==$srcH) {
            $distImg = $this->_createImage($width, $height, $src['type']);
            //imagecopyresampled($distImg, $src['img'], 0, 0, max(0,$srcW-$width)/2, max(0,$srcH-$height)/2, $width, $height, $srcW, $srcH);
            imagecopyresampled($distImg, $src['img'], 0, 0, 0, 0, $width, $height, $srcW, $srcH);
            imagedestroy($src['img']);
            $this->_setImg($distImg, $width, $height, $src['type']);
        }
        return $this;
    }


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

    public function crop ($x ,$y,$w ,$h) {
        if($this->_hasError()) return $this;

        $src = $this->_getImg();
        $srcW = $src['w'];
        $srcH = $src['h'];

        $x = $this->_processPercentage($x,$srcW);
        $w = $this->_processPercentage($w,$srcW);
        $y = $this->_processPercentage($y,$srcH);
        $h = $this->_processPercentage($h,$srcH);

        $distImg = $this->_createImage($w, $h,$src['type']);

        if( $w > $srcW || $h > $srcH ) {//缩放图片
            imagecopyresampled($distImg, $src['img'], 0, 0, $x, $y, $w, $h, $srcW, $srcH);
        } else {//裁剪
            imagecopy($distImg, $src['img'], 0, 0, $x, $y, $w, $h);
        }
        imagedestroy($src['img']);
        $this->_setImg($distImg,$w,$h,$src['type']);

        return $this;
    }

    /**
     * 旋转图片
     * @param int $angle 旋转角度
     * @param int $bgColor 旋转后空余背景
     */
    public function rotate ($angle,$bgColor='#ffffff') {
        if($this->_hasError()) return $this;
        $img = $this->_getImg();
        $bgColor = $this->_getRGB($bgColor);

        $color = imagecolorallocatealpha($img['img'],$bgColor['r'],$bgColor['g'],$bgColor['b'],127);
        if ($rotate_img = imagerotate($img['img'], $angle, $color)) {
            imagealphablending($rotate_img ,false);
            imagesavealpha($rotate_img ,true);
            imagedestroy($img['img']);
            $this->_setImg($rotate_img,imagesx($rotate_img),imagesy($rotate_img),$img['type']);
        }else{
            $this->_setError('rotate:error');
        }
        return  $this;
    }

    /**
     * 写入文字
     * @param string $text 写入文字
     * @param $textSize
     * @param string|array $position
     * @param $fontfile
     * @param string $color
     * @return $this
     *
     * @inheritDoc
     */

    public function addText ($text ,$textSize ,$position,$fontfile,$color='#000000') {
        if($this->_hasError())return $this;

        $src = $this->_getImg();
        $srcImg = $src['img'];
        $angle = 0;

        $color = $this->_getRGB($color);
        //$w = $h = 0;
        //if($position!='l'){
            $rect = imagettfbbox($textSize,$angle,$fontfile,$text);
            $minX = min(array($rect[0],$rect[2],$rect[4],$rect[6]));
            $maxX = max(array($rect[0],$rect[2],$rect[4],$rect[6]));
            $minY = min(array($rect[1],$rect[3],$rect[5],$rect[7]));
            $maxY = max(array($rect[1],$rect[3],$rect[5],$rect[7]));
            $w = $maxX-$minX;
            $h = $maxY-$minY;
        //}

        list($x,$y) = $this->_positionToXy($position,$w,$h,$src['w'],$src['h']);
        $y += $h;//

        $colorIdx = imagecolorallocate($srcImg, $color['r'], $color['g'], $color['b']);
        imagettftext($srcImg, $textSize, $angle, $x, $y, $colorIdx, $fontfile, $text);

        return $this;
    }

    /**
     * @param $waterFilePath
     * @param $position
     * @param string $w
     * @param string $h
     * @return $this
     *
     * @inheritDoc
     */

    public function watermark ($waterFilePath ,$position, $w='100%',$h='100%') {
        if($this->_hasError()){
            return $this;
        }
        //读取水印文件
        if(empty($waterFilePath) || !file_exists($waterFilePath)) {
            $this->_setError('watermark file/error');
            return $this;
        }

        $waterImg = $this->_new($waterFilePath)->resize($w, $h)->getImg();
        $wmW = $waterImg['w'];
        $wmH = $waterImg['h'];


        if($waterImg['img']) {
            $distImg = $this->_getImg();

            $distW = $distImg['w'];
            $distH = $distImg['h'];

            list($posX,$posY) = $this->_positionToXy($position,$wmW,$wmH,$distW,$distH);
            imagecopy($distImg['img'], $waterImg['img'], $posX, $posY, 0, 0, $wmW, $wmH);
            //imagecopymerge($distImg['img'], $waterImg['img'],  $posX, $posY, 0, 0, $wmW, $wmH,50);
        }else{
            $this->_setError('watermark type/error');
        }
        imagedestroy($waterImg['img']);
        return $this;
    }


    public function getImg(){
        return $this->_getImg();
    }


    /**
     * 保存
     * @param string $dest
     * @param bool $rmSrc
     * @return string filename
     */

    public function save($dest,$rmSrc = false){
        if(!$this->_hasError()) {

            $srcFileName = basename($this->_src);
            $varList = [
                '{file_dir}' => dirname($this->_src),
                '{file_ext}' => ltrim(strchr($srcFileName, '.'), '.'),
                '{file_name}' => substr($srcFileName, 0, strrpos($srcFileName, '.')),
                '{file_full_name}' => $srcFileName
            ];
            $dest = str_replace(array_keys($varList), array_values($varList), $dest);

            $dstType = strtolower(ltrim(strrchr($dest, '.'), '.'));
            $imageFun = 'image' . ($dstType === 'jpg' ? 'jpeg' : $dstType);
            $distImg = $this->_getImg('img');
            $imageFun($distImg, $dest);

            imagedestroy($distImg);
            $this->_setImg(null,0,0,null);

            if ($rmSrc && file_exists($this->_src)) {
                @unlink($this->_src);
            }
            return $dest;
        }
        return false;
    }

    public function __destruct()
    {
        /*
        $_tmp = $this->_getImg('img');
        if($_tmp){
            imagedestroy($_tmp);
        }
        */
    }


    /**
     * 拼图
     * @param $srcFileList
     * @param $distImg
     * @param string $style
     * @return bool
     */
    static function puzzle($srcFileList,$distImg,$style='y'){

        if(!empty($srcFileList)) {
            foreach ($srcFileList as $k => $v) {
                $v = trim($v);
                //imagecopy($dst_img, $src_img, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h)
            }
        }
        return false;
    }

}