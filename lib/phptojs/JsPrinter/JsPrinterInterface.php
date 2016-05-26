<?php
/**
 * Created by PhpStorm.
 * User: Jozef Môstka
 * Date: 24.5.2016
 * Time: 19:20
 */
namespace phptojs\JsPrinter;

use PhpParser\Comment;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\AssignOp;
use PhpParser\Node\Expr\BinaryOp;
use PhpParser\Node\Expr\Cast;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar;
use PhpParser\Node\Scalar\MagicConst;
use PhpParser\Node\Stmt;

interface JsPrinterInterface {



    public function pParam(Node\Param $node);

    public function pArg(Node\Arg $node);

    public function pConst(Node\Const_ $node);

    public function pName(Name $node);

    public function pName_FullyQualified(Name\FullyQualified $node);

    public function pName_Relative(Name\Relative $node);

    public function pScalar_MagicConst_Class(MagicConst\Class_ $node);

    public function pScalar_MagicConst_Dir(MagicConst\Dir $node);

    public function pScalar_MagicConst_File(MagicConst\File $node);

    public function pScalar_MagicConst_Function(MagicConst\Function_ $node);

    public function pScalar_MagicConst_Line(MagicConst\Line $node);

    public function pScalar_MagicConst_Method(MagicConst\Method $node);

    public function pScalar_MagicConst_Namespace(MagicConst\Namespace_ $node);

    public function pScalar_MagicConst_Trait(MagicConst\Trait_ $node);

    public function pScalar_String(Scalar\String_ $node);

    public function pScalar_Encapsed(Scalar\Encapsed $node);

    public function pScalar_LNumber(Scalar\LNumber $node);

    public function pScalar_DNumber(Scalar\DNumber $node);

    public function pExpr_Assign(Expr\Assign $node);

    public function pExpr_AssignRef(Expr\AssignRef $node);

    public function pExpr_AssignOp_Plus(AssignOp\Plus $node);

    public function pExpr_AssignOp_Minus(AssignOp\Minus $node);

    public function pExpr_AssignOp_Mul(AssignOp\Mul $node);

    public function pExpr_AssignOp_Div(AssignOp\Div $node);

    public function pExpr_AssignOp_Concat(AssignOp\Concat $node);

    public function pExpr_AssignOp_Mod(AssignOp\Mod $node);

    public function pExpr_AssignOp_BitwiseAnd(AssignOp\BitwiseAnd $node);

    public function pExpr_AssignOp_BitwiseOr(AssignOp\BitwiseOr $node);

    public function pExpr_AssignOp_BitwiseXor(AssignOp\BitwiseXor $node);

    public function pExpr_AssignOp_ShiftLeft(AssignOp\ShiftLeft $node);

    public function pExpr_AssignOp_ShiftRight(AssignOp\ShiftRight $node);

    public function pExpr_AssignOp_Pow(AssignOp\Pow $node);

    public function pExpr_BinaryOp_Plus(BinaryOp\Plus $node);

    public function pExpr_BinaryOp_Minus(BinaryOp\Minus $node);

    public function pExpr_BinaryOp_Mul(BinaryOp\Mul $node);

    public function pExpr_BinaryOp_Div(BinaryOp\Div $node);

    public function pExpr_BinaryOp_Concat(BinaryOp\Concat $node);

    public function pExpr_BinaryOp_Mod(BinaryOp\Mod $node);

    public function pExpr_BinaryOp_BooleanAnd(BinaryOp\BooleanAnd $node);

    public function pExpr_BinaryOp_BooleanOr(BinaryOp\BooleanOr $node);

    public function pExpr_BinaryOp_BitwiseAnd(BinaryOp\BitwiseAnd $node);

    public function pExpr_BinaryOp_BitwiseOr(BinaryOp\BitwiseOr $node);

    public function pExpr_BinaryOp_BitwiseXor(BinaryOp\BitwiseXor $node);

