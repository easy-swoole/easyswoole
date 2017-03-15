<?php namespace SuperClosure\Analyzer\Visitor;

use SuperClosure\Exception\ClosureAnalysisException;
use PhpParser\Node\Stmt\Namespace_ as NamespaceNode;
use PhpParser\Node\Stmt\Trait_ as TraitNode;
use PhpParser\Node\Stmt\Class_ as ClassNode;
use PhpParser\Node\Expr\Closure as ClosureNode;
use PhpParser\Node as AstNode;
use PhpParser\NodeVisitorAbstract as NodeVisitor;

/**
 * This is a visitor that extends the nikic/php-parser library and looks for a
 * closure node and its location.
 *
 * @internal
 */
final class ClosureLocatorVisitor extends NodeVisitor
{
    /**
     * @var \ReflectionFunction
     */
    private $reflection;

    /**
     * @var ClosureNode
     */
    public $closureNode;

    /**
     * @var array
     */
    public $location;

    /**
     * @param \ReflectionFunction $reflection
     */
    public function __construct($reflection)
    {
        $this->reflection = $reflection;
        $this->location = [
            'class'     => null,
            'directory' => dirname($this->reflection->getFileName()),
            'file'      => $this->reflection->getFileName(),
            'function'  => $this->reflection->getName(),
            'line'      => $this->reflection->getStartLine(),
            'method'    => null,
            'namespace' => null,
            'trait'     => null,
        ];
    }

    public function enterNode(AstNode $node)
    {
        // Determine information about the closure's location
        if (!$this->closureNode) {
            if ($node instanceof NamespaceNode) {
                $namespace = ($node->name && is_array($node->name->parts))
                    ? implode('\\', $node->name->parts)
                    : null;
                $this->location['namespace'] = $namespace;
            }
            if ($node instanceof TraitNode) {
                $this->location['trait'] = $node->name;
                $this->location['class'] = null;
            } elseif ($node instanceof ClassNode) {
                $this->location['class'] = $node->name;
                $this->location['trait'] = null;
            }
        }

        // Locate the node of the closure
        if ($node instanceof ClosureNode) {
            if ($node->getAttribute('startLine') == $this->location['line']) {
                if ($this->closureNode) {
                    $line = $this->location['file'] . ':' . $node->getAttribute('startLine');
                    throw new ClosureAnalysisException("Two closures were "
                        . "declared on the same line ({$line}) of code. Cannot "
                        . "determine which closure was the intended target.");
                } else {
                    $this->closureNode = $node;
                }
            }
        }
    }

    public function leaveNode(AstNode $node)
    {
        // Determine information about the closure's location
        if (!$this->closureNode) {
            if ($node instanceof NamespaceNode) {
                $this->location['namespace'] = null;
            }
            if ($node instanceof TraitNode) {
                $this->location['trait'] = null;
            } elseif ($node instanceof ClassNode) {
                $this->location['class'] = null;
            }
        }
    }

    public function afterTraverse(array $nodes)
    {
        if ($this->location['class']) {
            $this->location['class'] = $this->location['namespace'] . '\\' . $this->location['class'];
            $this->location['method'] = "{$this->location['class']}::{$this->location['function']}";
        } elseif ($this->location['trait']) {
            $this->location['trait'] = $this->location['namespace'] . '\\' . $this->location['trait'];
            $this->location['method'] = "{$this->location['trait']}::{$this->location['function']}";

            // If the closure was declared in a trait, then we will do a best
            // effort guess on the name of the class that used the trait. It's
            // actually impossible at this point to know for sure what it is.
            if ($closureScope = $this->reflection->getClosureScopeClass()) {
                $this->location['class'] = $closureScope ? $closureScope->getName() : null;
            } elseif ($closureThis = $this->reflection->getClosureThis()) {
                $this->location['class'] = get_class($closureThis);
            }
        }
    }
}
