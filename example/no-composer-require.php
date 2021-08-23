<?php
//设置iry/image 命名空间的 加载方式
spl_autoload_register(function ($class) {
    $classPath = str_replace('\\','/',rtrim($class,'\\'));
    if(strpos($classPath,'iry/image')===0 && !class_exists($class,false)){
        include str_replace('^iry/image/',dirname(__DIR__).'/src/', '^'.$classPath).'.php';
    }
});