    public function pExpr_BinaryOp_ShiftLeft(BinaryOp\ShiftLeft $node);

    public function pExpr_BinaryOp_ShiftRight(BinaryOp\ShiftRight $node);

    public function pExpr_BinaryOp_Pow(BinaryOp\Pow $node);

    public function pExpr_BinaryOp_LogicalAnd(BinaryOp\LogicalAnd $node);

    public function pExpr_BinaryOp_LogicalOr(BinaryOp\LogicalOr $node);

    public function pExpr_BinaryOp_LogicalXor(BinaryOp\LogicalXor $node);

    public function pExpr_BinaryOp_Equal(BinaryOp\Equal $node);

    public function pExpr_BinaryOp_NotEqual(BinaryOp\NotEqual $node);

    public function pExpr_BinaryOp_Identical(BinaryOp\Identical $node);

    public function pExpr_BinaryOp_NotIdentical(BinaryOp\NotIdentical $node);

    public function pExpr_BinaryOp_Spaceship(BinaryOp\Spaceship $node);

    public function pExpr_BinaryOp_Greater(BinaryOp\Greater $node);

    public function pExpr_BinaryOp_GreaterOrEqual(BinaryOp\GreaterOrEqual $node);

    public function pExpr_BinaryOp_Smaller(BinaryOp\Smaller $node);

    public function pExpr_BinaryOp_SmallerOrEqual(BinaryOp\SmallerOrEqual $node);

    public function pExpr_BinaryOp_Coalesce(BinaryOp\Coalesce $node);

    public function pExpr_Instanceof(Expr\Instanceof_ $node);

    public function pExpr_BooleanNot(Expr\BooleanNot $node);

    public function pExpr_BitwiseNot(Expr\BitwiseNot $node);

    public function pExpr_UnaryMinus(Expr\UnaryMinus $node);

    public function pExpr_UnaryPlus(Expr\UnaryPlus $node);

    public function pExpr_PreInc(Expr\PreInc $node);

    public function pExpr_PreDec(Expr\PreDec $node);

    public function pExpr_PostInc(Expr\PostInc $node);

    public function pExpr_PostDec(Expr\PostDec $node);

    public function pExpr_ErrorSuppress(Expr\ErrorSuppress $node);

    public function pExpr_YieldFrom(Expr\YieldFrom $node);

    public function pExpr_Print(Expr\Print_ $node);

    public function pExpr_Cast_Int(Cast\Int_ $node);

    public function pExpr_Cast_Double(Cast\Double $node);

    public function pExpr_Cast_String(Cast\String_ $node);

    public function pExpr_Cast_Array(Cast\Array_ $node);

    public function pExpr_Cast_Object(Cast\Object_ $node);

    public function pExpr_Cast_Bool(Cast\Bool_ $node);

    public function pExpr_Cast_Unset(Cast\Unset_ $node);

    public function pExpr_FuncCall(Expr\FuncCall $node);

    public function pExpr_MethodCall(Expr\MethodCall $node);

    public function pExpr_StaticCall(Expr\StaticCall $node);

    public function pExpr_Empty(Expr\Empty_ $node);

    public function pExpr_Isset(Expr\Isset_ $node);

    public function pExpr_Eval(Expr\Eval_ $node);

    public function pExpr_Include(Expr\Include_ $node);

    public function pExpr_List(Expr\List_ $node);

    public function pExpr_Variable(Expr\Variable $node);

    public function pExpr_Array(Expr\Array_ $node);

    public function pExpr_ArrayItem(Expr\ArrayItem $node);

    public function pExpr_ArrayDimFetch(Expr\ArrayDimFetch $node);

    public function pExpr_ConstFetch(Expr\ConstFetch $node);

    public function pExpr_ClassConstFetch(Expr\ClassConstFetch $node);

    public function pExpr_PropertyFetch(Expr\PropertyFetch $node);

