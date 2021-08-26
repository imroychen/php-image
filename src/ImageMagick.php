<?php
/**
 * User: roy
 * Date: 2018/9/5
 * Time: 13:52
 */

namespace iry\image;

/**

 * Class ImageMagick
 *  * 该库无需 Php ImageMagick 模块
 * 只需要主机上安装 ImageMagick软件即可
 *
 * @package iry\image
 * $img = new ImageMagick(图片路径)
 * $img->resize(宽,高,[质量 默认70%])->save(新的图片地址,[是否删除原图，默认否])
 * $img->resize(100,160,70)->crop($x,$y,$w,$h)->......->save('newimg.png')
 */

class ImageMagick extends Base
{
	private $_src,$_tmpSrc;

	private $_tmpFilePre;

	private function _getPos($position){
        $positionList = [
            'lb'=>'southwest',
            'lt'=>'northwest',
            'rb'=>'southeast',
            'rt'=>'northeast',
            'l'=>'west',
            'r'=>'east',
            't'=>'north',
            'b'=>'east',
            'c'=>'Center'
        ];
        return (is_string($position)&&isset($positionList[$position]))?$positionList[$position]:false;
    }
	private function _createTmpFile($append){
	    return $this->_tmpFilePre.'-'.mt_rand(10,99).mt_rand(10,99).$append;
    }
    private function _checkImg($filePath){
	    if(!file_exists($filePath)){
	        $this->_setError('img-n-exist/图片不存在'.basename($filePath));
	        return false;
        }
//	    else{
//	        //...is image
//        }
	    return true;
    }
    private function _setTmpRes($from,$to){
	    $this->_tmpSrc = $to;
	    if($from!=$to){
	        unlink($from);
        }
    }
	public function __construct($src)
	{
	    $this->_tmpFilePre = sys_get_temp_dir().'/'.'i_t_'.uniqid().'-'.mt_rand(10,99);
        $basename = basename($src);

		$this->_src = $src;

        $this->_tmpSrc = $this->_createTmpFile($basename);
		copy($src,$this->_tmpSrc);
	}

    /**
     * 缩放
     * @param int $width
     * @param int $height
     * @return $this
     */

	public function resize($width,$height){
		$src = $this->_tmpSrc;
		$dst = $src;
		if(file_exists($src)) {
			//$quality = intval($quality);

            $width = intval($width);
            $height = intval($height);
			$size = $width . 'x' . $height;
            /*-quality $quality*/
			$cmd = "convert -resize $size \"$src\" \"$dst\"";;
			exec($cmd);
            $this->_setTmpRes($src,$dst);
		}
		return $this;
	}

	public function stretch($width=300,$height=400,$eqRatio=false){
	    $this->resize($width,$height);//
        return $this;
    }

    /**
     * 裁剪
     * @param int $x x 支持"数字"和"*%"
     * @param int $y y 支持"数字"和"*%"
     * @param int $w WIDTH 支持"数字"和"*%"
     * @param int $h HEIGHT 支持"数字"和"*%"
     * @return $this
     *
     * @inheritDoc
     */
	public function crop($x,$y,$w,$h){
		$fromFile = $this->_tmpSrc;
		$toFile = $fromFile;

		if(strstr((string)$x,'%') || strstr((string)$y,'%')){
			$size = self::getSize($fromFile);
			if($size && count($size)>1){
				if(strstr((string)$x,'%')){
					$x = (str_replace('%','',$x)*1)/100;
					$x = round($x*$size[0],0);
				}

				if(strstr((string)$y,'%')){
					$y = (str_replace('%','',$y)*1)/100;
					$y = round($y*$size[1],0);
				}

				if(strstr((string)$h,'%')){
					$h = (str_replace('%','',$h)*1)/100;
					$h = round($h*$size[1],0);
				}

				if(strstr((string)$w,'%')){
					$w = (str_replace('%','',$w)*1)/100;
					$w = round($w*$size[0],0);
				}
			}else{
				return $this;
			}
		}


		$x=intval($x);
		$y=intval($y);
		$w=intval($w);
		$h=intval($h);
		$size = $w.'x'.$h.'+'.$x.'+'.$y;//{x}x{y}+{w}+{h}
		exec("convert $fromFile -crop $size $toFile");
		$this->_setTmpRes($fromFile,$toFile);
		//echo "// convert $fromFile -crop $size $toFile\n";
		return $this;
	}

    /**
     * @param string $watermarkFile
     * @param string $position lb/lt/rb/rt/l/r/t/b/c
     * l：left, r：right , t：top ,b:bottom ， c:Center
     * @param int|string $w
     * @param int|string $h
     * @return ImageMagick
     *
     * @inheritDoc
     */

