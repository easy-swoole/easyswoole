<?php namespace SuperClosure\Analyzer\Visitor;

use PhpParser\Node as AstNode;
use PhpParser\Node\Expr\Variable as VariableNode;
use PhpParser\NodeVisitorAbstract as NodeVisitor;

/**
 * Detects if the closure's AST contains a $this variable.
 *
 * @internal
 */
final class ThisDetectorVisitor extends NodeVisitor
{
    /**
     * @var bool
     */
    public $detected = false;

    public function leaveNode(AstNode $node)
    {
        if ($node instanceof VariableNode) {
            if ($node->name === 'this') {
                $this->detected = true;
            }
        }
    }
}
