<?php
require_once ('no-composer-require.php'); //普通加载方式
//require_once('vendor/autoload.php');//composer 加载方式

use iry\image\Image;
//use \iry\image\Gd;
use \iry\image\ImageMagick;

//-------
//可选 默认GD
//Image::setLib();//自动设置库，如果你的服务器支持GD则使用GD 否则使用Magick
//Image::setLib('Gd'); //手动设置Gd库 需要PHP GD扩展支持
//Image::setLib('ImageMagick');//手动设置库 需要在你的服务器上安装ImageMagick软件 （无需PHP图库扩展）

$waterMark = __DIR__ . '/img/watermark.png';
//----------------
$img = Image::src(__DIR__ . '/img/test-img.jpg'); //也可以直接 new Gd('file'); 或者 new ImageMagick('file');
//$img = new ImageMagick(__DIR__ . '/img/test-img.jpg');
//重置图片尺寸
$img->resize(800,800);

//添加水印 lt左上，rt:右上 ,l:左中，t:上中....
$img->watermark($waterMark,'c')->watermark($waterMark,'lt',60,60)->watermark($waterMark,'t',60,60)->watermark($waterMark,'rb',120,120);

//添加文本
$font = __DIR__ . '/img/SourceHanSerifSC-Regular.otf';
$img->addText('left & top 左上',20,'lt',$font )
    ->addText('right & bottom 右下',20,'rb',$font )
    ->addText('Center 中',35,'c',$font );

//->corp(...)/*更多处理图片的方法....->*/;

//图片旋转30度
$img->rotate(30);

//拉伸
//$img->stretch(500,500,true);

//保存结果
$r = $img->save('{file_dir}/result.jpg');


//失败获取错误原因
if(!$r) {
    $error = $img->getError();
    var_export($error);
}else{
    echo "ok";
}

