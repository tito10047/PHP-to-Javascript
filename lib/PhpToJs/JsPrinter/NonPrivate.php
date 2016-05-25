<?php
/**
 * Created by PhpStorm.
 * User: Jozef Môstka
 * Date: 24.5.2016
 * Time: 19:14
 */

namespace PhpToJs\JsPrinter;


use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\AssignOp;
use PhpParser\Node\Expr\BinaryOp;
use PhpParser\Node\Expr\Cast;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar;
use PhpParser\Node\Scalar\MagicConst;
use PhpParser\Node\Stmt;
use PhpToJs\Printer\SourceWriter;


class ClosureHelper{
    private $arrayIndex = null;
    private $arrayIndexStack = array();
    public function pushArrayIndex(){
        $this->arrayIndexStack[]=$this->arrayIndex;
        $this->arrayIndex=0;
    }

    public function popArrayIndex(){
        $this->arrayIndex = array_pop($this->arrayIndexStack);
    }

    public function arrayIndex(){
        return $this->arrayIndex++;
    }

    private $isNamespace=false;
    public function setNamespace($is){
        $this->isNamespace=$is;
    }
    public function isNamespace(){
        return $this->isNamespace;
    }

    /**
     * @var Stmt\ClassConst[]
     */
    private $classConstants = array();
    /**
     * @var Stmt\ClassMethod[]
     */
    private $classMethods = array();
    private $classConstructor = null;
    /**
     * @var Stmt\PropertyProperty[]
     */
    private $classStaticProperties = array();
    private $classIsInterface = false;
    private $nextClassIsInterface = false;
    private $classStack = array();
    public function pushClass(){
        $this->classStack[]=array(
            0=>$this->classConstants,
            1=>$this->classMethods,
            2=>$this->classConstructor,
            3=>$this->classStaticProperties,
            4=>$this->classIsInterface
        );
        $this->classConstants           = array();
        $this->classMethods             = array();
        $this->classConstructor         = null;
        $this->classStaticProperties    = array();
        $this->classIsInterface         = $this->nextClassIsInterface;
        $this->nextClassIsInterface=false;
    }

    public function popClass(){
        $data = array_pop($this->classStack);
        $this->classConstants           = $data[0];
        $this->classMethods             = $data[1];
        $this->classConstructor         = $data[2];
        $this->classStaticProperties    = $data[3];
        $this->classIsInterface         = $data[4];
    }

    /** @return Stmt\ClassConst[] */
    public function getClassConstants(){
        return $this->classConstants;}
    /** @return Stmt\ClassMethod */
    public function getClassConstructor(){
        return $this->classConstructor;}
    /** @return Stmt\ClassMethod[] */
    public function getClassMethods(){
        return $this->classMethods;}
    /** @return Stmt\PropertyProperty[] */
    public function getClassStaticProperties(){
        return $this->classStaticProperties;}
    /** @return boolean */
    public function classIsInterface(){
        return $this->classIsInterface;}

    /** @param Stmt\ClassConst $classConstant */
    public function addClassConstants($classConstant){
        $this->classConstants = $classConstant;}
    /** @param null $classConstructor */
    public function setClassConstructor($classConstructor){
        $this->classConstructor = $classConstructor;}
    /** @param boolean $isInterface */
    public function setClassIsInterface($isInterface){
        $this->classIsInterface = $isInterface;}
    public function setNextClassIsInterface(){
        $this->nextClassIsInterface=true;
    }
    /** @param Stmt\ClassMethod $classMethod */
    public function addClassMethod(Stmt\ClassMethod $classMethod){
        $this->classMethods[] = $classMethod;}
    /** @param Stmt\PropertyProperty $classStaticProperty */
    public function addClassStaticProperty($classStaticProperty){
        $this->classStaticProperties[] = $classStaticProperty;
    }

    private $varStack = array();
    private $varScopeStack = array();
    private $usedVarStack = array();
    private $usedVarScopeScack = array();
    public function pushVarScope(){
        $this->varScopeStack[] = $this->varStack;
        $this->usedVarScopeScack[] = $this->usedVarStack;
        $this->varStack = array();
        $this->usedVarStack = array();
    }

