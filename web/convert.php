<?php
/**
 * Created by PhpStorm.
 * User: mostkaj
 * Date: 26.5.2016
 * Time: 20:48
 */
if (!isset($_COOKIE["converter"])) {
    return;
}
if (!@$_POST["code"]){
    return;
}
$code=urldecode($_POST["code"]);
$code=str_replace(["__AND__","__PLUS__"],["&","+"],$code);
require_once __DIR__."/../vendor/autoload.php";

try{

    $parser        = (new \PhpParser\ParserFactory())->create(\PhpParser\ParserFactory::PREFER_PHP7);
    $prettyPrinter = new \phptojs\JsPrinter\NonPrivate();

    $stmts = $parser->parse($code);
    ob_start();
    $jsCode = $prettyPrinter->jsPrint($stmts);
    $errors = ob_get_clean();
    $errors = explode(PHP_EOL,$errors);
    foreach ($errors as $error){
        echo "//".$error.PHP_EOL;
    }
    echo $jsCode;
    
}catch (PhpParser\Error $e) {
    echo 'ERROR:', $e->getMessage();
}catch (Exception $e){
    echo "ERROR:Some is wrong";
}