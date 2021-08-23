# PHP image processing and image manipulation
## 示例
```php
use iry\image\Image;
$waterMark = __DIR__ . '/img/watermark.png';

$img = Image::src(__DIR__ . '/img/test-img.jpg'); //也可以直接 new Gd('file'); 或者 new Magick('file');
//重置图片尺寸
$img->resize(800,800);
//添加水印 lt左上，rt:右上 ,l:左中，t:上中....
$img->watermark($waterMark,'c')->watermark($waterMark,'lt',60,60)->watermark($waterMark,'rb',120,120);
//添加文本
//$img->addText('text2',14);
//图片旋转90度
$img->rotate(90);
//拉伸
$img->stretch(500,500,true);
//保存结果
$r = $img->save('{file_dir}/test.dist.jpg');
//添加文字
$img->addText('left & top 左上',20,'lt','....' )
    ->addText('right & bottom 右下',20,'rb',$font );

//支持连贯写法
//$img->resize(800,800)->rotate(90)->watermark('....')->....->save('保存');
```
---

## 方法说明

### getSize 获取图片尺寸 
    静态方法
    @param: 文件路径
    @return: [宽,高]
    
### getImageInfo 获取图片信息
```php
    // 静态方法
    Image::getImageInfo($file);
    //@return 
    [
        "width" =>'宽',
        "height" =>'高',
        "type" =>'文件类型',
        "suf"=>'文件后缀',
        "size" =>'文件大小',
        "mime" =>'mime'
    ]
```

### 缩放 resize
```php
/**
 * resize
 * @param int $width 宽
 * @param int $height 高
 * @return $this
 */
Image::src('file')->resize(400,500);
```
### 裁剪图片 crop
```php
/**
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
Image::src('file')->crop(0,0,400,500);
```
### 旋转图片 rotate
```
/**
 * 旋转图片
 * @param int $angle 旋转角度
 * @param int $bgColor 旋转后空余背景 可选
 */
 Image::src('file')->rotate(30);
 Image::src('file')->rotate(30,'#ffffff')->rotate(60);
```
### 添加文字(水印) addText 
```php
/**
 * @param string $text 写入文字
 * @param $textSize GD1为像素 GD2 单位为磅（pound）
 * @param string|[] $position 写入文字起点坐标或者位置
 * 位置: lt/rt/lb/rb/t/b/l/r/c (lt leftTop 左上) (t : 上中)  坐标:[x,y]
 * @param $fontFile 字体文件
 * @param string $color
 * @return $this
 */
Image::src('file.jpg')->addText($text,12,'lt',$fontFile)
```
**$fontFile** 字体文件：使用绝对路径<br>
需要自己去<u>**下载**</u>、也可以从的 Windows、Mac、Linux字体安装目录<u>**拷贝**</u>字体到你的项目中
windows(C:\Windows\Fonts)、Mac(/System/Library/Fonts)
<br>**注**：部分字体可能需要商用授权（请自行联系字体发布方）
<br>常见字体下载:
<br>[Han Serif 下载地址](https://github.com/adobe-fonts/source-han-serif/tree/release/OTF)
<br>[Han Serif 简体中文](https://github.com/adobe-fonts/source-han-sans/tree/release/OTF/SimplifiedChinese)

### 图片水印 watermark

### 保存图片：save
```php
/**
 * 保存
 * @param string $dest 目标路径
 * @param bool $rmSrc 是否移除原图 可选 默认：false
 * @return string|false filename|false
 */
 Image::src('file')->resize(400,500)->save('test.png',true);
 //save 方法第一个参数 可以使用变量
 Image::src('file')->resize(400,500)->save('{file_dir}/test_result.{file_ext}');
```
save方法第一个参数(目标文件)可以使用以下变量
1. {file_dir}: 原文件所在的目录,
2. {file_ext}: 原文件扩展名称,
3. {file_name}: 原文件名称(不包含后缀),
4. {file_full_name}: 原文件名称



## 快捷方

