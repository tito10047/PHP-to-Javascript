<?php
/**
 * Created by PhpStorm.
 * User: Jozef Môstka
 * Date: 24.5.2016
 * Time: 19:14
 */

namespace phptojs\JsPrinter;


use PhpParser\Error;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\AssignOp;
use PhpParser\Node\Expr\BinaryOp;
use PhpParser\Node\Expr\Cast;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar;
use PhpParser\Node\Scalar\MagicConst;
use PhpParser\Node\Stmt;
use phptojs\Printer\SourceWriter;


class ClosureHelper {
	private $arrayIndex = null;
	private $arrayIndexStack = array();

	public function pushArrayIndex() {
		$this->arrayIndexStack[] = $this->arrayIndex;
		$this->arrayIndex = 0;
	}

	public function popArrayIndex() {
		$this->arrayIndex = array_pop($this->arrayIndexStack);
	}

	public function arrayIndex() {
		return $this->arrayIndex++;
	}

	private $isNamespace = false;
	/** @var Node\Name */
	private $namespace;

	public function setNamespace($is, $namespace) {
		$this->isNamespace = $is;
		$this->namespace = $namespace;
	}

	public function isNamespace() {
		return $this->isNamespace;
	}

	/**
	 * @var Stmt\ClassConst[]
	 */
	private $classConstants = array();
	/**
	 * @var Stmt\ClassMethod[]
	 */
	private $classPublicMethods = array();
	private $classPrivateMethods = array();
	private $_classHasConstructor = false;
	private $classConstructorParams = null;
	/**
	 * @var Stmt\PropertyProperty[]
	 */
	private $classStaticProperties = array();
	private $classIsInterface = false;
	private $nextClassIsInterface = false;
	private $classStack = array();
	private $currentClassName = "";
	private $currentMethodName = "";
	private $currentFunctionName = "";
	private $classHasMagicMethods = false;
	private $classPrivatePropertiesNames = array();
	private $classPrivateMethodsNames = array();
	private $isInsidePrivateMethod = false;

	public function pushClass($className) {

		$this->classStack[] = array(
			0 => $this->classConstants,
			1 => $this->classPublicMethods,
			2 => $this->classConstructorParams,
			3 => $this->classStaticProperties,
			4 => $this->classIsInterface,
			5 => $this->currentClassName,
			6 => $this->currentMethodName,
			7 => $this->currentFunctionName,
			8 => $this->classHasMagicMethods,
			9 => $this->_classHasConstructor,
			10 => $this->classPrivatePropertiesNames,
			11 => $this->classPrivateMethodsNames,
			12 => $this->classPrivateMethods,
			13 => $this->isInsidePrivateMethod
		);
		$this->classConstants = array();
		$this->classPublicMethods = array();
		$this->classConstructorParams = null;
		$this->classStaticProperties = array();
		$this->classIsInterface = $this->nextClassIsInterface;
		$this->nextClassIsInterface = false;
		$this->currentClassName = $className;
		$this->currentMethodName = "";
		$this->currentFunctionName = "";
		$this->classHasMagicMethods = false;
		$this->_classHasConstructor = false;
		$this->classPrivatePropertiesNames = array();
		$this->classPrivateMethodsNames = array();
		$this->classPrivateMethods = array();
		$this->isInsidePrivateMethod = false;
	}

	public function popClass() {
		$data = array_pop($this->classStack);
		$this->classConstants = $data[0];
		$this->classPublicMethods = $data[1];
		$this->classConstructorParams = $data[2];
		$this->classStaticProperties = $data[3];
		$this->classIsInterface = $data[4];
		$this->currentClassName = $data[5];
		$this->currentMethodName = $data[6];
		$this->currentFunctionName = $data[7];
		$this->classHasMagicMethods = $data[8];
		$this->_classHasConstructor = $data[9];
		$this->classPrivatePropertiesNames = $data[10];
		$this->classPrivateMethodsNames = $data[11];
		$this->classPrivateMethods = $data[12];
		$this->isInsidePrivateMethod = $data[13];
	}

	/** @return Stmt\ClassConst[] */
	public function getClassConstants() {
		return $this->classConstants;
	}

	/** @return Stmt\ClassMethod */
	public function getClassConstructor() {
		return $this->classConstructorParams;
	}

	/** @return Stmt\ClassMethod[] */
	public function getClassPublicMethods() {
		return $this->classPublicMethods;
	}

	/** @return Stmt\ClassMethod[] */
	public function getClassPrivateMethods() {
		return $this->classPrivateMethods;
	}

	public function setIsInsidePrivateMethod($isOrNot) {
		$this->isInsidePrivateMethod = $isOrNot;
	}

	public function isInsidePrivateMethod() {
		return $this->isInsidePrivateMethod;
	}

	/** @return Stmt\PropertyProperty[] */
	public function getClassStaticProperties() {
		return $this->classStaticProperties;
	}

	/** @return boolean */
	public function classIsInterface() {
		return $this->classIsInterface;
	}

	/** @param Stmt\ClassConst $classConstant */
	public function addClassConstants(Stmt\ClassConst $classConstant) {
		$this->classConstants[] = $classConstant;
	}

	public function addClassPrivatePropertyName($name) {
		$this->classPrivatePropertiesNames[] = $name;
	}

	public function addClassPrivateMethodName($name) {
		$this->classPrivateMethodsNames[] = $name;
	}

	public function addClassPrivateMethod($method) {
		$this->classPrivateMethods[] = $method;
	}

	public function isClassPrivateProperty($name) {
		return in_array($name, $this->classPrivatePropertiesNames);
	}

	public function isClassPrivateMethod($name) {
		return in_array($name, $this->classPrivateMethodsNames);
	}

	public function hasClassPrivateMethodsOrProperties() {
		return count($this->classPrivatePropertiesNames) > 0 || count($this->classPrivateMethodsNames) > 0;
	}

	/**
	 * @param $classConstructorParams
	 */
	public function setClassConstructorParams($classConstructorParams) {
		$this->_classHasConstructor = true;
		$this->classConstructorParams = $classConstructorParams;
	}

	public function getClassConstructorParams() {
		return $this->classConstructorParams;
	}

	public function classHasConstructor() {
		return $this->_classHasConstructor;
	}

	/** @param boolean $isInterface */
	public function setClassIsInterface($isInterface) {
		$this->classIsInterface = $isInterface;
	}

	public function setNextClassIsInterface() {
		$this->nextClassIsInterface = true;
	}

	public function setClassHasMagicMethods() {
		$this->classHasMagicMethods = true;
	}

	public function getClassHasMagicMethods() {
		return $this->classHasMagicMethods;
	}

	public function getClassName() {
		$className = "";
		if ($this->isNamespace) {
			$className .= $this->getNamespaceName();
			$className .= "\\\\";
		}
		$className .= $this->currentClassName;
		return $className;
	}