    public function popVarScope(){
        if (count($this->varStack)){
            throw new \Exception('var stack is not empty `'.join(',',$this->varStack).'`');
        }
        $this->varStack = array_pop($this->varScopeStack);
        $this->usedVarStack = array_pop($this->usedVarScopeScack);
    }

    private $isDefScope=false;
    public function isDefScope($isOrNot){
        $this->isDefScope=$isOrNot;
    }

    public function pushVar($name){
        if ($name=='this'){
            return;
        }
        if ($this->isDefScope){
            $this->usedVarStack[]=$name;
            return;
        }
        if (!in_array($name,$this->varStack) && !in_array($name,$this->usedVarStack))
            $this->varStack[]=$name;
    }

    public function useVar($name){
        $this->usedVarStack[]=$name;
    }

    public function fileExtend(ClosureHelper $helper){
        $this->usedVarStack = $helper->usedVarStack;
    }

    public function globalVar(){

    }

    public function getVarsDef(){
        $ret = $this->varStack;
        $this->usedVarStack = array_merge($this->usedVarStack,$this->varStack);
        $this->varStack = array();
        return $ret;
    }

    public function pushLoop() {
    }
    public function popLoop(&$loopName) {
    }

    public function getLoopName(Scalar\DNumber $num) {
    }

}

class NonPrivate extends JsPrinterAbstract implements JsPrinterInterface{

    private static function WTF($message='WTF',$node=null){
        if (self::$showWarnings==false) return;
        var_dump($node);
        throw new Error($message);
    }

    /** @var ClosureHelper */
    private $closureHelper;
    /** @var SourceWriter */
    protected $writer;
    public function __construct(){
        $this->closureHelper = new ClosureHelper();
        $this->writer = new SourceWriter();
    }

    public function pParam(Node\Param $node) {
        self::notImplemented($node->byRef,"reference param {$node->name} by & ");
        self::notImplemented($node->variadic,"variadic param {$node->name} by ... ");
        $this->closureHelper->useVar($node->name);
        $this->print_($node->name);

    }

    public function pArg(Node\Arg $node) {//TODO: implement this
        self::notImplemented($node->unpack,'unpacking argument by ...');
        self::notImplemented($node->byRef, 'reference by &');
        $this->closureHelper->isDefScope(true);
        $this->p($node->value);
        $this->closureHelper->isDefScope(false);
    }

    public function pConst(Node\Const_ $node) {
        $this->print_("%{constName} = ",$node->name );
        $this->p($node->value);
    }

    public function pName(Name $node) {
        if (count($node->parts)==1 && $node->parts[0]=='parent'){
            $this->print_('parent.prototype');
            return;
        }
        $this->print_(implode('.', $node->parts));
    }

    public function pName_FullyQualified(Name\FullyQualified $node) {
        $this->print_('N.' . implode('.', $node->parts));
    }

    public function pName_Relative(Name\Relative $node) {//TODO: implement this
        self::WTF('pName_Relative',$node);
        $this->print_('namespace\\' . implode('\\', $node->parts));
    }

    // Magic Constants

    public function pScalar_MagicConst_Class(MagicConst\Class_ $node) {//TODO: implement this
        self::notImplemented(true,__METHOD__);
        return '__CLASS__';
    }

    public function pScalar_MagicConst_Dir(MagicConst\Dir $node) {//TODO: implement this
        self::notImplemented(true,__METHOD__);
        return '__DIR__';
    }

    public function pScalar_MagicConst_File(MagicConst\File $node) {//TODO: implement this
        self::notImplemented(true,__METHOD__);
        return '__FILE__';
    }

    public function pScalar_MagicConst_Function(MagicConst\Function_ $node) {//TODO: implement this
        self::notImplemented(true,__METHOD__);
        return '__FUNCTION__';
    }

    public function pScalar_MagicConst_Line(MagicConst\Line $node) {//TODO: implement this
        self::notImplemented(true,__METHOD__);
        return '__LINE__';
    }

    public function pScalar_MagicConst_Method(MagicConst\Method $node) {//TODO: implement this
        self::notImplemented(true,__METHOD__);
        return '__METHOD__';
    }

    public function pScalar_MagicConst_Namespace(MagicConst\Namespace_ $node) {//TODO: implement this
        self::notImplemented(true,__METHOD__);
        return '__NAMESPACE__';
    }

