<?php
/**
 * Created by PhpStorm.
 * User: Jozef Môstka
 * Date: 24.5.2016
 * Time: 19:13
 */

namespace phptojs\JsPrinter;

define('JS_SCRIPT_BEGIN','<script>'.PHP_EOL);
define('JS_SCRIPT_END','</script>');


use PhpParser\Node;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinterAbstract;
use PhpToJs\Printer\SourceWriter;

abstract class JsPrinterAbstract extends PrettyPrinterAbstract{

    /** @var SourceWriter */
    protected $writer;

    protected $ROOT_PATH_FROM=null;
    protected $ROOT_PATH_TO=null;
    protected $ROOT_PATH_TO_EXT=null;
    protected $isOnlyJsFile = false;

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

    /**
     * Pretty prints a file of statements (includes the opening <?php tag if it is required).
     *
     * @param array $filePath
     * @param bool $isOnlyJsFile
     * @return string Pretty printed statements
     */
    public function jsPrintFile($filePath, $isOnlyJsFile=false) {
        $this->ROOT_PATH_FROM = dirname($filePath).DIRECTORY_SEPARATOR;
        $this->isOnlyJsFile = $isOnlyJsFile;

        $code = file_get_contents($filePath);

        $parser = (new ParserFactory())->create(ParserFactory::PREFER_PHP7);
        $stmts = $parser->parse($code);

        $code = $this->jsPrint($stmts);

        if (!$isOnlyJsFile){
            $code = JS_SCRIPT_BEGIN.$code.JS_SCRIPT_END;
        }
        $code = str_replace(JS_SCRIPT_BEGIN.JS_SCRIPT_END,"",$code);

        return $code;
    }

    public function jsPrintFileTo($filePath, $dstFilePath){
        $this->ROOT_PATH_TO = dirname($dstFilePath).DIRECTORY_SEPARATOR;
        $this->ROOT_PATH_TO_EXT = pathinfo($dstFilePath,PATHINFO_EXTENSION);
        $isOnlyJsFile= $this->ROOT_PATH_TO_EXT=='js';
        $code = $this->jsPrintFile($filePath,$isOnlyJsFile);
        return file_put_contents($dstFilePath,$code);
    }
    /**
     * Pretty prints a node.
     *
     * @param Node $node Node to be pretty printed
     *
     * @return void
     */
    protected function p(Node $node) {
        $this->{'p' . $node->getType()}($node);
    }

    /**
     * Pretty prints an array of statements.
     *
     * @param Node[] $stmts Array of statements
     *
     * @return string Pretty printed statements
     */
    public function jsPrint(array $stmts) {
        $this->pStmts($stmts, false);
        return $this->writer->getResetCode();
    }
}