	public function getSimpleClassName() {
		return $this->currentClassName;
	}

	public function getNamespaceName() {
		$name = "";
		if ($this->isNamespace) {
			$name .= join("\\\\", $this->namespace->parts);
		}
		return $name;
	}

	public function getMethodName() {
		$methodName = "";
		if ($this->isNamespace) {
			$methodName .= $this->getNamespaceName();
			$methodName .= "\\\\";
		}
		$methodName .= $this->currentClassName;
		$methodName .= "::";
		$methodName .= $this->currentMethodName;
		return $methodName;
	}

	public function getFunctionName() {

		$functionName = "";
		if ($this->isNamespace) {
			$functionName .= $this->getNamespaceName();
			$functionName .= "\\\\";
		}
		$functionName .= $this->currentFunctionName;
		return $functionName;
	}

	public function setMethodName($name) {
		$this->currentMethodName = $name;
	}

	public function setFunctionName($name) {
		$this->currentFunctionName = $name;
	}

	/** @param Stmt\ClassMethod $classMethod */
	public function addClassPublicMethod(Stmt\ClassMethod $classMethod) {
		$this->classPublicMethods[] = $classMethod;
	}

	/** @param Stmt\PropertyProperty $classStaticProperty */
	public function addClassStaticProperty($classStaticProperty) {
		$this->classStaticProperties[] = $classStaticProperty;
	}

	private $varStack = array();
	private $varScopeStack = array();
	private $usedVarStack = array();
	private $usedVarScopeScack = array();

	public function pushVarScope() {
		$this->varScopeStack[] = $this->varStack;
		$this->usedVarScopeScack[] = $this->usedVarStack;
		$this->varStack = array();
		$this->usedVarStack = array();
	}

	public function popVarScope() {
		if (count($this->varStack)) {
			throw new \Exception('var stack is not empty `' . join(',', $this->varStack) . '`');
		}
		$this->varStack = array_pop($this->varScopeStack);
		$this->usedVarStack = array_pop($this->usedVarScopeScack);
	}

	private $isDefScope = false;

	public function isDefScope($isOrNot) {
		$this->isDefScope = $isOrNot;
	}

	public function pushVar($name) {
		if ($name == 'this') {
			return;
		}
		if ($this->isDefScope) {
			$this->usedVarStack[] = $name;
			return;
		}
		if (!in_array($name, $this->varStack) && !in_array($name, $this->usedVarStack))
			$this->varStack[] = $name;
	}

	public function useVar($name) {
		$this->usedVarStack[] = $name;
	}

	public function fileExtend(ClosureHelper $helper) {
		$this->usedVarStack = $helper->usedVarStack;
	}

	public function globalVar() {

	}

	public function getVarsDef() {
		$ret = $this->varStack;
		$this->usedVarStack = array_merge($this->usedVarStack, $this->varStack);
		$this->varStack = array();
		return $ret;
	}

	public function pushLoop() {
	}

	public function popLoop(&$loopName) {
	}

	public function getLoopName($num) {
	}


}

class JsPrinter extends JsPrinterAbstract implements JsPrinterInterface {

	private static function WTF($message = 'WTF', $node = null) {
		var_dump($node);
		throw new Error($message);
	}

	/** @var ClosureHelper */
	private $closureHelper;
	/** @var SourceWriter */
	protected $writer;

	public function __construct() {
		$this->closureHelper = new ClosureHelper();
		$this->writer = new SourceWriter();
	}

	public function pParam(Node\Param $node) {
		$this->notImplemented($node->byRef, "reference param {$node->name} by & ");
		$this->closureHelper->useVar($node->name);
		if ($node->variadic && JsPrinterAbstract::$enableVariadic) {
			$this->print_("...");
		}
		$this->print_($node->name);

	}

	/**
	 * @param Node\Param[] $params
	 * @return string
	 * @throws Error
	 */
	public function pParamDefaultValues(array $params) {
		foreach ($params as $node) {
			if (!$node instanceof Node\Param) {
				throw new Error('this is not instanceof Node\Param but ' . get_class($node));
			}
			if (!$node->default) {
				continue;
			}
			$this->writer
				->print_("if (typeof %{argX} == 'undefined') %{argX}=", $node->name, $node->name);
			$this->p($node->default);
			$this->writer
				->println(";");
		}
		foreach ($params as $paramPos => $node) {
			if (!$node->type) {
				continue;
			}
			if ($node->variadic) {
				$this->println("for(var __paraPos={$paramPos};__paraPos<arguments.length;__paraPos++){");
				$this->indent();
			}
			$this->print_("if (!");
			if (is_string($node->type)) {
				$this->print_("is%{Type}(%{argX})", ucfirst($node->type), $node->name);
			} else {
				$classParts = explode("\\", $node->type);
				if (count($classParts) > 1) {
					$className = "N." . join(".", $classParts);
				} else {
					$className = $node->type;
				}
				if ($node->variadic) {
					$this->print_("%{argX}", "arguments[__paraPos]");
				} else {
					$this->print_("%{argX}", $node->name);
				}
				$this->print_(" instanceof %{Class}", $className);
			}
			$this->println(") throw new Error('bad param type');");

			if ($node->variadic) {
				$this->outdent();
				$this->println("}");
			}
		}
	}

	public function pArg(Node\Arg $node) {//TODO: implement this
		$this->notImplemented($node->unpack, 'unpacking argument by ...');
		$this->notImplemented($node->byRef, 'reference by &');
		$this->closureHelper->isDefScope(true);
		$this->p($node->value);
		$this->closureHelper->isDefScope(false);
	}

	public function pConst(Node\Const_ $node) {
		$this->print_("%{constName} = ", $node->name);
		$this->p($node->value);
	}

	public function pName(Name $node) {
		if (count($node->parts) == 1 && $node->parts[0] == 'parent') {
			$this->print_('parent.prototype');
			return;
		}
		if (count($node->parts) == 1 && $node->parts[0] == 'self') {
			$this->print_($this->closureHelper->getSimpleClassName());
			return;
		}
		if (count($node->parts) == 1 && $node->parts[0] == "FALSE") {
			$this->print_("false");
			return;
		}
		if (count($node->parts) == 1 && $node->parts[0] == "TRUE") {
			$this->print_("true");
			return;
		}
		$this->print_(implode('.', $node->parts));
	}

	public function pName_FullyQualified(Name\FullyQualified $node) {
		if (count($node->parts)>1){
			$this->print_('N.');
		}
		$this->print_(implode('.', $node->parts));
	}

	public function pName_Relative(Name\Relative $node) {//TODO: implement this
		self::WTF('pName_Relative', $node);
		$this->print_('namespace\\' . implode('\\', $node->parts));
	}

	// Magic Constants

	public function pScalar_MagicConst_Class(MagicConst\Class_ $node) {//TODO: implement this
		$this->print_("'{$this->closureHelper->getClassName()}'");
	}

