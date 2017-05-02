<?php
/**
 * Created by PhpStorm.
 * User: Jozef Môstka
 * Date: 24.5.2016
 * Time: 19:13
 */

namespace phptojs\JsPrinter;

define('JS_SCRIPT_BEGIN', '<script>' . PHP_EOL);
define('JS_SCRIPT_END', '</script>');


use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\AssignOp;
use PhpParser\Node\Expr\BinaryOp;
use PhpParser\Node\Expr\Cast;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar;
use PhpParser\Node\Scalar\MagicConst;
use PhpParser\Node\Stmt;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinterAbstract;
use PhpToJs\Printer\SourceWriter;

abstract class JsPrinterAbstract extends PrettyPrinterAbstract {
	public static $enableVariadic = false;
	/** @var SourceWriter */
	protected $writer;

	protected $ROOT_PATH_FROM = null;
	protected $ROOT_PATH_TO = null;
	protected $ROOT_PATH_TO_EXT = null;
	protected $isOnlyJsFile = false;

	/**
	 * @return array
	 */
	public function getErrors() {
		return $this->errors;
	}

	public static $throwErrors = true;
	protected $errors = [];

	protected function notImplemented($expression, $message, $throw = false) {
		if ($expression) {
			$msg = "not implemented " . $message;
			$this->errors[] = $msg;
			if ($throw) {
				if (self::$throwErrors == true) {
					throw new \RuntimeException($msg);
				} else {
				}
			}
		}
	}

	/**
	 * Pretty prints a file of statements (includes the opening <?php tag if it is required).
	 *
	 * @param array $filePath
	 * @param bool $isOnlyJsFile
	 * @return string Pretty printed statements
	 */
	public function jsPrintFile($filePath, $isOnlyJsFile = false) {
		$this->ROOT_PATH_FROM = dirname($filePath) . DIRECTORY_SEPARATOR;
		$this->isOnlyJsFile = $isOnlyJsFile;

		$code = file_get_contents($filePath);

		$parser = (new ParserFactory())->create(ParserFactory::PREFER_PHP7);
		$stmts = $parser->parse($code);

		$code = $this->jsPrint($stmts);

		if (!$isOnlyJsFile) {
			$code = JS_SCRIPT_BEGIN . $code . JS_SCRIPT_END;
		}
		$code = str_replace(JS_SCRIPT_BEGIN . JS_SCRIPT_END, "", $code);

		return $code;
	}

