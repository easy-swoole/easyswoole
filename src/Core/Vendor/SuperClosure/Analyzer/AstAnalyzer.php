<?php namespace SuperClosure\Analyzer;

use SuperClosure\Analyzer\Visitor\ThisDetectorVisitor;
use SuperClosure\Exception\ClosureAnalysisException;
use SuperClosure\Analyzer\Visitor\ClosureLocatorVisitor;
use SuperClosure\Analyzer\Visitor\MagicConstantVisitor;
use PhpParser\NodeTraverser;
use PhpParser\PrettyPrinter\Standard as NodePrinter;
use PhpParser\Error as ParserError;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\Parser as CodeParser;
use PhpParser\ParserFactory;
use PhpParser\Lexer\Emulative as EmulativeLexer;

/**
 * This is the AST based analyzer.
 *
 * We're using reflection and AST-based code parser to analyze a closure and
 * determine its code and context using the nikic/php-parser library. The AST
 * based analyzer and has more capabilities than the token analyzer, but is,
 * unfortunately, about 25 times slower.
 */
class AstAnalyzer extends ClosureAnalyzer
{
    protected function determineCode(array &$data)
    {
        // Find the closure by traversing through a AST of the code.
        // Note: This also resolves class names to their FQCNs while traversing.
        $this->locateClosure($data);

        // Make a second pass through the AST, but only through the closure's
        // nodes, to resolve any magic constants to literal values.
        $traverser = new NodeTraverser;
        $traverser->addVisitor(new MagicConstantVisitor($data['location']));
        $traverser->addVisitor($thisDetector = new ThisDetectorVisitor);
        $data['ast'] = $traverser->traverse([$data['ast']])[0];
        $data['hasThis'] = $thisDetector->detected;

        // Bounce the updated AST down to a string representation of the code.
        $data['code'] = (new NodePrinter)->prettyPrint([$data['ast']]);
    }

    /**
     * Parses the closure's code and produces an abstract syntax tree (AST).
     *
     * @param array $data
     *
     * @throws ClosureAnalysisException if there is an issue finding the closure
     */
    private function locateClosure(array &$data)
    {
        try {
            $locator = new ClosureLocatorVisitor($data['reflection']);
            $fileAst = $this->getFileAst($data['reflection']);

            $fileTraverser = new NodeTraverser;
            $fileTraverser->addVisitor(new NameResolver);
            $fileTraverser->addVisitor($locator);
            $fileTraverser->traverse($fileAst);
        } catch (ParserError $e) {
            // @codeCoverageIgnoreStart
            throw new ClosureAnalysisException(
                'There was an error analyzing the closure code.', 0, $e
            );
            // @codeCoverageIgnoreEnd
        }

        $data['ast'] = $locator->closureNode;
        if (!$data['ast']) {
            // @codeCoverageIgnoreStart
            throw new ClosureAnalysisException(
                'The closure was not found within the abstract syntax tree.'
            );
            // @codeCoverageIgnoreEnd
        }

        $data['location'] = $locator->location;
    }

    /**
     * Returns the variables that in the "use" clause of the closure definition.
     * These are referred to as the "used variables", "static variables", or
     * "closed upon variables", "context" of the closure.
     *
     * @param array $data
     */
    protected function determineContext(array &$data)
    {
        // Get the variable names defined in the AST
        $refs = 0;
        $vars = array_map(function ($node) use (&$refs) {
            if ($node->byRef) {
                $refs++;
            }
            return $node->var;
        }, $data['ast']->uses);
        $data['hasRefs'] = ($refs > 0);

        // Get the variable names and values using reflection
        $values = $data['reflection']->getStaticVariables();

        // Combine the names and values to create the canonical context.
        foreach ($vars as $name) {
            if (isset($values[$name])) {
                $data['context'][$name] = $values[$name];
            }
        }
    }

    /**
     * @param \ReflectionFunction $reflection
     *
     * @throws ClosureAnalysisException
     *
     * @return \PhpParser\Node[]
     */
    private function getFileAst(\ReflectionFunction $reflection)
    {
        $fileName = $reflection->getFileName();
        if (!file_exists($fileName)) {
            throw new ClosureAnalysisException(
                "The file containing the closure, \"{$fileName}\" did not exist."
            );
        }


        return $this->getParser()->parse(file_get_contents($fileName));
    }

    /**
     * @return CodeParser
     */
    private function getParser()
    {
        if (class_exists('PhpParser\ParserFactory')) {
            return (new ParserFactory)->create(ParserFactory::PREFER_PHP7);
        }

        return new CodeParser(new EmulativeLexer);
    }
}