	public function pScalar_MagicConst_Dir(MagicConst\Dir $node) {//TODO: implement this
		$this->notImplemented(true, __METHOD__);
		return '__DIR__';
	}

	public function pScalar_MagicConst_File(MagicConst\File $node) {//TODO: implement this
		$this->notImplemented(true, __METHOD__);
		return '__FILE__';
	}

	public function pScalar_MagicConst_Function(MagicConst\Function_ $node) {//TODO: implement this
		$this->print_("'{$this->closureHelper->getFunctionName()}'");
	}

	public function pScalar_MagicConst_Line(MagicConst\Line $node) {//TODO: implement this
		$this->notImplemented(true, __METHOD__);
		return '__LINE__';
	}

	public function pScalar_MagicConst_Method(MagicConst\Method $node) {//TODO: implement this
		$this->print_("'{$this->closureHelper->getMethodName()}'");
	}

	public function pScalar_MagicConst_Namespace(MagicConst\Namespace_ $node) {//TODO: implement this
		$this->print_("'{$this->closureHelper->getNamespaceName()}'");
	}

	public function pScalar_MagicConst_Trait(MagicConst\Trait_ $node) {//TODO: implement this
		$this->notImplemented(true, __METHOD__);
		return '__TRAIT__';
	}

	public function pScalar_String(Scalar\String_ $node) {
		$str = addcslashes($node->value, '\'\\');
		$str = str_replace(PHP_EOL, '\r\n\\' . PHP_EOL, $str);
		$this->print_('\'' . $str . '\'');
	}

	public function pScalar_Encapsed(Scalar\Encapsed $node) {
		if ($node->getAttribute('kind') === Scalar\String_::KIND_HEREDOC) {
			$label = $node->getAttribute('docLabel');
			if ($label && !$this->encapsedContainsEndLabel($node->parts, $label)) {
				$this->notImplemented(true, "encapsed strig with <<<");
				if (count($node->parts) === 1
					&& $node->parts[0] instanceof Scalar\EncapsedStringPart
					&& $node->parts[0]->value === ''
				) {
					$str = $this->pNoIndent("<<<$label\n$label") . $this->docStringEndToken;
					$this->print_($str);
					return;
				}
				$str = $this->pNoIndent(
						"<<<$label\n" . $this->pEncapsList($node->parts, null) . "\n$label"
					) . $this->docStringEndToken;
				$this->print_($str);
			}
		}
		$this->print_('"');
		$this->pEncapsList($node->parts, '"');
		$this->print_('"');
		$this->println();
	}

	protected function encapsedContainsEndLabel(array $parts, $label) {
		foreach ($parts as $i => $part) {
			$atStart = $i === 0;
			$atEnd = $i === count($parts) - 1;
			if ($part instanceof Scalar\EncapsedStringPart
				&& $this->containsEndLabel($part->value, $label, $atStart, $atEnd)
			) {
				return true;
			}
		}
		return false;
	}

	protected function containsEndLabel($string, $label, $atStart = true, $atEnd = true) {
		$start = $atStart ? '(?:^|[\r\n])' : '[\r\n]';
		$end = $atEnd ? '(?:$|[;\r\n])' : '[;\r\n]';
		return false !== strpos($string, $label)
		&& preg_match('/' . $start . $label . $end . '/', $string);
	}


	public function pExpr_Instanceof(Expr\Instanceof_ $node) {
		$this->p($node->expr);
		$this->print_(" instanceof ");
		if ($node->class instanceof Expr\Variable) {
			$this->print_("N._GET_(");
		} else {
			if ($node->class instanceof Name && count($node->class->parts) > 1) {
				$this->print_("N.");
			}
		}
		$this->p($node->class);
		if ($node->class instanceof Expr\Variable) {
			$this->print_(")");
		}
	}

	// Function calls and similar constructs

	public function pExpr_FuncCall(Expr\FuncCall $node) {
		if ($node->name instanceof Expr\Closure) {
			$this->print_("(");
		}
		$this->p($node->name);
		if ($node->name instanceof Expr\Closure) {
			$this->print_(")");
		}
		$this->print_('(');
		$this->pCommaSeparated($node->args);
		$this->print_(')');
	}

	public function pExpr_MethodCall(Expr\MethodCall $node) {
		if ($node->var instanceof Expr\Variable && $node->var->name == "this" && $this->closureHelper->isClassPrivateMethod($node->name)) {
			$this->print_("__private(");
		}
		$this->pVarOrNewExpr($node->var);
		if ($node->var instanceof Expr\Variable && $node->var->name == "this" && $this->closureHelper->isClassPrivateMethod($node->name)) {
			$this->print_(")");
		}
		$this->print_('.');
		$this->pObjectProperty($node->name);
		if ($node->var instanceof Expr\Variable && $node->var->name == "this" && $this->closureHelper->isClassPrivateMethod($node->name)) {
			$this->print_(".call(this");
			if (count($node->args)>0){
				$this->print_(",");
			}
		}else {
			$this->print_('(');
		}
		$this->pCommaSeparated($node->args);
		$this->print_(')');
	}

	public function pExpr_StaticCall(Expr\StaticCall $node) {//TODO: implement this
		$this->p($node->class);
		$this->print_('.');
		if ($node->name instanceof Expr) {
			if ($node->name instanceof Expr\Variable || $node->name instanceof Expr\ArrayDimFetch) {
				$this->p($node->name);
			} else {
				$this->print_('{');
				$this->p($node->name);
				$this->print_('}');
			}
		} else {
			$this->print_($node->name);
		}
		if (count($node->class->parts) == 1 && $node->class->parts[0] == "parent") {
			$this->print_(".call");
		}
		$this->print_('(');
		if (count($node->class->parts) == 1 && $node->class->parts[0] == "parent") {
			$this->print_("this");
			if (count($node->args) > 0) {
				$this->print_(",");
			}
		}
		$this->pCommaSeparated($node->args);
		$this->print_(')');
	}

