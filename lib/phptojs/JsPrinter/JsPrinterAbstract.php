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
        if (!file_exists(dirname($dstFilePath))){
            mkdir(dirname($dstFilePath),0777,true);
        }
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


    /**
     * @see PhpParser\Printer\PrinterAbstract::pStmts
     * @param array $nodes
     * @param bool $indent
     * @return string|void
     */
    protected function pStmts(array $nodes, $indent=true) {
        foreach ($nodes as $node) {
            $this->pComments($node->getAttribute('comments', array()));

            $this->writer->pushDelay();
            $this->p($node);
            $this->writer->popDelayToVar($stmts);

            $this->printVarDef();
            $this->printUseByRefDef();
            $this->writer->print_($stmts);

            $this->writer->println($node instanceof Node\Expr ? ';' : '');
        }
    }

    abstract protected function printUseByRefDef();
    abstract protected function printVarDef();

    /**
     * @param \PhpParser\Comment[] $comments
     * @return void
     * @return string
     */
    protected function pComments(array $comments) {
        foreach ($comments as $comment) {
            $this->writer->println($comment->getReformattedText());
        }
    }

    /**
     * @see PhpParser\Printer\PrinterAbstract::pInfixOp
     * @param $type
     * @param \PhpParser\Node $leftNode
     * @param string|int $operator or delayId
     * @param \PhpParser\Node $rightNode
     * @return void
     */
    protected function pInfixOp($type, Node $leftNode, $operator, Node $rightNode) {
        list($precedence, $associativity) = $this->precedenceMap[$type];

        $this->pPrec($leftNode, $precedence, $associativity, -1);
        if (gettype($operator)=="integer"){
            $this->writer->writeDelay($operator);
        }else {
            $this->writer->print_($operator);
        }
        $this->pPrec($rightNode, $precedence, $associativity, 1);
    }

    /**
     * @see PhpParser\Printer\PrinterAbstract::pPrefixOp
     * @param $type
     * @param $operatorString
     * @param \PhpParser\Node $node
     * @return void
     */
    protected function pPrefixOp($type, $operatorString, Node $node) {
        list($precedence, $associativity) = $this->precedenceMap[$type];
        $this->writer->print_($operatorString);
        $this->pPrec($node, $precedence, $associativity, 1);
    }

    /**
     * @see PhpParser\Printer\PrinterAbstract::pPostfixOp
     * @param $type
     * @param \PhpParser\Node $node
     * @param $operatorString
     * @return void
     */
    protected function pPostfixOp($type, Node $node, $operatorString) {
        list($precedence, $associativity) = $this->precedenceMap[$type];
        $this->pPrec($node, $precedence, $associativity, -1);
        $this->writer->print_($operatorString);
    }

    /**
     * @see PhpParser\Printer\PrinterAbstract::pPrec
     * @param \PhpParser\Node $node
     * @param int $parentPrecedence
     * @param int $parentAssociativity
     * @param int $childPosition
     * @return void
     */
    protected function pPrec(Node $node, $parentPrecedence, $parentAssociativity, $childPosition) {
        $type = $node->getType();
        if (isset($this->precedenceMap[$type])) {
            $childPrecedence = $this->precedenceMap[$type][0];
            if ($childPrecedence > $parentPrecedence ||
                ($parentPrecedence == $childPrecedence && $parentAssociativity != $childPosition)
            ) {
                $this->writer->print_("(");
                $this->{'p' . $type}($node);
                $this->writer->print_(")");
                return;
            }
        }

        $this->{'p' . $type}($node);
    }

    /**
     * @param array $nodes
     * @param string $glue
     * @return void
     */
    protected function pImplode(array $nodes, $glue = '') {
        $l=count($nodes);
        for ($i=0;$i<$l;$i++) {
            $node=$nodes[$i];
            $this->p($node);
            if ($i<$l-1) {
                $this->writer->print_($glue);
            }
        }
    }
}