    public function pScalar_MagicConst_Trait(MagicConst\Trait_ $node) {//TODO: implement this
        self::notImplemented(true,__METHOD__);
        return '__TRAIT__';
    }

    public function pScalar_String(Scalar\String_ $node) {
        $str = addcslashes($node->value, '\'\\');
        $str = str_replace(PHP_EOL,'\n\\'.PHP_EOL,$str);
        $this->print_('\'' . $str . '\'');
    }

    public function pScalar_Encapsed(Scalar\Encapsed $node) {
        $this->print_('"');
        $this->pEncapsList($node->parts, '"');
        $this->println('"');
    }

    public function pScalar_LNumber(Scalar\LNumber $node) {
        $this->print_((string) $node->value);
    }

    public function pScalar_DNumber(Scalar\DNumber $node) {
        $stringValue = (string) $node->value;
        if ($stringValue=='INF'){
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
        $this->pInfixOp('Expr_BinaryOp_LogicalAnd', $node->left, ' and ', $node->right);
    }

    public function pExpr_BinaryOp_LogicalOr(BinaryOp\LogicalOr $node) {
        $this->pInfixOp('Expr_BinaryOp_LogicalOr', $node->left, ' or ', $node->right);
    }

    public function pExpr_BinaryOp_LogicalXor(BinaryOp\LogicalXor $node) {//TODO: implement this
        $this->pInfixOp('Expr_BinaryOp_LogicalXor', $node->left, ' xor ', $node->right);
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
        self::notImplemented(true, __METHOD__);
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
        self::notImplemented(true, __METHOD__);
    }

    public function pExpr_Instanceof(Expr\Instanceof_ $node) {
        $this->pInfixOp('Expr_Instanceof', $node->expr, ' instanceof ', $node->class);
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
        self::notImplemented(true,'ErrorSuppress by @',true);
        $this->pPrefixOp('Expr_ErrorSuppress', '@', $node->expr);
    }

    public function pExpr_YieldFrom(Expr\YieldFrom $node) {
        // TODO: Implement pExpr_YieldFrom() method.
        self::notImplemented(true, __METHOD__);
    }

    public function pExpr_Print(Expr\Print_ $node) {
        // TODO: Implement pExpr_Print() method.
        $this->print_("console.log(");
        $this->p($node->expr);
        $this->print_(")");
    }

    // Casts

    public function pExpr_Cast_Int(Cast\Int_ $node) {
        $this->print_("parseInt(".$this->p($node->expr).")");
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
        self::notImplemented(true,' conversion to (array)',true);
        $this->pPrefixOp('Expr_Cast_Array', '(array) ', $node->expr);
    }

    public function pExpr_Cast_Object(Cast\Object_ $node) {//TODO: implement this
        self::notImplemented(true,' conversion to (object)',true);
        $this->pPrefixOp('Expr_Cast_Object', '(object) ', $node->expr);
    }

    public function pExpr_Cast_Bool(Cast\Bool_ $node) {
        return "Boolean(".$this->p($node->expr).")";
    }

    public function pExpr_Cast_Unset(Cast\Unset_ $node) {//TODO: implement this
        $this->pPrefixOp('Expr_Cast_Unset', 'delete ', $node->expr);
    }

    // Function calls and similar constructs

    public function pExpr_FuncCall(Expr\FuncCall $node) {
        $this->p($node->name);
        $this->print_('(');
        $this->pCommaSeparated($node->args);
        $this->print_(')');
    }

    public function pExpr_MethodCall(Expr\MethodCall $node) {
        $this->pVarOrNewExpr($node->var);
        $this->print_('.');
        $this->pObjectProperty($node->name);
        $this->print_('(');
        $this->pCommaSeparated($node->args);
        $this->print_(')');
    }

    public function pExpr_StaticCall(Expr\StaticCall $node) {//TODO: implement this
        $this->p($node->class);
        $this->print_('.');
        if ($node->name instanceof Expr){
            if ($node->name instanceof Expr\Variable || $node->name instanceof Expr\ArrayDimFetch){
                $this->p($node->name);
            }else{
                $this->print_('{');
                $this->p($node->name);
                $this->print_('}');
            }
        }else{
            $this->print_($node->name);
        }
        $this->print_('(');
        $this->pCommaSeparated($node->args);
        $this->print_(')');
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

    public function pExpr_Include(Expr\Include_ $node) {//TODO: implement this
        self::notImplemented(true,' include and require');
        static $map = array(
            Expr\Include_::TYPE_INCLUDE      => 'include',
            Expr\Include_::TYPE_INCLUDE_ONCE => 'include_once',
            Expr\Include_::TYPE_REQUIRE      => 'require',
            Expr\Include_::TYPE_REQUIRE_ONCE => 'require_once',
        );

        $this->pushDelay();
        $this->p($node->expr);
        $this->popDelayToVar($path);
        $path = substr(substr($path,1),0,-1);

        if ($this->ROOT_PATH_TO) {

            $jsPrinter = new self();
            $jsPrinter->closureHelper->fileExtend($this->closureHelper);
            $jsPrinter->jsPrintFileTo($this->ROOT_PATH_FROM.$path, $this->ROOT_PATH_TO.$path.'.js');
        }

        $this->println('eval(%{include}("%{path}.js"))',$map[$node->type],$path);
    }

    public function pExpr_List(Expr\List_ $node) {//TODO: implement this
        self::notImplemented(true,' list()',true);
    }

    public function pExpr_Variable(Expr\Variable $node) {//TODO: implement this
        $this->closureHelper->pushVar($node->name);
        if ($node->name instanceof Expr) {
            self::notImplemented(true,"acces by \${name}");
            //return '${' . $this->p($node->name) . '}';
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
        self::notImplemented($node->byRef,' array value reference &');
        if ($node->key!==null){
            $this->p($node->key);
        }else{
            $this->print_($this->closureHelper->arrayIndex());
        }
        $this->print_(":");
        $this->p($node->value);
    }

    public function pExpr_ArrayDimFetch(Expr\ArrayDimFetch $node) {//TODO: implement this
        $this->pVarOrNewExpr($node->var);
        $this->print_('[');
        if (null !== $node->dim){
            $this->p($node->dim);
        }
        $this->print_(']');
    }

    public function pExpr_ConstFetch(Expr\ConstFetch $node) {//TODO: implement this
        $this->p($node->name);
    }

    public function pExpr_ClassConstFetch(Expr\ClassConstFetch $node) {//TODO: implement this
        $this->p($node->class);
        $this->print_('.' . $node->name);
    }

    public function pExpr_PropertyFetch(Expr\PropertyFetch $node) {//TODO: implement this
        $this->pVarOrNewExpr($node->var);
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

    public function pExpr_ShellExec(Expr\ShellExec $node) {
        // TODO: Implement pExpr_ShellExec() method.
    }

    public function pExpr_Closure(Expr\Closure $node) {
        // TODO: Implement pExpr_Closure() method.
    }

    public function pExpr_ClosureUse(Expr\ClosureUse $node) {
        // TODO: Implement pExpr_ClosureUse() method.
    }

    public function pExpr_New(Expr\New_ $node) {
        // TODO: Implement pExpr_New() method.
    }

    public function pExpr_Clone(Expr\Clone_ $node) {
        // TODO: Implement pExpr_Clone() method.
    }

    public function pExpr_Ternary(Expr\Ternary $node) {
        // TODO: Implement pExpr_Ternary() method.
    }

    public function pExpr_Exit(Expr\Exit_ $node) {
        // TODO: Implement pExpr_Exit() method.
    }

    public function pExpr_Yield(Expr\Yield_ $node) {
        // TODO: Implement pExpr_Yield() method.
    }

    public function pStmt_Namespace(Stmt\Namespace_ $node) {
        // TODO: Implement pStmt_Namespace() method.
    }

    public function pStmt_Use(Stmt\Use_ $node) {
        // TODO: Implement pStmt_Use() method.
    }

    public function pStmt_GroupUse(Stmt\GroupUse $node) {
        // TODO: Implement pStmt_GroupUse() method.
    }

    public function pStmt_UseUse(Stmt\UseUse $node) {
        // TODO: Implement pStmt_UseUse() method.
    }

    public function pUseType($type) {
        // TODO: Implement pUseType() method.
    }

    public function pStmt_Interface(Stmt\Interface_ $node) {
        // TODO: Implement pStmt_Interface() method.
    }

    public function pStmt_Class(Stmt\Class_ $node) {
        // TODO: Implement pStmt_Class() method.
    }

    public function pStmt_Trait(Stmt\Trait_ $node) {
        // TODO: Implement pStmt_Trait() method.
    }

    public function pStmt_TraitUse(Stmt\TraitUse $node) {
        // TODO: Implement pStmt_TraitUse() method.
    }

    public function pStmt_TraitUseAdaptation_Precedence(Stmt\TraitUseAdaptation\Precedence $node) {
        // TODO: Implement pStmt_TraitUseAdaptation_Precedence() method.
    }

    public function pStmt_TraitUseAdaptation_Alias(Stmt\TraitUseAdaptation\Alias $node) {
        // TODO: Implement pStmt_TraitUseAdaptation_Alias() method.
    }

    public function pStmt_Property(Stmt\Property $node) {
        // TODO: Implement pStmt_Property() method.
    }

    public function pStmt_PropertyProperty(Stmt\PropertyProperty $node) {
        // TODO: Implement pStmt_PropertyProperty() method.
    }

    public function pStmt_ClassMethod(Stmt\ClassMethod $node) {
        // TODO: Implement pStmt_ClassMethod() method.
    }

    public function pStmt_ClassConst(Stmt\ClassConst $node) {
        // TODO: Implement pStmt_ClassConst() method.
    }

    public function pStmt_Function(Stmt\Function_ $node) {
        // TODO: Implement pStmt_Function() method.
    }

    public function pStmt_Const(Stmt\Const_ $node) {
        // TODO: Implement pStmt_Const() method.
    }

    public function pStmt_Declare(Stmt\Declare_ $node) {
        // TODO: Implement pStmt_Declare() method.
    }

    public function pStmt_DeclareDeclare(Stmt\DeclareDeclare $node) {
        // TODO: Implement pStmt_DeclareDeclare() method.
    }

    public function pStmt_If(Stmt\If_ $node) {
        // TODO: Implement pStmt_If() method.
    }

    public function pStmt_ElseIf(Stmt\ElseIf_ $node) {
        // TODO: Implement pStmt_ElseIf() method.
    }

    public function pStmt_Else(Stmt\Else_ $node) {
        // TODO: Implement pStmt_Else() method.
    }

    public function pStmt_For(Stmt\For_ $node) {
        // TODO: Implement pStmt_For() method.
    }

    public function pStmt_Foreach(Stmt\Foreach_ $node) {
        // TODO: Implement pStmt_Foreach() method.
    }

    public function pStmt_While(Stmt\While_ $node) {
        // TODO: Implement pStmt_While() method.
    }

    public function pStmt_Do(Stmt\Do_ $node) {
        // TODO: Implement pStmt_Do() method.
    }

    public function pStmt_Switch(Stmt\Switch_ $node) {
        // TODO: Implement pStmt_Switch() method.
    }

    public function pStmt_Case(Stmt\Case_ $node) {
        // TODO: Implement pStmt_Case() method.
    }

    public function pStmt_TryCatch(Stmt\TryCatch $node) {
        // TODO: Implement pStmt_TryCatch() method.
    }

    public function pStmt_Catch(Stmt\Catch_ $node) {
        // TODO: Implement pStmt_Catch() method.
    }

    public function pStmt_Break(Stmt\Break_ $node) {
        // TODO: Implement pStmt_Break() method.
    }

    public function pStmt_Continue(Stmt\Continue_ $node) {
        // TODO: Implement pStmt_Continue() method.
    }

    public function pStmt_Return(Stmt\Return_ $node) {
        // TODO: Implement pStmt_Return() method.
    }

    public function pStmt_Throw(Stmt\Throw_ $node) {
        // TODO: Implement pStmt_Throw() method.
    }

    public function pStmt_Label(Stmt\Label $node) {
        // TODO: Implement pStmt_Label() method.
    }

    public function pStmt_Goto(Stmt\Goto_ $node) {
        // TODO: Implement pStmt_Goto() method.
    }

    public function pStmt_Echo(Stmt\Echo_ $node) {
        // TODO: Implement pStmt_Echo() method.
    }

    public function pStmt_Static(Stmt\Static_ $node) {
        // TODO: Implement pStmt_Static() method.
    }

    public function pStmt_Global(Stmt\Global_ $node) {
        // TODO: Implement pStmt_Global() method.
    }

    public function pStmt_StaticVar(Stmt\StaticVar $node) {
        // TODO: Implement pStmt_StaticVar() method.
    }

    public function pStmt_Unset(Stmt\Unset_ $node) {
        // TODO: Implement pStmt_Unset() method.
    }

    public function pStmt_InlineHTML(Stmt\InlineHTML $node) {
        // TODO: Implement pStmt_InlineHTML() method.
    }

    public function pStmt_HaltCompiler(Stmt\HaltCompiler $node) {
        // TODO: Implement pStmt_HaltCompiler() method.
    }

    public function pStmt_Nop(Stmt\Nop $node) {
        // TODO: Implement pStmt_Nop() method.
    }

    public function pType($node) {
        // TODO: Implement pType() method.
    }

    public function pClassCommon(Stmt\Class_ $node, $afterClassToken) {
        // TODO: Implement pClassCommon() method.
    }

    public function pObjectProperty($node) {
        // TODO: Implement pObjectProperty() method.
    }

    public function pModifiers($modifiers) {
        // TODO: Implement pModifiers() method.
    }

    public function pEncapsList(array $encapsList, $quote) {
        // TODO: Implement pEncapsList() method.
    }

    public function pDereferenceLhs(Node $node) {
        // TODO: Implement pDereferenceLhs() method.
    }

    public function pCallLhs(Node $node) {
        // TODO: Implement pCallLhs() method.
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


    /**
     * @param null $atStart
     * @return NonPrivate
     */
    public function pushDelay($atStart=null) {
        call_user_func_array(array($this->writer,__FUNCTION__),func_get_args());
        return $this;
    }

    /**
     * @param null $id
     * @return NonPrivate
     */
    public function popDelay(&$id = null) {
        $this->writer->popDelay($id);
        return $this;
    }

    /**
     * @param $var
     * @return $this
     */
    public function popDelayToVar(&$var){
        $this->writer->popDelayToVar($var);
        return $this;
    }

    /**
     * @param $id
     * @return NonPrivate
     */
    public function writeDelay($id) {
        call_user_func_array(array($this->writer,__FUNCTION__),func_get_args());
        return $this;
    }

    /**
     * @return NonPrivate
     */
    public function writeLastDelay() {
        call_user_func_array(array($this->writer,__FUNCTION__),func_get_args());
        return $this;
    }

    /**
     * @param $string
     * @param ... $objects
     * @return NonPrivate
     */
    public function println($string = '', $objects = null) {
        call_user_func_array(array($this->writer,__FUNCTION__),func_get_args());
        return $this;
    }

    /**
     * @param $string
     * @param ... $objects
     * @return NonPrivate
     */
    public function print_($string, $objects = null) {
        call_user_func_array(array($this->writer,__FUNCTION__),func_get_args());
        return $this;
    }

    /**
     * @return NonPrivate
     */
    public function indent() {
        call_user_func_array(array($this->writer,__FUNCTION__),func_get_args());
        return $this;
    }

    /**
     * @return NonPrivate
     */
    public function outdent() {
        call_user_func_array(array($this->writer,__FUNCTION__),func_get_args());
        return $this;
    }

    /**
     * @param $string
     * @param ... $objects
     * @return NonPrivate
     */
    public function indentln($string, $objects = null) {
        call_user_func_array(array($this->writer,__FUNCTION__),func_get_args());
        return $this;
    }

    private function pushLoop($atStart) {
        $this->closureHelper->pushLoop();
        $this->pushDelay($atStart);
    }

    private function popLoopPrintName(&$body){
        $this->popDelayToVar($body);
        $this->closureHelper->popLoop($loopName);
        if ($loopName!==null){
            $this->print_($loopName.":");
        }
    }

    protected function printVarDef(){
        $vars = $this->closureHelper->getVarsDef();
        if (count($vars)){
            $this->println('var '.join(',',$vars).';');
        }
    }
}