	public function pExpr_Include(Expr\Include_ $node) {//TODO: implement this
		$this->notImplemented(true, ' include and require');
		static $map = array(
			Expr\Include_::TYPE_INCLUDE => 'include',
			Expr\Include_::TYPE_INCLUDE_ONCE => 'include_once',
			Expr\Include_::TYPE_REQUIRE => 'require',
			Expr\Include_::TYPE_REQUIRE_ONCE => 'require_once',
		);

		$this->pushDelay();
		$this->p($node->expr);
		$this->popDelayToVar($path);
		$path = substr(substr($path, 1), 0, -1);

		if ($this->ROOT_PATH_TO) {

			$jsPrinter = new self();
			$jsPrinter->closureHelper->fileExtend($this->closureHelper);
			$jsPrinter->jsPrintFileTo($this->ROOT_PATH_FROM . $path, $this->ROOT_PATH_TO . $path . '.js');
		}

		$this->println('eval(%{include}("%{path}.js"))', $map[$node->type], $path);
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

		$pList = [];
		$listNode = null;
		if ($leftNode instanceof Expr\List_) {
			$listNode = $leftNode;
			$leftNode = new Expr\Variable("__LIST_VALUES__");
			$pList = [];
			foreach ($listNode->items as $var) {
				if ($var == null) {
					$pList[] = null;
					continue;
				}
				$this->closureHelper->pushVar($var->value->name);
				$pList[] = $var->value->name;
			}
			$this->closureHelper->pushVar("__LIST_VALUES__");
			$this->printVarDef();
		}

		$this->pPrec($leftNode, $precedence, $associativity, -1);
		if (gettype($operator) == "integer") {
			$this->writer->writeDelay($operator);
		} else {
			$this->writer->print_($operator);
		}
		$this->pPrec($rightNode, $precedence, $associativity, 1);

		if ($listNode instanceof Expr\List_) {
			$this->println(";");
			foreach ($pList as $pos => $varName) {
				if ($varName == null) {
					continue;
				}
				$this->print_("%{varName}=__LIST_VALUES__[%{keyPos}]", $varName, $pos);
				if ($pos < count($pList) - 1) {
					$this->println(";");
				} else {
					$this->println();
				}
			}
		}
	}


	public function pExpr_List(Expr\List_ $node, $force = false) {

	}

	public function pExpr_Variable(Expr\Variable $node) {//TODO: implement this
		$this->closureHelper->pushVar($node->name);
		if ($node->name instanceof Expr) {
			$this->notImplemented(true, "acces by \${name}");
			$this->print_($node->name);
		} else {
			$this->print_($node->name);
		}
	}

	public function pExpr_Array(Expr\Array_ $node) {
		$this->closureHelper->pushArrayIndex();
		$this->print_("{");
		$this->pCommaSeparated($node->items);
		$this->print_("}");
		$this->closureHelper->popArrayIndex();
	}

	public function pExpr_ArrayItem(Expr\ArrayItem $node) {
		$this->notImplemented($node->byRef, ' array value reference &');
		if ($node->key !== null) {
			$this->p($node->key);
		} else {
			$this->print_($this->closureHelper->arrayIndex());
		}
		$this->print_(":");
		$this->p($node->value);
	}

	public function pExpr_ArrayDimFetch(Expr\ArrayDimFetch $node) {//TODO: implement this
		$this->pVarOrNewExpr($node->var);
		$this->print_('[');
		if (null !== $node->dim) {
			$this->p($node->dim);
		}
		$this->print_(']');
	}

	public function pExpr_ConstFetch(Expr\ConstFetch $node) {//TODO: implement this
		$this->p($node->name);
	}

	public function pExpr_ClassConstFetch(Expr\ClassConstFetch $node) {//TODO: implement this
		if (count($node->class->parts)==1 && $node->class->parts[0]=="self"){
			$this->print_($this->closureHelper->getSimpleClassName());
		}else {
			$this->p($node->class);
		}
		$this->print_('.' . $node->name);
	}

	public function pExpr_PropertyFetch(Expr\PropertyFetch $node) {//TODO: implement this
		if ($node->var instanceof Expr\Variable && $node->var->name == "this" && $this->closureHelper->isClassPrivateProperty($node->name)) {
			$this->print_("__private(");
		}
		$this->pVarOrNewExpr($node->var);
		if ($node->var instanceof Expr\Variable && $node->var->name == "this" && $this->closureHelper->isClassPrivateProperty($node->name)) {
			$this->print_(")");
		}
		if (!($node->name instanceof Expr)) {
			$this->print_('.');
		}
		$this->pObjectProperty($node->name);
	}

	public function pExpr_StaticPropertyFetch(Expr\StaticPropertyFetch $node) {//TODO: implement this
		$this->p($node->class);
		$this->print_('.');
		$this->pObjectProperty($node->name);
	}

	public function pExpr_ShellExec(Expr\ShellExec $node) {//TODO: implement this
		$this->notImplemented(true, "shell exec", true);
	}

	private $useByRef = null;
	private $useByRedStack = array();

	protected function printUseByRefDef() {
		if ($this->useByRef !== null) {
			$useByRef = $this->useByRef;
			$this->useByRef = null;
			$this->println($useByRef);
		}
	}


	public function pExpr_Closure(Expr\Closure $node) {//TODO: implement this
		$this->notImplemented($node->byRef, "closure reference by &");
		if ($node->static) {
			self::WTF();
			$this->print_('static');
		}
		$this->closureHelper->pushVarScope();
		$useByRef = array();
		if (!empty($node->uses)) {
			$useByRef2 = array();
			foreach ($node->uses as $use) {
				$this->closureHelper->useVar($use->var);
				if (!$use->byRef) {
					$useByRef2[] = $use->var . '_=' . $use->var;
					$useByRef[] = $use->var . '=' . $use->var . '_';
				}
			}
			if (count($useByRef2)) {
				$this->useByRedStack[] = 'var ' . join(',', $useByRef2) . ';';
			}
		}

		$this->print_('function(');
		$this->closureHelper->isDefScope(true);
		$this->pCommaSeparated($node->params);
		$this->closureHelper->isDefScope(false);
		$this->print_(")");
		$this->println("{")
			->indent();
		if (count($useByRef)) {
			$this->println('var ' . join(',', $useByRef) . ';');
		}

		$this->pushDelay(true);
		$this->pStmts($node->stmts);
		$this->popDelayToVar($body);

		$this->printVarDef();
		$this->print_($body);

		$this->outdent()
			->println("}");
		if ($this->useByRef !== null) {
			throw new \Exception('method printUseByRefDef must be used!!');
		}
		if (count($useByRef)) {
			$this->useByRef = array_pop($this->useByRedStack);
		}
		$this->closureHelper->popVarScope();
	}

	public function pExpr_ClosureUse(Expr\ClosureUse $node) {//TODO: implement this
		throw new \Exception('What you doing here??');
	}

	public function pExpr_New(Expr\New_ $node) {//TODO: implement this
		$this->print_("new ");
		if ($node->class instanceof Expr\Variable) {
			$this->print_("(N._GET_(");
		}
		if ($node->class instanceof Stmt\Class_) {
			$node->class->parameters = $node->args;
		}
		$this->p($node->class);
		if ($node->class instanceof Expr\Variable) {
			$this->print_("))");
		}
		if (!$node->class instanceof Stmt\Class_) {
			$this->print_('(');
			$this->pCommaSeparated($node->args);
			$this->print_(')');
		}
	}

	public function pExpr_Clone(Expr\Clone_ $node) {//TODO: implement this
		$this->notImplemented(true, "cloning by clone");
	}