	public function watermark($watermarkFile,$position,$w='100%',$h='100%'){
        $position = empty($position)?'rb':$position;
        if(!$this->_checkImg($watermarkFile)){return $this;}
        $src = $this->_tmpSrc;
        $dist = $src;

        $_WmFile = $watermarkFile;
        if($w!='100%'||$h!='100%') {
            $_WmFile = $this->_createTmpFile(basename($watermarkFile));
            $cmd = 'convert -resize {{:size}} -quality 95 "{{:src}}" "{{:dist}}"';
            $this->_exec($cmd, ['size' => $w.'x'.$h, 'src' => $watermarkFile, 'dist' => $_WmFile]);
        }

        $args = [
            //'-gravity {{:pos}}',
            //'-geometry +10+50',//坐标 or 边距
            '-compose dissolve',//溶解效果
            '-define compose:args=80'//透明度 和溶解配合使用
            //'-composite -quality 95',//质量
            //........
        ];
        $_pos = $this->_getPos($position);
        if($_pos) {
            $args[] = '-gravity ' . $_pos;
            if (strpos('/[lt][rt][lb][rb]', "[$position]") > 0) $args[] = '-geometry +10+10';//margin
            if (strpos('/[l][r]', "[$position]") > 0) $args[] = '-geometry +10+0';//margin
            if (strpos('/[b][t]', "[$position]") > 0) $args[] = '-geometry +0+10';//margin
        }else{
            $args[] = '-geometry +'.$position[0].'+'.$position[1];//margin
        }

        $cmd = 'convert {{:src}} {{:WMFile}} '.implode(' ',$args).' -composite {{:dist}}';
        //$cmd = 'composite -gravity {{:pos}} -compose plus {{:WMFile}} {{:src}} {{:dist}}';

        $this->_exec($cmd,[
            //'pos'=>$positionList[$position],
            'src'=>$src,
            'dist'=>$dist,
            'WMFile'=>$_WmFile
        ]);
        if($_WmFile!=$watermarkFile){unlink($_WmFile);}
        $this->_setTmpRes($src,$dist);
        return $this;
    }

    /**
     * @param string $text
     * @param $textSize
     * @param int $color
     * @param $position
     * @return string|void
     *
     * @inheritDoc
     */
    public function addText($text,$textSize,$position,$font='',$color='#ffffff'){

        $_t = $this->_getPos($position);
        if($_t) {
            $pos = '-gravity '.$_t;
            $margin = '-annotate +10+10';
            if (strpos('/[lt][rt][lb][rb]', "[$position]") > 0) $margin = '-annotate +10+10';//margin
            if (strpos('/[l][r]', "[$position]") > 0) $margin = '-annotate +10+0';//margin
            if (strpos('/[b][t]', "[$position]") > 0) $margin = '-annotate +0+10';//margin
        }else{
            $pos = '';
            $margin = ' -annotate +'.$position[0].'+'.$position[1];
        }

        if(!empty($font)){
            if(!file_exists($font)){
                $this->_setError('error/addText:Font file does not exist');
                $font = '';
            }
            $font = '-font ' . var_export($font, true);
        }else{
            $font = '';
        }

        $cmd = 'mogrify {{:font}} -pointsize {{:tSize}} -fill black -weight bolder {{:pos}} {{:margin}} {{:text}} {{:src}}';

        $this->_exec($cmd,[
            'font'=>$font,'tSize'=>$textSize,
            'pos'=>$pos,'text'=>var_export($text,true),
            'margin'=>$margin,'src'=>$this->_tmpSrc
        ]);
        return $this;
    }

    /**
     * @param int $angle
     * @param string $bgColor
     * @return $this
     */
    public function rotate($angle, $bgColor = '#ffffff')
    {
        $cmd = 'convert -rotate {{:angle}} {{:src}} {{:dist}}';
        $this->_exec($cmd,['angle'=>$angle,'src'=>$this->_tmpSrc,'dist'=>$this->_tmpSrc]);
        //$this->_setTmpRes($this->_tmpSrc,$this->_tmpSrc);
        return $this;
    }

	/**
     * 保存
	 * @param string $dest
	 * @param bool $rmSrc
	 * @return string filename
	 */

	public function save($dest,$rmSrc = false){

		//var_export($this->_tmpSrc);
		//var_export($dest);
		$srcFileName = basename($this->_src);
		$varList = [
			'{file_dir}'=>dirname($this->_src),
			'{file_ext}'=>ltrim(strchr($srcFileName,'.'),'.'),
			'{file_name}'=>substr($srcFileName,0,strrpos($srcFileName,'.')),
			'{file_full_name}'=>$srcFileName
		];
		$dest = str_replace(array_keys($varList),array_values($varList),$dest);
		if($this->_tmpSrc===$this->_src){
			copy($this->_tmpSrc,$dest);
		}else {
			rename($this->_tmpSrc, $dest);
		}
		if($rmSrc && file_exists($this->_src)){
			@unlink($this->_src);
		}

		return $dest;

	}

	public function __destruct()
	{
	    $fileList = glob($this->_tmpFilePre.'*');
	    if(count($fileList)>0){
	        foreach ($fileList as $v){
                if(file_exists($v)){
                    @unlink($v);
                }
            }
        }
	}

	private function _exec($cmd,$args=[])
    {
	    if(!empty($args)){
	        foreach ($args as $k=>$v){
                $cmd = str_replace('{{:'.$k.'}}',$v,$cmd);
            }
        }
        exec($cmd);
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
				if (preg_match("/\s+/", $v)) {
					$srcFileList[$k] = var_export($v);
				}
			}

			$cmd = '';
			if($style==='y') {
				$cmd = "convert -append " . implode(' ', $srcFileList) . ' ' . $distImg;
			}elseif($style==='x'){
				$cmd = "convert -append " . implode(' ', $srcFileList) . ' ' . $distImg;
			}
			if($cmd) {
				exec($cmd);
				return $distImg;
			}
		}
		return false;
	}

    static function getSize($img){
        exec('identify  -format "%[fx:w]x%[fx:h]"  '.var_export($img,true),$r);
        if(!empty($r) && isset($r[0])){
            return explode('x',$r[0]);
        }
        return false;
    }

}