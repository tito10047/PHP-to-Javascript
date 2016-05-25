<?php
error_reporting(E_ALL);
ini_set('xdebug.max_nesting_level', 3000);
function checkNodeJs(){
    exec("nodejs -v",$output);
    return count($output)==1;
}
if (!checkNodeJs()){
    throw new RuntimeException("nodejs not found in system path");
}

//exec("nodejs '/home/jofo/projects/PHP-Parser/test/code/JsPrinter/jsSrc/runTest.js'",$output);
//var_dump($output);
//
//exit;
require_once "../vendor/autoload.php";


$code = file_get_contents(__DIR__."/simple.test.php");

$parser = (new \PhpParser\ParserFactory())->create(\PhpParser\ParserFactory::PREFER_PHP7);
$prettyPrinter = new \phptojs\JsPrinter\NonPrivate();

try {
    // parse
    $stmts = $parser->parse($code);
    //var_dump($stmts[0]);exit;
    // pretty print
    $code = $prettyPrinter->jsPrint($stmts);
    echo $code;
} catch (PhpParser\Error $e) {
    echo 'Parse Error: ', $e->getMessage();
}