	public function pExpr_Ternary(Expr\Ternary $node) {//TODO: implement this
		// a bit of cheating: we treat the ternary as a binary op where the ?...: part is the operator.
		// this is okay because the part between ? and : never needs parentheses.
		$this->pushDelay();
		$this->print_("?");
		if ($node->if !== null) {
			$this->p($node->if);
		}
		$this->print_(":");
		$this->popDelay($delayId);
		$this->pInfixOp('Expr_Ternary', $node->cond, $delayId, $node->else);
		/*$this->pInfixOp('Expr_Ternary',
			$node->cond, ' ?' . (null !== $node->if ? ' ' . $this->p($node->if) . ' ' : '') . ': ', $node->else
		);*/
	}

	public function pExpr_Exit(Expr\Exit_ $node) {
		$this->print_("throw new Exit(");
		if ($node->expr !== null) {
			$this->p($node->expr);
		}
		$this->println(");");
	}

	public function pExpr_Yield(Expr\Yield_ $node) {//TODO: implement this
		$this->notImplemented(true, "using yield", true);
	}

	/**
	 * @param null|Node\Name $node
	 * @param $pos
	 */
	private function printNamespaceVar($node,$pos=0){
		if ($node===null){
			return;
		}
		if ($pos==0){
			$this->print_("/** @var {{");
		}
		$this->print_("%{name}: {",$node->parts[$pos]);
		if (count($node->parts)>$pos+1) {
			$this->printNamespaceVar($node, $pos + 1);
		}
		$this->print_("}");
		if ($pos==0){
			$this->println("}} N*/");
		}
	}

	public function pStmt_Namespace(Stmt\Namespace_ $node) {//TODO: implement this
		if ($node->name !== null) {
			$this->closureHelper->setNamespace(true, $node->name);
			$this->printNamespaceVar($node->name);
			$this->print_("N._INIT_('");
			$this->p($node->name);
			$this->println("');")
				->println("(function(){");

			$this->indent();
			$this->println("for(var __ClassName in this){");
			$this->indentln('eval("var "+__ClassName+" = this."+__ClassName+";");');
			$this->println("}");
			$this->outdent();

			$this->closureHelper->pushVarScope();
			$this->pStmts($node->stmts);
			$this->closureHelper->popVarScope();
			$this->print_("}).call(N.");
			$this->p($node->name);
			$this->println(");");
			$this->closureHelper->setNamespace(false, null);
		} else {
			$this->pStmts($node->stmts);
		}
	}

	public function pStmt_Use(Stmt\Use_ $node) {//TODO: implement this
		foreach ($node->uses as $node) {
			$this->print_("var %{varName} = N.", $node->alias ? $node->alias : $node->name->getLast());
			$this->p($node->name);
			$this->println(";");
		}
	}

	public function pStmt_GroupUse(Stmt\GroupUse $node) {//TODO:
		$this->notImplemented(true, __METHOD__);
	}

	public function pStmt_UseUse(Stmt\UseUse $node) {//TODO::
		$this->notImplemented(true, __METHOD__);
	}

	public function pUseType($type) {//TODO:
		$this->notImplemented(true, __METHOD__);
	}

	public function pStmt_Interface(Stmt\Interface_ $node) {
		$classWrapper = new Stmt\Class_($node->name,
			array(
				'type' => $node->getType(),
				'name' => $node->name,
				'extends' => null,
				'implements' => $node->extends,
				'stmts' => $node->stmts,
			), $node->getAttributes());
		$this->closureHelper->setNextClassIsInterface();

		$this->pStmt_Class($classWrapper);
	}

