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

    /**
     * @param Node\Param[] $params
     * @return string
     * @throws Error
     */
    public function pParamDefaultValues(array $params){
        foreach($params as $node){
            if (!$node instanceof Node\Param){
                throw new Error('this is not instanceof Node\Param but '.get_class($node));
            }
            if (!$node->default){
                continue;
            }
            $this->writer
                ->print_("if (typeof %{argX} == 'undefined') %{argX}=",$node->name,$node->name);
            $this->p($node->default);
            $this->writer
                ->println(";");
        }
        foreach($params as $node){
            if (!$node->type){
                continue;
            }
            $this->print_("if (!");
            if (is_string($node->type)){
                $this->print_("is%{Type}(%{argX})",ucfirst($node->type),$node->name);
            }else{
                $this->print_("%{argX} instanceof %{Class}",$node->name,$node->type);
            }
            $this->println(") throw new Error('bad param type');");
        }
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
        if ($node->getAttribute('kind') === Scalar\String_::KIND_HEREDOC) {
            $label = $node->getAttribute('docLabel');
            if ($label && !$this->encapsedContainsEndLabel($node->parts, $label)) {
                if (count($node->parts) === 1
                    && $node->parts[0] instanceof Scalar\EncapsedStringPart
                    && $node->parts[0]->value === ''
                ) {
                    $str = $this->pNoIndent("<<<$label\n$label") . $this->docStringEndToken;
                    $this->print_($str);
                    return;
                }
                self::notImplemented(true,"encapsed strig with <<<");
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

    public function pExpr_ShellExec(Expr\ShellExec $node) {//TODO: implement this
        self::notImplemented(true,"shell exec",true);
    }

    private $useByRef=null;
    private $useByRedStack=array();

    protected function printUseByRefDef(){
        if ($this->useByRef!==null){
            $useByRef = $this->useByRef;
            $this->useByRef=null;
            $this->println($useByRef);
        }
    }


    public function pExpr_Closure(Expr\Closure $node) {//TODO: implement this
        self::notImplemented($node->byRef,"closure reference by &");
        if ($node->static){
            self::WTF();
            $this->print_('static');
        }
        $this->closureHelper->pushVarScope();
        $useByRef = array();
        if (!empty($node->uses)){
            $useByRef2=array();
            foreach($node->uses as $use){
                $this->closureHelper->useVar($use->var);
                if (!$use->byRef){
                    $useByRef2[]=$use->var.'_='.$use->var;
                    $useByRef[]=$use->var.'='.$use->var.'_';
                }
            }
            if (count($useByRef2)){
                $this->useByRedStack[] = 'var '.join(',',$useByRef2).';';
            }
        }

        $this->print_('function(');
        $this->closureHelper->isDefScope(true);
        $this->pCommaSeparated($node->params);
        $this->closureHelper->isDefScope(false);
        $this->print_(")");
        $this->println("{")
            ->indent();
        if (count($useByRef)){
            $this->println('var '.join(',',$useByRef).';');
        }

        $this->pushDelay(true);
        $this->pStmts($node->stmts);
        $this->popDelayToVar($body);

        $this->printVarDef();
        $this->print_($body);

        $this->outdent()
            ->println("}");
        if ($this->useByRef!==null){
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
        $this->p($node->class);
        $this->print_('(');
        $this->pCommaSeparated($node->args);
        $this->print_(')');
    }

    public function pExpr_Clone(Expr\Clone_ $node) {//TODO: implement this
        self::notImplemented(true,"cloning by clone");
    }

    public function pExpr_Ternary(Expr\Ternary $node) {//TODO: implement this
        // a bit of cheating: we treat the ternary as a binary op where the ?...: part is the operator.
        // this is okay because the part between ? and : never needs parentheses.
        $this->pushDelay();
        $this->print_("?");
        if ($node->if!==null){
            $this->p($node->if);
        }
        $this->print_(":");
        $this->popDelay($delayId);
        $this->pInfixOp('Expr_Ternary',$node->cond,$delayId,$node->else);
        /*$this->pInfixOp('Expr_Ternary',
            $node->cond, ' ?' . (null !== $node->if ? ' ' . $this->p($node->if) . ' ' : '') . ': ', $node->else
        );*/
    }

    public function pExpr_Exit(Expr\Exit_ $node) {
        $this->print_("throw new Exit(");
        if ($node->expr!==null){
            $this->p($node->expr);
        }
        $this->println(");");
    }

    public function pExpr_Yield(Expr\Yield_ $node) {//TODO: implement this
        self::notImplemented(true,"using yield",true);
    }

    public function pStmt_Namespace(Stmt\Namespace_ $node) {//TODO: implement this
        if ($node->name!==null){
            $this->closureHelper->setNamespace(true);
            $this->print_("N._INIT_('");
            $this->p($node->name);
            $this->println("');")
                ->println("(function(){");
            $this->closureHelper->pushVarScope();
            $this->pStmts($node->stmts);
            $this->closureHelper->popVarScope();
            $this->print_("}).call(N.");
            $this->p($node->name);
            $this->println(");");
            $this->closureHelper->setNamespace(false);
        }else{
            $this->pStmts($node->stmts);
        }
    }

    public function pStmt_Use(Stmt\Use_ $node) {//TODO: implement this
        foreach($node->uses as $node) {
            $this->print_("var %{varName} = N.", $node->alias ? $node->alias : $node->name->getLast());
            $this->p($node->name);
            $this->println(";");
        }
    }

    public function pStmt_GroupUse(Stmt\GroupUse $node) {//TODO:
        self::notImplemented(true,__METHOD__);
    }

    public function pStmt_UseUse(Stmt\UseUse $node) {//TODO::
        self::notImplemented(true,__METHOD__);
    }

    public function pUseType($type) {//TODO:
        self::notImplemented(true,__METHOD__);
    }

    public function pStmt_Interface(Stmt\Interface_ $node) {
        $classWrapper = new Stmt\Class_($node->name,
            array(
                'type'       => $node->getType(),
                'name'       => $node->name,
                'extends'    => null,
                'implements' => $node->extends,
                'stmts'      => $node->stmts,
            ),$node->getAttributes());
        $this->closureHelper->setNextClassIsInterface();

        $this->pStmt_Class($classWrapper);
    }

    public function pStmt_Class(Stmt\Class_ $node) {//TODO: implement this
        //self::notImplemented($node->extends,'extending class');
        //self::notImplemented($node->implements,'implementng class');
        //. (null !== $node->extends ? ' extends ' . $this->p($node->extends) : '')
        //. (!empty($node->implements) ? ' implements ' . $this->pCommaSeparated($node->implements) : '')
        //$this->indent();
        $this->closureHelper->pushClass();

        $this->pushDelay()->indent();
        $this->println("function %{ClassName}(%{Arguments}){",$node->name,'/*constructor arguments*/')
            ->indent();
        if ($node->extends){
            $this->println("parent.call(this %{Arguments});",'/*constructor arguments*/');
        }
        if ($this->closureHelper->classIsInterface()){
            $this->println('__INTERFACE_NEW__();');
        }
        $this->closureHelper->pushVarScope();
        $this->pStmts($node->stmts);
        $this->closureHelper->popVarScope();
        $this->outdent()
            ->println("}")
            ->outdent()
            ->popDelay($classBody);
        $this->pushDelay()->indent();
        if ($node->type & Stmt\Class_::MODIFIER_ABSTRACT){
            $this->println('%{ClassName}.prototype.__isAbstract__=true;',$node->name);
        }
        foreach($this->closureHelper->getClassStaticProperties() as $property){/** @var Stmt\PropertyProperty $property */
            //if ($node->type & Stmt\Class_::MODIFIER_STATIC){ TODO: implement private static property
            $this->print_("%{ClassName}.",$node->name);
            $this->p($property);
            $this->println(";");
            //}
        }
        foreach($this->closureHelper->getClassMethods() as $method){/** @var Stmt\ClassMethod $method */
            $this->print_("%{ClassName}.%{prototype}",$node->name,$method->type & Stmt\Class_::MODIFIER_STATIC?"":"prototype.");
            $this->pStmt_ClassMethod($method,true);
        }
        foreach($this->closureHelper->getClassConstants() as $consts){
            /** @var Stmt\ClassConst $consts */
            foreach($consts as $cons){
                $this->print_("%{ClassName}.",$node->name);
                $this->pConst($cons);
                $this->println(";");
            }

        }
        $this->println("return %{ClassName};",$node->name);
        $this->outdent()
            ->popDelay($methodsAndOthers);
        $this
            ->println("var %{Class} = %{useNamSPC}(function (%{useParent}){",
                $node->name,
                $this->closureHelper->isNamespace()?"this.{$node->name} = ":'',
                $node->extends?'parent':'');
        if ($node->extends || $node->implements){
            $this->indent()
                ->print_("__extends(%{ClassName}, %{parent}",$node->name,$node->extends?'parent':'null');
            if ($node->implements){
                $this->print_(',arguments[1]');
            }
            $this->println(');')
                ->outdent();
        }
        $this->writeDelay($classBody);
        $this->writeDelay($methodsAndOthers);

        $this->print_("})(");
        if ($node->extends || $node->implements){
            $this->print_("%{extend}",$node->extends?$node->extends:'null');
            if ($node->implements){
                $this->print_(',[');
                $this->pCommaSeparated($node->implements);
                $this->print_("]");
            }
        }
        $this->println(");");

        $this->closureHelper->popClass();
        //$this->outdent();
    }

    public function pStmt_Trait(Stmt\Trait_ $node) {//TODO: implement this
        self::notImplemented(true,"tait",true);
    }

    public function pStmt_TraitUse(Stmt\TraitUse $node) {//TODO: implement this
        self::notImplemented(true,"use tait",true);
    }

    public function pStmt_TraitUseAdaptation_Precedence(Stmt\TraitUseAdaptation\Precedence $node) {//TODO: implement this
        self::notImplemented(true,"pStmt_TraitUseAdaptation_Precedence",true);
    }

    public function pStmt_TraitUseAdaptation_Alias(Stmt\TraitUseAdaptation\Alias $node) {//TODO: implement this
        self::notImplemented(true,"pStmt_TraitUseAdaptation_Alias",true);
    }

    public function pStmt_Property(Stmt\Property $node) {//TODO: implement this
        foreach($node->props as $property){
            if (!$property->default){
                $property->default = new \PhpParser\Node\Expr\ConstFetch(new \PhpParser\Node\Name('null',$property->getAttributes()),$property->getAttributes());
            }
        }
        if ($node->type & Stmt\Class_::MODIFIER_STATIC){
            foreach ($node->props as $prop) {
                $this->closureHelper->addClassStaticProperty($prop);
            }
            return;
        }
        self::notImplemented($node->type & Stmt\Class_::MODIFIER_PRIVATE, "private property");
        foreach($node->props as $property){
            $this->print_("this.");
            $this->p($property);
            $this->println(";");
        }
    }

    public function pStmt_PropertyProperty(Stmt\PropertyProperty $node) {//TODO: implement this
        $this->print_($node->name);
        if ($node->default!==null){
            $this->print_(" = ");
            $this->p($node->default);
        }
    }

    public function pStmt_ClassMethod(Stmt\ClassMethod $node, $force=false) {//TODO: implement this
        if ($force){
            $this->closureHelper->pushVarScope();

            self::notImplemented($node->byRef,'method return reference');
            //return //$this->pModifiers($node->type)
            $this->print_($node->name);
            $this->print_(" = function(");
            $this->pCommaSeparated($node->params);
            $this->println("){");
            if ($node->stmts!==null){
                $this->pParamDefaultValues($node->params);

                $this->pushDelay(true);
                $this->pStmts($node->stmts);
                $this->popDelayToVar($body);

                $this->printVarDef();
                $this->print_($body);
            }else{
                if ($this->closureHelper->classIsInterface()){
                    $this->println("__INTERFACE_FUNC__();");
                }else if($node->isAbstract()){
                    $this->println("__ABSTRACT_FUNC__();");
                }else{
                    self::WTF('where is body???');
                }
            };
            $this->println("};");
            $this->closureHelper->popVarScope();
        }else{
            $this->closureHelper->addClassMethod($node);
        }
    }

    public function pStmt_ClassConst(Stmt\ClassConst $node) {
        $this->closureHelper->addClassConstants($node);
    }

    public function pStmt_Function(Stmt\Function_ $node) {
        $this->closureHelper->pushVarScope();
        self::notImplemented($node->byRef,"function return reference by function &$node->name(...");
        if ($this->closureHelper->isNamespace()){
            $this->println("var %{name} = this.%{name} = function(",$node->name,$node->name);
        }else{
            $this->print_("function %{name}(",$node->name);
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
            ->println('}');
        $this->closureHelper->popVarScope();
    }

    public function pStmt_Const(Stmt\Const_ $node) {
        if ($this->closureHelper->isNamespace()){
            foreach($node->consts as $const){
                $this->print_("var %{varName} = ",$const->name);
                $this->p($const->value);
                $this->println(";this.%{varName}=%{varName};",$const->name,$const->name);
            }
        }else{
            foreach($node->consts as $const){
                $this->print_("window.%{varName} = ",$const->name);
                $this->p($const->value);
                $this->println(";");
            }
        }
    }

    public function pStmt_Declare(Stmt\Declare_ $node) {
        self::notImplemented(true,"declare()",true);
    }

    public function pStmt_DeclareDeclare(Stmt\DeclareDeclare $node) {
        self::notImplemented(true,"declare()",true);
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
        if ($node->else!==null){
            $this->p($node->else);
        }else{
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
        $this->indent();
        $this->pStmts($node->stmts);
        $this->outdent();
        $this->popDelayToVar($loopBody);

        $this->print_("for(");
        $this->pCommaSeparated($node->init);
        $this->print_("; ");
        $this->pCommaSeparated($node->cond);
        $this->print_("; ");
        $this->pCommaSeparated($node->loop);
        $this->println("){")
            ->print_($loopBody)
            ->println("}");
    }

    public function pStmt_Foreach(Stmt\Foreach_ $node) {
        self::notImplemented($node->byRef,"reference by & in foreach value");

        $this->pushDelay();     //expression
        $this->p($node->expr);
        $this->popDelayToVar($expression);

        if ($node->keyVar) {    //key name
            $this->pushDelay();
            $this->p($node->keyVar);
            $this->popDelayToVar($keyName);
        }else{
            $keyName = "_key_";
        }
        $this->pushDelay();     //value name
        $this->p($node->valueVar);
        $this->popDelayToVar($varName);

        $this->pushLoop(true);
//TODO: $this->pStmts($node->cases);
        $this->popLoopPrintName($loopBody);

        $this->println("for (%{key} in %{expr}){",$keyName,$expression)
            ->indent()
            ->println("%{varName} = %{expr}[%{key}]",$varName,$expression,$keyName)
            ->print_($loopBody)
            ->outdent();
    }

    public function pStmt_While(Stmt\While_ $node) {
        $this->pushLoop(true);
//TODO: $this->pStmts($node->cases);
        $this->popLoopPrintName($loopBody);

        $this->pushDelay();
        $this->p($node->cond);
        $this->popDelayToVar($cond);

        $this->println("while(%{cond}){",$cond)
            ->indent()
            ->print_($loopBody)
            ->outdent()
            ->println("}");
    }

    public function pStmt_Do(Stmt\Do_ $node) {
        $this->pushLoop(true);
//TODO: $this->pStmts($node->cases);
        $this->popLoopPrintName($loopBody);

        $this->pushDelay(false);
        $this->p($node->cond);
        $this->popDelayToVar($cond);

        $this->println("do {")
            ->indent()
            ->print_($loopBody)
            ->outdent()
            ->println("}while (%{cond});",$cond);
    }

    public function pStmt_Switch(Stmt\Switch_ $node) {
        $this->pushDelay();
        $this->p($node->cond);
        $this->popDelayToVar($cond);

        $this->pushLoop(true);
        $this->pStmts($node->cases);
        $this->popLoopPrintName($loopBody);

        $this->println("switch (%{cond}){",$cond)
            ->indent()
            ->print_($loopBody)
            ->outdent()
            ->println("}");
    }

    public function pStmt_Case(Stmt\Case_ $node) {
        if ($node->cond!==null){
            $this->print_("case ");
            $this->p($node->cond);
        }else{
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
        foreach($node->catches as $catch){
            $this->pushDelay();
            $this->pStmt_Catch($catch);
            $v=null;
            $this->popDelayToVar($v);
            $catches[]=$v;
        }
        $this->print_(join('else',$catches));
        $this->outdent();
        if ($node->finallyStmts!==null){
            $this->println("}finally{")
                ->indent();
            $this->pStmts($node->finallyStmts);
            $this->outdent();

        }
        $this->println("}");
    }

    public function pStmt_Catch(Stmt\Catch_ $node) {
        $this->pushDelay(false);
        $this->p($node->type);
        $this->popDelayToVar($type);
        $this->println("if (__e__ instanceof %{type}){",$type)
            ->indent()
            ->println("var %{varName}=__e__;",$node->var);
        $this->pStmts($node->stmts);
        $this->outdent()
            ->println("}");
    }

    public function pStmt_Break(Stmt\Break_ $node) {
        $name='';
        if ($node->num !== null){
            $name = ' '.$this->closureHelper->getLoopName($node->num);
        }
        $this->println('break %{name};',$name);
    }

    public function pStmt_Continue(Stmt\Continue_ $node) {
        $name='';
        if ($node->num !== null){
            $name = ' '.$this->closureHelper->getLoopName($node->num);
        }
        $this->println('continue %{name};',$name);
    }

    public function pStmt_Return(Stmt\Return_ $node) {
        $this->print_("return ");
        if ($node->expr!==null){
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
        self::notImplemented(true,"labels:");
    }

    public function pStmt_Goto(Stmt\Goto_ $node) {//TODO: implement this
        self::notImplemented(true,'goto.',true);
        //TODO: implement it. http://stackoverflow.com/questions/9751207/how-can-i-use-goto-in-javascript/23181432#23181432
    }

    public function pStmt_Echo(Stmt\Echo_ $node) {
        return 'document.write(' . $this->pCommaSeparated($node->exprs) . ');';
    }

    public function pStmt_Static(Stmt\Static_ $node) {//TODO: implement this
        self::notImplemented(true," static variables",true);
    }

    public function pStmt_Global(Stmt\Global_ $node) {//TODO: implement this
        self::notImplemented(true," global variables",true);
    }

    public function pStmt_StaticVar(Stmt\StaticVar $node) {//TODO: implement this
        self::notImplemented(true,'static vars',true);
    }

    public function pStmt_Unset(Stmt\Unset_ $node) {//TODO: implement this
        $this->print_("delete ");
        $this->pCommaSeparated($node->vars);
        $this->println(";");
    }

    public function pStmt_InlineHTML(Stmt\InlineHTML $node) {//TODO: implement this
        $this->p($node->value);
        //return JS_SCRIPT_END . $this->pNoIndent("\n" . $node->value) . JS_SCRIPT_BEGIN;
    }

    public function pStmt_HaltCompiler(Stmt\HaltCompiler $node) {//TODO: implement this
        self::notImplemented(true," __halt_compiler()",true);
    }

    public function pStmt_Nop(Stmt\Nop $node) {
        // TODO: Implement pStmt_Nop() method.
        self::notImplemented(true,__METHOD__);
    }

    public function pType($node) {
        // TODO: Implement pType() method.
        self::notImplemented(true,__METHOD__);
    }

    public function pClassCommon(Stmt\Class_ $node, $afterClassToken) {
        // TODO: Implement pClassCommon() method.
        self::notImplemented(true,__METHOD__);
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
                $str = str_replace(PHP_EOL,'\n\\'.PHP_EOL,$str);
                $this->print_($str);
                //$str .= addcslashes($element, "\n\r\t\f\v$" . $quote . "\\");
            } else {
                if ($element instanceof Scalar\EncapsedStringPart){
                    $this->print_("\\n\\\n");
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
        self::notImplemented(true,__METHOD__);
    }

    public function pCallLhs(Node $node) {
        // TODO: Implement pCallLhs() method.
        self::notImplemented(true,__METHOD__);
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