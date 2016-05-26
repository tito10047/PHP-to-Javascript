<?php
/**
 * Created by PhpStorm.
 * User: mostkaj
 * Date: 26.5.2016
 * Time: 20:40
 */
if (!isset($_COOKIE["converter"])){
    return;
}
if (!@$_GET["template"]){
    return;
}
$template=$_GET["template"];
$files=glob(__DIR__."/../test/code/jsPrinter/phpSrc/*/{$template}.js.php");
if (count($files)==0){
    echo "1";
}
$file=$files[0];
echo file_get_contents($file);