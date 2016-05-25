<?php
/**
 * Created by PhpStorm.
 * User: Jozef Môstka
 * Date: 24.5.2016
 * Time: 19:13
 */

namespace PhpToJs\JsPrinter;


use PhpParser\PrettyPrinterAbstract;
use PhpToJs\Printer\SourceWriter;

abstract class JsPrinterAbstract extends PrettyPrinterAbstract{

    /** @var SourceWriter */
    protected $writer;

    public static $showWarnings=true;
    public static $throwErrors=true;
    protected static function notImplemented($expression,$message, $throw=false){
        if ($expression){
            $msg = "not implemented ".$message.PHP_EOL;
            if ($throw){
                if (self::$throwErrors==false)
                    throw new \RuntimeException($message);
            }
            if (self::$showWarnings==false) return;
            echo $msg;
        }
    }

}