	public function jsPrintFileTo($filePath, $dstFilePath) {
		$this->ROOT_PATH_TO = dirname($dstFilePath) . DIRECTORY_SEPARATOR;
		$this->ROOT_PATH_TO_EXT = pathinfo($dstFilePath, PATHINFO_EXTENSION);
		$isOnlyJsFile = $this->ROOT_PATH_TO_EXT == 'js';
		$code = $this->jsPrintFile($filePath, $isOnlyJsFile);
		if (!file_exists(dirname($dstFilePath))) {
			mkdir(dirname($dstFilePath), 0777, true);
		}
		return file_put_contents($dstFilePath, $code);
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
	protected function pStmts(array $nodes, $indent = true) {
		foreach ($nodes as $node) {
			$comments=$node->getAttribute('comments', array());
			if ($comments && !($node instanceof Stmt\ClassMethod || $node instanceof Stmt\ClassConst)){
				$this->pComments($comments);
			}

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
			$comment=$comment->getReformattedText();
			$comment=preg_replace('/(@(param|var) )([\w\|\\\\]+)( \$\w*)?/',"$1{\$3}$4",$comment);
			$comment=preg_replace('/(@(param|var) )({[\w\|\\\\]*} )?\$(\w*)/',"$1$3$4",$comment);
			$comment=str_replace(["@var","{\\","\\"],["@type","{N.","."],$comment);
			$this->writer->println($comment);
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
		if (gettype($operator) == "integer") {
			$this->writer->writeDelay($operator);
		} else {
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
		$l = count($nodes);
		for ($i = 0; $i < $l; $i++) {
			$node = $nodes[$i];
			$this->p($node);
			if ($i < $l - 1) {
				$this->writer->print_($glue);
			}
		}
	}


	public function pScalar_LNumber(Scalar\LNumber $node) {
		$this->print_((string)$node->value);
	}

	public function pScalar_DNumber(Scalar\DNumber $node) {
		$stringValue = (string)$node->value;
		if ($stringValue == 'INF') {
			$stringValue = 'Infinity';
		}
		// ensure that number is really printed as float
		$stringValue = ctype_digit($stringValue) ? $stringValue . '.0' : $stringValue;
		$this->print_($stringValue);
	}

	public function pExpr_Assign(Expr\Assign $node) {
		$this->pInfixOp('Expr_Assign', $node->var, ' = ', $node->expr);
	}

	public function pExpr_AssignRef(Expr\AssignRef $node) {
		$this->pInfixOp('Expr_AssignRef', $node->var, ' =& ', $node->expr);
	}

	public function pExpr_AssignOp_Plus(AssignOp\Plus $node) {
		$this->pInfixOp('Expr_AssignOp_Plus', $node->var, ' += ', $node->expr);
	}

	public function pExpr_AssignOp_Minus(AssignOp\Minus $node) {
		$this->pInfixOp('Expr_AssignOp_Minus', $node->var, ' -= ', $node->expr);
	}

	public function pExpr_AssignOp_Mul(AssignOp\Mul $node) {
		$this->pInfixOp('Expr_AssignOp_Mul', $node->var, ' *= ', $node->expr);
	}

	public function pExpr_AssignOp_Div(AssignOp\Div $node) {
		$this->pInfixOp('Expr_AssignOp_Div', $node->var, ' /= ', $node->expr);
	}

	public function pExpr_AssignOp_Concat(AssignOp\Concat $node) {
		$this->pInfixOp('Expr_AssignOp_Concat', $node->var, ' += ', $node->expr);
	}

	public function pExpr_AssignOp_Mod(AssignOp\Mod $node) {
		$this->pInfixOp('Expr_AssignOp_Mod', $node->var, ' %= ', $node->expr);
	}

	public function pExpr_AssignOp_BitwiseAnd(AssignOp\BitwiseAnd $node) {
		$this->pInfixOp('Expr_AssignOp_BitwiseAnd', $node->var, ' &= ', $node->expr);
	}

	public function pExpr_AssignOp_BitwiseOr(AssignOp\BitwiseOr $node) {
		$this->pInfixOp('Expr_AssignOp_BitwiseOr', $node->var, ' |= ', $node->expr);
	}

	public function pExpr_AssignOp_BitwiseXor(AssignOp\BitwiseXor $node) {
		$this->pInfixOp('Expr_AssignOp_BitwiseXor', $node->var, ' ^= ', $node->expr);
	}

	public function pExpr_AssignOp_ShiftLeft(AssignOp\ShiftLeft $node) {
		$this->pInfixOp('Expr_AssignOp_ShiftLeft', $node->var, ' <<= ', $node->expr);
	}

	public function pExpr_AssignOp_ShiftRight(AssignOp\ShiftRight $node) {
		$this->pInfixOp('Expr_AssignOp_ShiftRight', $node->var, ' >>= ', $node->expr);
	}

	public function pExpr_AssignOp_Pow(AssignOp\Pow $node) {//TODO: implement this
		$this->pInfixOp('Expr_AssignOp_Pow', $node->var, ' **= ', $node->expr);
	}

	// Binary expressions

	public function pExpr_BinaryOp_Plus(BinaryOp\Plus $node) {
		$this->pInfixOp('Expr_BinaryOp_Plus', $node->left, ' + ', $node->right);
	}

	public function pExpr_BinaryOp_Minus(BinaryOp\Minus $node) {
		$this->pInfixOp('Expr_BinaryOp_Minus', $node->left, ' - ', $node->right);
	}

	public function pExpr_BinaryOp_Mul(BinaryOp\Mul $node) {
		$this->pInfixOp('Expr_BinaryOp_Mul', $node->left, ' * ', $node->right);
	}

	public function pExpr_BinaryOp_Div(BinaryOp\Div $node) {
		$this->pInfixOp('Expr_BinaryOp_Div', $node->left, ' / ', $node->right);
	}

	public function pExpr_BinaryOp_Concat(BinaryOp\Concat $node) {
		$this->pInfixOp('Expr_BinaryOp_Concat', $node->left, ' . ', $node->right);
	}

	public function pExpr_BinaryOp_Mod(BinaryOp\Mod $node) {
		$this->pInfixOp('Expr_BinaryOp_Mod', $node->left, ' % ', $node->right);
	}

	public function pExpr_BinaryOp_BooleanAnd(BinaryOp\BooleanAnd $node) {
		$this->pInfixOp('Expr_BinaryOp_BooleanAnd', $node->left, ' && ', $node->right);
	}

	public function pExpr_BinaryOp_BooleanOr(BinaryOp\BooleanOr $node) {
		$this->pInfixOp('Expr_BinaryOp_BooleanOr', $node->left, ' || ', $node->right);
	}

	public function pExpr_BinaryOp_BitwiseAnd(BinaryOp\BitwiseAnd $node) {
		$this->pInfixOp('Expr_BinaryOp_BitwiseAnd', $node->left, ' & ', $node->right);
	}

	public function pExpr_BinaryOp_BitwiseOr(BinaryOp\BitwiseOr $node) {
		$this->pInfixOp('Expr_BinaryOp_BitwiseOr', $node->left, ' | ', $node->right);
	}

	public function pExpr_BinaryOp_BitwiseXor(BinaryOp\BitwiseXor $node) {
		$this->pInfixOp('Expr_BinaryOp_BitwiseXor', $node->left, ' ^ ', $node->right);
	}

	public function pExpr_BinaryOp_ShiftLeft(BinaryOp\ShiftLeft $node) {
		$this->pInfixOp('Expr_BinaryOp_ShiftLeft', $node->left, ' << ', $node->right);
	}

	public function pExpr_BinaryOp_ShiftRight(BinaryOp\ShiftRight $node) {
		$this->pInfixOp('Expr_BinaryOp_ShiftRight', $node->left, ' >> ', $node->right);
	}

	public function pExpr_BinaryOp_Pow(BinaryOp\Pow $node) {//TODO: implement this
		$this->pInfixOp('Expr_BinaryOp_Pow', $node->left, ' ** ', $node->right);
	}

	public function pExpr_BinaryOp_LogicalAnd(BinaryOp\LogicalAnd $node) {
		$this->pInfixOp('Expr_BinaryOp_LogicalAnd', $node->left, ' && ', $node->right);
	}

	public function pExpr_BinaryOp_LogicalOr(BinaryOp\LogicalOr $node) {
		$this->pInfixOp('Expr_BinaryOp_LogicalOr', $node->left, ' || ', $node->right);
	}

	public function pExpr_BinaryOp_LogicalXor(BinaryOp\LogicalXor $node) {//TODO: implement this
		$this->pInfixOp('Expr_BinaryOp_LogicalXor', $node->left, ' ^ ', $node->right);
	}

	public function pExpr_BinaryOp_Equal(BinaryOp\Equal $node) {
		$this->pInfixOp('Expr_BinaryOp_Equal', $node->left, ' == ', $node->right);
	}

	public function pExpr_BinaryOp_NotEqual(BinaryOp\NotEqual $node) {
		$this->pInfixOp('Expr_BinaryOp_NotEqual', $node->left, ' != ', $node->right);
	}

	public function pExpr_BinaryOp_Identical(BinaryOp\Identical $node) {
		$this->pInfixOp('Expr_BinaryOp_Identical', $node->left, ' === ', $node->right);
	}

	public function pExpr_BinaryOp_NotIdentical(BinaryOp\NotIdentical $node) {
		$this->pInfixOp('Expr_BinaryOp_NotIdentical', $node->left, ' !== ', $node->right);
	}

	public function pExpr_BinaryOp_Spaceship(BinaryOp\Spaceship $node) {
		//TODO: Implement pExpr_BinaryOp_Spaceship() method.
		$this->notImplemented(true, __METHOD__);
	}

	public function pExpr_BinaryOp_Greater(BinaryOp\Greater $node) {
		$this->pInfixOp('Expr_BinaryOp_Greater', $node->left, ' > ', $node->right);
	}

	public function pExpr_BinaryOp_GreaterOrEqual(BinaryOp\GreaterOrEqual $node) {
		$this->pInfixOp('Expr_BinaryOp_GreaterOrEqual', $node->left, ' >= ', $node->right);
	}

	public function pExpr_BinaryOp_Smaller(BinaryOp\Smaller $node) {
		$this->pInfixOp('Expr_BinaryOp_Smaller', $node->left, ' < ', $node->right);
	}

	public function pExpr_BinaryOp_SmallerOrEqual(BinaryOp\SmallerOrEqual $node) {
		$this->pInfixOp('Expr_BinaryOp_SmallerOrEqual', $node->left, ' <= ', $node->right);
	}

	public function pExpr_BinaryOp_Coalesce(BinaryOp\Coalesce $node) {
		// TODO: Implement pExpr_BinaryOp_Coalesce() method.
		$this->notImplemented(true, __METHOD__);
	}

	// Unary expressions

	public function pExpr_BooleanNot(Expr\BooleanNot $node) {
		$this->pPrefixOp('Expr_BooleanNot', '!', $node->expr);
	}

	public function pExpr_BitwiseNot(Expr\BitwiseNot $node) {//TODO: implement this
		$this->pPrefixOp('Expr_BitwiseNot', '~', $node->expr);
	}

	public function pExpr_UnaryMinus(Expr\UnaryMinus $node) {
		$this->pPrefixOp('Expr_UnaryMinus', '-', $node->expr);
	}

	public function pExpr_UnaryPlus(Expr\UnaryPlus $node) {
		$this->pPrefixOp('Expr_UnaryPlus', '+', $node->expr);
	}

	public function pExpr_PreInc(Expr\PreInc $node) {
		$this->pPrefixOp('Expr_PreInc', '++', $node->var);
	}

	public function pExpr_PreDec(Expr\PreDec $node) {
		$this->pPrefixOp('Expr_PreDec', '--', $node->var);
	}

	public function pExpr_PostInc(Expr\PostInc $node) {
		$this->pPostfixOp('Expr_PostInc', $node->var, '++');
	}

	public function pExpr_PostDec(Expr\PostDec $node) {
		$this->pPostfixOp('Expr_PostDec', $node->var, '--');
	}

	public function pExpr_ErrorSuppress(Expr\ErrorSuppress $node) {//TODO: implement this
		$this->notImplemented(true, 'ErrorSuppress by @', true);
		$this->pPrefixOp('Expr_ErrorSuppress', '@', $node->expr);
	}

	public function pExpr_YieldFrom(Expr\YieldFrom $node) {
		// TODO: Implement pExpr_YieldFrom() method.
		$this->notImplemented(true, __METHOD__);
	}

	public function pExpr_Print(Expr\Print_ $node) {
		// TODO: Implement pExpr_Print() method.
		$this->print_("console.log(");
		$this->p($node->expr);
		$this->print_(")");
	}

	// Casts

	public function pExpr_Cast_Int(Cast\Int_ $node) {
		$this->print_("parseInt(");
		$this->p($node->expr);
		$this->print_(")");
	}

	public function pExpr_Cast_Double(Cast\Double $node) {
		$this->print_("parseFloat(");
		$this->p($node->expr);
		$this->print_(")");
	}

	public function pExpr_Cast_String(Cast\String_ $node) {
		$this->print_("(");
		$this->p($node->expr);
		$this->print_(").toString()");
	}

	public function pExpr_Cast_Array(Cast\Array_ $node) {//TODO: implement this
		$this->notImplemented(true, ' conversion to (array)', true);
		$this->pPrefixOp('Expr_Cast_Array', '(array) ', $node->expr);
	}

	public function pExpr_Cast_Object(Cast\Object_ $node) {//TODO: implement this
		$this->notImplemented(true, ' conversion to (object)', true);
		$this->pPrefixOp('Expr_Cast_Object', '(object) ', $node->expr);
	}

	public function pExpr_Cast_Bool(Cast\Bool_ $node) {
		return "Boolean(" . $this->p($node->expr) . ")";
	}

	public function pExpr_Cast_Unset(Cast\Unset_ $node) {//TODO: implement this
		$this->notImplemented(true, __METHOD__);
		$this->pPrefixOp('Expr_Cast_Unset', 'delete ', $node->expr);
	}

	public function pExpr_Empty(Expr\Empty_ $node) {//TODO: implement this
		$this->print_('empty(');
		$this->p($node->expr);
		$this->print_(')');
	}

	public function pExpr_Isset(Expr\Isset_ $node) {//TODO: implement this
		$this->print_('isset(');
		$this->pCommaSeparated($node->vars);
		$this->print_(')');
	}

	public function pExpr_Eval(Expr\Eval_ $node) {//TODO: implement this
		$this->print_('eval(');
		$this->p($node->expr);
		$this->print_(')');
	}

	/**
	 * @param null $atStart
	 * @return JsPrinter
	 */
	public function pushDelay($atStart = null) {
		call_user_func_array(array($this->writer, __FUNCTION__), func_get_args());
		return $this;
	}

	/**
	 * @param null $id
	 * @return JsPrinter
	 */
	public function popDelay(&$id = null) {
		$this->writer->popDelay($id);
		return $this;
	}

	/**
	 * @param $var
	 * @return $this
	 */
	public function popDelayToVar(&$var) {
		$this->writer->popDelayToVar($var);
		return $this;
	}

	/**
	 * @param $id
	 * @return JsPrinter
	 */
	public function writeDelay($id) {
		call_user_func_array(array($this->writer, __FUNCTION__), func_get_args());
		return $this;
	}

	/**
	 * @return JsPrinter
	 */
	public function writeLastDelay() {
		call_user_func_array(array($this->writer, __FUNCTION__), func_get_args());
		return $this;
	}

	/**
	 * @param $string
	 * @param ... $objects
	 * @return JsPrinter
	 */
	public function println($string = '', $objects = null) {
		call_user_func_array(array($this->writer, __FUNCTION__), func_get_args());
		return $this;
	}

	/**
	 * @param $string
	 * @param ... $objects
	 * @return JsPrinter
	 */
	public function print_($string, $objects = null) {
		call_user_func_array(array($this->writer, __FUNCTION__), func_get_args());
		return $this;
	}

	/**
	 * @return JsPrinter
	 */
	public function indent() {
		call_user_func_array(array($this->writer, __FUNCTION__), func_get_args());
		return $this;
	}

	/**
	 * @return JsPrinter
	 */
	public function outdent() {
		call_user_func_array(array($this->writer, __FUNCTION__), func_get_args());
		return $this;
	}

	/**
	 * @param $string
	 * @param ... $objects
	 * @return JsPrinter
	 */
	public function indentln($string, $objects = null) {
		call_user_func_array(array($this->writer, __FUNCTION__), func_get_args());
		return $this;
	}
}