	public function pStmt_Class(Stmt\Class_ $node) {//TODO: implement this
		
		if ($node->name != null) {
			$className = $node->name;
		} else {
			$className = "__anonymous__";
		}
		$this->closureHelper->pushClass($className);

		$anonymousClassParameters = array();
		if (isset($node->parameters)) {
			$anonymousClassParameters = $node->parameters;
		}

		$this->pushDelay();

		if ($node->extends || $node->implements) {
			$this->indent()
				->print_("__extends(%{ClassName}, %{parent}", $className, $node->extends ? 'parent' : 'null');
			if ($node->implements) {
				$this->print_(',arguments[1]');
			}
			$this->println(');')
				->outdent();
		}
		$this->popDelayToVar($extends);

		$this->pushDelay();
		$this->closureHelper->pushVarScope();
		$this->pStmts($node->stmts);
		$this->closureHelper->popVarScope();
		$this->popDelay($constructorBody);

		$this->pushDelay()->indent();
		if (is_int($node->flags) && $node->flags & Stmt\Class_::MODIFIER_ABSTRACT) {
			$this->println('%{ClassName}.prototype.__isAbstract__=true;', $className);
		}
		foreach ($this->closureHelper->getClassStaticProperties() as $property) {
			/** @var Stmt\PropertyProperty $property */
			//if ($node->type & Stmt\Class_::MODIFIER_STATIC){ TODO: implement private static property
			$comments = $property->getAttribute('comments', array());
			if ($comments) {
				$this->pComments($comments);
				$property->setAttribute("comments",[]);
			}
			$this->print_("%{ClassName}.", $node->name);
			$this->p($property);
			$this->println(";");
			//}
		}

		foreach ($this->closureHelper->getClassConstants() as $consts) {
			/** @var Stmt\ClassConst $consts */
			foreach ($consts->consts as $cons) {
				$comments = $cons->getAttribute('comments', array());
				if ($comments) {
					$this->pComments($comments);
					$cons->setAttribute("comments",[]);
				}
				$this->print_("%{ClassName}.", $className);
				$this->pConst($cons);
				$this->println(";");
			}
		}

		foreach ($this->closureHelper->getClassPublicMethods() as $method) {
			$comments = $method->getAttribute('comments', array());
			if ($comments) {
				$this->pComments($comments);
				$method->setAttribute("comments",[]);
			}
			/** @var Stmt\ClassMethod $method */
			$this->print_("%{ClassName}.%{prototype}", $className, $method->type & Stmt\Class_::MODIFIER_STATIC ? "" : "prototype.");
			$this->pStmt_ClassMethod($method, true);
		}

		if ($this->closureHelper->getClassHasMagicMethods()) {
			$this->println("var __handler = {")
				->indent()
				->println("construct: function(target, args) {")
				->indent()
				->println("var obj = Object.create(%{ClassName}.prototype);", $className)
				->println("%{ClassName}.apply(obj,args);", $className)
				->println("return new Proxy(obj,__PROXY_HANDLER);")
				->outdent()
				->println("}")
				->outdent()
				->println("};")
				->println("return new Proxy(%{ClassName}, __handler);", $className);
		} else {
			$this->println("return %{ClassName};", $className);
		}
		$this->outdent()
			->popDelay($methodsAndOthers);

		$this->pushDelay()->indent();
		$this->print_("function %{ClassName}(", $className);
		if ($this->closureHelper->classHasConstructor()) {
			$this->pCommaSeparated($this->closureHelper->getClassConstructorParams());
		}
		$this->println("){");
		$this->indent();
		if ($this->closureHelper->classHasConstructor()) {
			$this->println("var __isInheritance=__IS_INHERITANCE__;");
		}
		if ($node->extends) {
			$this->println("window.__IS_INHERITANCE__=true;");
			$this->println("parent.call(this);");
		} else {
			$this->println("window.__IS_INHERITANCE__=false;");
		}
		if ($this->closureHelper->classIsInterface()) {
			$this->println('__INTERFACE_NEW__();');
		}
		$this->writeDelay($constructorBody);

		foreach ($this->closureHelper->getClassPrivateMethods() as $method) {
			$comments = $method->getAttribute('comments', array());
			if ($comments) {
				$this->pComments($comments);
				$method->setAttribute("comments",[]);
			}
			/** @var Stmt\ClassMethod $method */
			$this->println("__private(this).%{methodName}=__%{methodName};",$method->name,$method->name);
		}
		if ($this->closureHelper->classHasConstructor()) {
			$this->println("if (__isInheritance==false){");
			$this->indent();
				$this->print_("this.__construct(");
				$this->pCommaSeparated($this->closureHelper->getClassConstructorParams());
				$this->println(");");
			$this->outdent();
			$this->println("}");
		}else {
			$this->println("if (this.__construct){");
			$this->indentln("this.__construct.apply(this,arguments);");
			$this->println("}");
		}
		$this->outdent()
			->println("}")
			->outdent()
			->print_($extends)
			->popDelay($classBody);

		$format = "";
		$params = [];
		if ($node->name != null) {
			$format .= "var %{Class} = ";
			$params[] = $node->name;
		}
		if ($this->closureHelper->isNamespace() && !isset($node->parameters)) {
			$format .= "%{useNamSPC}";
			$params[] = "this.{$node->name} = ";
		}
		$format .= "(function (%{useParent}";
		$params[] = $node->extends ? 'parent' : '';
		call_user_func_array(array($this->writer, "print_"), array_merge([$format], $params));

		if (count($anonymousClassParameters)) {
			if ($node->implements) {
				$this->print_(",__implements");
			}
			$this->print_(",");
			$this->pCommaSeparated($anonymousClassParameters);
		}

		$this->println("){");
		if ($this->closureHelper->hasClassPrivateMethodsOrProperties()) {
			$this->indentln("var __private = __PRIVATIZE__();");
		}
		foreach ($this->closureHelper->getClassPrivateMethods() as $method) {
			/** @var Stmt\ClassMethod $method */
			$this->print_("var __");
			$this->pStmt_ClassMethod($method, true);
		}
		$this->writeDelay($classBody);
		$this->writeDelay($methodsAndOthers);

		$this->print_("})(");
		if ($node->extends || $node->implements) {
			if ($node->extends){
				$this->p($node->extends);
			}else{
				$this->print_('null');
			}
			if ($node->implements) {
				$this->print_(',[');
				$this->pCommaSeparated($node->implements);
				$this->print_("]");
			}
		}
		if (count($anonymousClassParameters)) {
			$this->print_(",");
			$this->pCommaSeparated($anonymousClassParameters);
		}
		$this->print_(")");
		if (!isset($node->parameters)) {
			$this->print_(";");
		}
		$this->println();
		$this->closureHelper->popClass();
		//$this->outdent();
	}

	public function pStmt_Trait(Stmt\Trait_ $node) {//TODO: implement this
		$this->notImplemented(true, "tait", true);
	}

	public function pStmt_TraitUse(Stmt\TraitUse $node) {//TODO: implement this
		$this->notImplemented(true, "use tait", true);
	}

	public function pStmt_TraitUseAdaptation_Precedence(Stmt\TraitUseAdaptation\Precedence $node) {//TODO: implement this
		$this->notImplemented(true, "pStmt_TraitUseAdaptation_Precedence", true);
	}

	public function pStmt_TraitUseAdaptation_Alias(Stmt\TraitUseAdaptation\Alias $node) {//TODO: implement this
		$this->notImplemented(true, "pStmt_TraitUseAdaptation_Alias", true);
	}

	public function pStmt_Property(Stmt\Property $node) {//TODO: implement this
		foreach ($node->props as $property) {
			if (!$property->default) {
				$property->default = new \PhpParser\Node\Expr\ConstFetch(new \PhpParser\Node\Name('null', $property->getAttributes()), $property->getAttributes());
			}
		}
		if ($node->type & Stmt\Class_::MODIFIER_STATIC) {
			foreach ($node->props as $prop) {
				$this->closureHelper->addClassStaticProperty($prop);
			}
			return;
		}
		foreach ($node->props as $property) {
			if ($node->type & Stmt\Class_::MODIFIER_PRIVATE) {
				$this->closureHelper->addClassPrivatePropertyName($property->name);
				$this->print_("__private(");
			}
			$this->print_("this");
			if ($node->type & Stmt\Class_::MODIFIER_PRIVATE) {
				$this->print_(")");
			}
			$this->print_(".");
			$this->p($property);
			$this->println(";");
		}
	}

	public function pStmt_PropertyProperty(Stmt\PropertyProperty $node) {//TODO: implement this
		$this->print_($node->name);
		if ($node->default !== null) {
			$this->print_(" = ");
			$this->p($node->default);
		}
	}

	public function pStmt_ClassMethod(Stmt\ClassMethod $node, $force = false) {//TODO: implement this
		if ($force) {
			if (in_array($node->name, ["__get", "__set", "__call"])) {
				$this->closureHelper->setClassHasMagicMethods();
			}
			if ($node->name == "__construct") {
				$this->closureHelper->setClassConstructorParams($node->params);
			}
			$this->closureHelper->pushVarScope();

			$this->notImplemented($node->byRef, 'method return reference');
			//return //$this->pModifiers($node->type)
			$this->closureHelper->setMethodName($node->name);
			$this->print_($node->name);
			$this->print_(" = function(");
			$this->pCommaSeparated($node->params);
			$this->println("){");
			if ($node->stmts !== null) {
				$this->pParamDefaultValues($node->params);

				$this->pushDelay(true);
				$this->pStmts($node->stmts);
				$this->popDelayToVar($body);

				$this->printVarDef();
				$this->print_($body);
			} else {
				if ($this->closureHelper->classIsInterface()) {
					$this->println("__INTERFACE_FUNC__();");
				} else if ($node->isAbstract()) {
					$this->println("__ABSTRACT_FUNC__();");
				} else {
					self::WTF('where is body???');
				}
			};
			$this->println("};");
			$this->closureHelper->popVarScope();
		} else {
			if ($node->type & Stmt\Class_::MODIFIER_PRIVATE) {
				$this->closureHelper->addClassPrivateMethodName($node->name);
				$this->closureHelper->addClassPrivateMethod($node);
			} else {
				$this->closureHelper->addClassPublicMethod($node);
			}
		}
	}