    public function pExpr_StaticPropertyFetch(Expr\StaticPropertyFetch $node);

    public function pExpr_ShellExec(Expr\ShellExec $node);

    public function pExpr_Closure(Expr\Closure $node);

    public function pExpr_ClosureUse(Expr\ClosureUse $node);

    public function pExpr_New(Expr\New_ $node);

    public function pExpr_Clone(Expr\Clone_ $node);

    public function pExpr_Ternary(Expr\Ternary $node);

    public function pExpr_Exit(Expr\Exit_ $node);

    public function pExpr_Yield(Expr\Yield_ $node);

    public function pStmt_Namespace(Stmt\Namespace_ $node);

    public function pStmt_Use(Stmt\Use_ $node);

    public function pStmt_GroupUse(Stmt\GroupUse $node);

    public function pStmt_UseUse(Stmt\UseUse $node);

    public function pUseType($type);

    public function pStmt_Interface(Stmt\Interface_ $node);

    public function pStmt_Class(Stmt\Class_ $node);

    public function pStmt_Trait(Stmt\Trait_ $node);

    public function pStmt_TraitUse(Stmt\TraitUse $node);

    public function pStmt_TraitUseAdaptation_Precedence(Stmt\TraitUseAdaptation\Precedence $node);

    public function pStmt_TraitUseAdaptation_Alias(Stmt\TraitUseAdaptation\Alias $node);

    public function pStmt_Property(Stmt\Property $node);

    public function pStmt_PropertyProperty(Stmt\PropertyProperty $node);

    public function pStmt_ClassMethod(Stmt\ClassMethod $node);

    public function pStmt_ClassConst(Stmt\ClassConst $node);

    public function pStmt_Function(Stmt\Function_ $node);

    public function pStmt_Const(Stmt\Const_ $node);

    public function pStmt_Declare(Stmt\Declare_ $node);

    public function pStmt_DeclareDeclare(Stmt\DeclareDeclare $node);

    public function pStmt_If(Stmt\If_ $node);

    public function pStmt_ElseIf(Stmt\ElseIf_ $node);

    public function pStmt_Else(Stmt\Else_ $node);

    public function pStmt_For(Stmt\For_ $node);

    public function pStmt_Foreach(Stmt\Foreach_ $node);

    public function pStmt_While(Stmt\While_ $node);

    public function pStmt_Do(Stmt\Do_ $node);

    public function pStmt_Switch(Stmt\Switch_ $node);

    public function pStmt_Case(Stmt\Case_ $node);

    public function pStmt_TryCatch(Stmt\TryCatch $node);

    public function pStmt_Catch(Stmt\Catch_ $node, &$catchesVars);

    public function pStmt_Break(Stmt\Break_ $node);

    public function pStmt_Continue(Stmt\Continue_ $node);

    public function pStmt_Return(Stmt\Return_ $node);

    public function pStmt_Throw(Stmt\Throw_ $node);

    public function pStmt_Label(Stmt\Label $node);

    public function pStmt_Goto(Stmt\Goto_ $node);

    public function pStmt_Echo(Stmt\Echo_ $node);

    public function pStmt_Static(Stmt\Static_ $node);

    public function pStmt_Global(Stmt\Global_ $node);

    public function pStmt_StaticVar(Stmt\StaticVar $node);

    public function pStmt_Unset(Stmt\Unset_ $node);

    public function pStmt_InlineHTML(Stmt\InlineHTML $node);

    public function pStmt_HaltCompiler(Stmt\HaltCompiler $node);

    public function pStmt_Nop(Stmt\Nop $node);

    public function pType($node);

    public function pClassCommon(Stmt\Class_ $node, $afterClassToken);

    public function pObjectProperty($node);

    public function pModifiers($modifiers);

    public function pEncapsList(array $encapsList, $quote);

    public function pDereferenceLhs(Node $node);

    public function pCallLhs(Node $node);
}