	public function pStmt_ClassConst(Stmt\ClassConst $node) {
		$this->closureHelper->addClassConstants($node);
	}

	public function pStmt_Function(Stmt\Function_ $node) {
		$this->closureHelper->pushVarScope();
		$this->notImplemented($node->byRef, "function return reference by function &$node->name(...");
		$this->closureHelper->setFunctionName($node->name);
		if ($this->closureHelper->isNamespace()) {
			$this->println("var %{name} = this.%{name} = function(", $node->name, $node->name);
		} else {
			$this->print_("function %{name}(", $node->name);
		}
		$this->pCommaSeparated($node->params);
		$this->println("){")
			->indent();
		//TODO: where is use keyword???
		$this->pParamDefaultValues($node->params);

		$this->pushDelay(true);
		$this->pStmts($node->stmts);
		$this->popDelayToVar($body);

		$this->printVarDef();
		$this->print_($body);
		$this->outdent()
			->print_('}');
		if ($this->closureHelper->isNamespace()) {
			$this->print_(";");
		}
		$this->println();
		$this->closureHelper->popVarScope();
	}

	public function pStmt_Const(Stmt\Const_ $node) {
		if ($this->closureHelper->isNamespace()) {
			foreach ($node->consts as $const) {
				$this->print_("var %{varName} = ", $const->name);
				$this->p($const->value);
				$this->println(";this.%{varName}=%{varName};", $const->name, $const->name);
			}
		} else {
			foreach ($node->consts as $const) {
				$this->print_("window.%{varName} = ", $const->name);
				$this->p($const->value);
				$this->println(";");
			}
		}
	}

	public function pStmt_Declare(Stmt\Declare_ $node) {
		$this->notImplemented(true, "declare()", true);
	}

	public function pStmt_DeclareDeclare(Stmt\DeclareDeclare $node) {
		$this->notImplemented(true, "declare()", true);
	}

	public function pStmt_If(Stmt\If_ $node) {
		$this->print_("if (");
		$this->p($node->cond);
		$this->println("){")
			->indent();
		$this->pStmts($node->stmts);
		$this->outdent()
			->print_("}");
		$this->pImplode($node->elseifs);
		if ($node->else !== null) {
			$this->p($node->else);
		} else {
			$this->println();
		}
	}

	public function pStmt_ElseIf(Stmt\ElseIf_ $node) {
		$this->print_("else if(");
		$this->p($node->cond);
		$this->println("){")
			->indent();
		$this->pStmts($node->stmts);
		$this->outdent()
			->println("}");
	}

	public function pStmt_Else(Stmt\Else_ $node) {
		$this->println("else{");
		$this->pStmts($node->stmts);
		$this->println("}");
	}

	public function pStmt_For(Stmt\For_ $node) {

		$this->pushDelay();
		$this->print_("for(");
		$this->pCommaSeparated($node->init);
		$this->print_("; ");
		$this->pCommaSeparated($node->cond);
		$this->print_("; ");
		$this->pCommaSeparated($node->loop);
		$this->print_(")");
		$this->popDelayToVar($statement);

		$this->pushDelay();
		$this->printVarDef();
		$this->popDelayToVar($vars);


		$this->pushDelay();
		$this->indent();
		$this->pStmts($node->stmts);
		$this->outdent();
		$this->popDelayToVar($loopBody);

		$this->print_($vars)
			->print_($statement)
			->println("{")
			->print_($loopBody)
			->println("}");
	}

	public function pStmt_Foreach(Stmt\Foreach_ $node) {
		$this->notImplemented($node->byRef, "reference by & in foreach value");

		$this->pushDelay();     //expression
		$this->p($node->expr);
		$this->popDelayToVar($expression);

		if ($node->keyVar) {    //key name
			$this->pushDelay();
			$this->p($node->keyVar);
			$this->popDelayToVar($keyName);
		} else {
			$keyName = "_key_";
			$this->closureHelper->pushVar($keyName);
		}

		$this->pushDelay();
		$this->printVarDef();
		$this->popDelayToVar($vars);

		$this->pushDelay();     //value name
		$this->p($node->valueVar);
		$this->popDelayToVar($varName);

		$this->pushDelay();
		$this->printVarDef();
		$this->popDelayToVar($keyVar);

		$this->pushLoop(true);
		$this->pStmts($node->stmts);
		$this->popLoopPrintName($loopBody);

		$this->print_($vars)
			->println("for (%{key} in %{expr}){", $keyName, $expression)
			->indent()
//			->println("if (!%{expr}.hasOwnProperty(%{key})) continue;", $expression, $keyName)
			->println($keyVar)
			->println("%{varName} = %{expr}[%{key}];", $varName, $expression, $keyName)
			->print_($loopBody)
			->outdent()
			->println("}");
	}

	public function pStmt_While(Stmt\While_ $node) {
		$this->pushLoop(true);
		$this->pStmts($node->stmts);
		$this->popLoopPrintName($loopBody);

		$this->pushDelay();
		$this->p($node->cond);
		$this->popDelayToVar($cond);

		$this->println("while(%{cond}){", $cond)
			->indent()
			->print_($loopBody)
			->outdent()
			->println("}");
	}

	public function pStmt_Do(Stmt\Do_ $node) {
		$this->pushLoop(true);
		$this->pStmts($node->stmts);
		$this->popLoopPrintName($loopBody);

		$this->pushDelay(false);
		$this->p($node->cond);
		$this->popDelayToVar($cond);

		$this->println("do {")
			->indent()
			->print_($loopBody)
			->outdent()
			->println("}while (%{cond});", $cond);
	}

	public function pStmt_Switch(Stmt\Switch_ $node) {
		$this->pushDelay();
		$this->p($node->cond);
		$this->popDelayToVar($cond);

		$this->pushLoop(true);
		$this->pStmts($node->cases);
		$this->popLoopPrintName($loopBody);

		$this->println("switch (%{cond}){", $cond)
			->indent()
			->print_($loopBody)
			->outdent()
			->println("}");
	}

	public function pStmt_Case(Stmt\Case_ $node) {
		if ($node->cond !== null) {
			$this->print_("case ");
			$this->p($node->cond);
		} else {
			$this->print_("default");
		}
		$this->println(":")
			->indent();
		$this->pStmts($node->stmts);
		$this->outdent()
			->println();
	}

	public function pStmt_TryCatch(Stmt\TryCatch $node) {//TODO: implement this
		$this->println("try{")
			->indent();
		$this->pStmts($node->stmts);
		$this->outdent();
		$this->println("}catch(__e__){")
			->indent();

		$catches = array();
		$catchesVars = array();
		foreach ($node->catches as $catch) {
			$this->pushDelay();
			$this->pStmt_Catch($catch, $catchesVars);
			$v = null;
			$this->popDelayToVar($v);
			$catches[] = $v;
		}
		$this->println("var %{vars};", join(", ", array_unique($catchesVars)));
		$this->print_(join('else', $catches));
		$this->outdent();
		if ($node->finally !== null) {
			$this->println("}finally{")
				->indent();
			$this->pStmts($node->finally->stmts);
			$this->outdent();

		}
		$this->println("}");
	}

	public function pStmt_Catch(Stmt\Catch_ $node, &$catchesVars) {
		$this->pushDelay(false);
		//TODO: implements multiple types in catch
		$this->p($node->types[0]);
		$this->popDelayToVar($type);
		$this->println("if (__e__ instanceof %{type}){", $type)
			->indent()
			->println("%{varName}=__e__;", $node->var);
		$this->pStmts($node->stmts);
		$this->outdent()
			->println("}");

		$this->closureHelper->useVar($node->var);
		$catchesVars[] = $node->var;
	}

	public function pStmt_Break(Stmt\Break_ $node) {
		$name = '';
		if ($node->num !== null) {
			$name = ' ' . $this->closureHelper->getLoopName($node->num);
		}
		$this->println('break %{name};', $name);
	}

	public function pStmt_Continue(Stmt\Continue_ $node) {
		$name = '';
		if ($node->num !== null) {
			$name = ' ' . $this->closureHelper->getLoopName($node->num);
		}
		$this->println('continue %{name};', $name);
	}

	public function pStmt_Return(Stmt\Return_ $node) {
		$this->print_("return ");
		if ($node->expr !== null) {
			$this->p($node->expr);
		}
		$this->println(";");
	}

	public function pStmt_Throw(Stmt\Throw_ $node) {
		$this->print_("throw ");
		$this->p($node->expr);
		$this->println(";");
	}

	public function pStmt_Label(Stmt\Label $node) {//TODO: implement this
		$this->notImplemented(true, "labels:");
	}

	public function pStmt_Goto(Stmt\Goto_ $node) {//TODO: implement this
		$this->notImplemented(true, 'goto.', true);
		//TODO: implement it. http://stackoverflow.com/questions/9751207/how-can-i-use-goto-in-javascript/23181432#23181432
	}

	public function pStmt_Echo(Stmt\Echo_ $node) {
		$this->print_('console.log(');
		$this->pCommaSeparated($node->exprs);
		$this->print_(');');
	}

	public function pStmt_Static(Stmt\Static_ $node) {//TODO: implement this
		$this->notImplemented(true, " static variables", true);
	}

	public function pStmt_Global(Stmt\Global_ $node) {//TODO: implement this
		$this->notImplemented(true, " global variables", true);
	}

	public function pStmt_StaticVar(Stmt\StaticVar $node) {//TODO: implement this
		$this->notImplemented(true, 'static vars', true);
	}

	public function pStmt_Unset(Stmt\Unset_ $node) {//TODO: implement this
		$this->print_("delete ");
		$this->pCommaSeparated($node->vars);
		$this->println(";");
	}

	public function pStmt_InlineHTML(Stmt\InlineHTML $node) {//TODO: implement this
		$this->notImplemented(true, "InlineHTML", true);
		//return JS_SCRIPT_END . $this->pNoIndent("\n" . $node->value) . JS_SCRIPT_BEGIN;
	}

	public function pStmt_HaltCompiler(Stmt\HaltCompiler $node) {//TODO: implement this
		$this->notImplemented(true, " __halt_compiler()", true);
	}

	public function pStmt_Nop(Stmt\Nop $node) {
		// TODO: Implement pStmt_Nop() method.
		$this->notImplemented(true, __METHOD__);
	}

	public function pType($node) {
		// TODO: Implement pType() method.
		$this->notImplemented(true, __METHOD__);
	}

	public function pClassCommon(Stmt\Class_ $node, $afterClassToken) {
		// TODO: Implement pClassCommon() method.
		$this->notImplemented(true, __METHOD__);
	}

	public function pObjectProperty($node) {//TODO: implement this
		if ($node instanceof Expr) {
			$this->print_("[");
			$this->p($node);
			$this->print_("]");
		} else {
			$this->print_($node);
		}
	}

	public function pModifiers($modifiers) {//TODO: implement this
		/*return ($modifiers & Stmt\Class_::MODIFIER_PUBLIC    ? 'public '    : '')
		. ($modifiers & Stmt\Class_::MODIFIER_PROTECTED ? 'protected ' : '')
		. ($modifiers & Stmt\Class_::MODIFIER_PRIVATE   ? 'private '   : '')
		. ($modifiers & Stmt\Class_::MODIFIER_STATIC    ? 'static '    : '')
		. ($modifiers & Stmt\Class_::MODIFIER_ABSTRACT  ? 'abstract '  : '')
		. ($modifiers & Stmt\Class_::MODIFIER_FINAL     ? 'final '     : '');*/
	}

	public function pEncapsList(array $encapsList, $quote) {
		$str = '';
		foreach ($encapsList as $element) {
			if (is_string($element)) {
				$str = addcslashes($element, '\'\\');
				$str = str_replace(PHP_EOL, '\r\n\\' . PHP_EOL, $str);
				$this->print_($str);
				//$str .= addcslashes($element, "\n\r\t\f\v$" . $quote . "\\");
			} else {
				if ($element instanceof Scalar\EncapsedStringPart) {
					if ($element->value==PHP_EOL){
						$this->print_("\\r\\n\\\n");
					}else {
						$this->print_($element->value);
					}
					continue;
				}
				$this->print_('"+');
				$this->p($element);
				$this->print_('+"');
			}
		}
	}

	public function pDereferenceLhs(Node $node) {
		// TODO: Implement pDereferenceLhs() method.
		$this->notImplemented(true, __METHOD__);
	}

	public function pCallLhs(Node $node) {
		// TODO: Implement pCallLhs() method.
		$this->notImplemented(true, __METHOD__);
	}


	public function pVarOrNewExpr(Node $node) {//TODO: implement this
		if ($node instanceof Expr\New_) {
			$this->print_("(");
			$this->p($node);
			$this->print_(")");
		} else {
			$this->p($node);
		}
	}


	private function pushLoop($atStart) {
		$this->closureHelper->pushLoop();
		$this->pushDelay($atStart);
	}

	private function popLoopPrintName(&$body) {
		$this->popDelayToVar($body);
		$this->closureHelper->popLoop($loopName);
		if ($loopName !== null) {
			$this->print_($loopName . ":");
		}
	}

	/**
	 * @return $this
	 */
	protected function printVarDef() {
		$vars = $this->closureHelper->getVarsDef();
		if (count($vars)) {
			$this->println('var ' . join(',', $vars) . ';');
		}
		return $this;
	}
}