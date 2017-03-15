<?php namespace SuperClosure\Analyzer;

use SuperClosure\Exception\ClosureAnalysisException;

/**
 * This is the token based analyzer.
 *
 * We're using Uses reflection and tokenization to analyze a closure and
 * determine its code and context. This is much faster than the AST based
 * implementation.
 */
class TokenAnalyzer extends ClosureAnalyzer
{
    public function determineCode(array &$data)
    {
        $this->determineTokens($data);
        $data['code'] = implode('', $data['tokens']);
        $data['hasThis'] = (strpos($data['code'], '$this') !== false);
    }

    private function determineTokens(array &$data)
    {
        $potential = $this->determinePotentialTokens($data['reflection']);
        $braceLevel = $index = $step = $insideUse = 0;
        $data['tokens'] = $data['context'] = [];

        foreach ($potential as $token) {
            $token = new Token($token);
            switch ($step) {
                // Handle tokens before the function declaration.
                case 0:
                    if ($token->is(T_FUNCTION)) {
                        $data['tokens'][] = $token;
                        $step++;
                    }
                    break;
                // Handle tokens inside the function signature.
                case 1:
                    $data['tokens'][] = $token;
                    if ($insideUse) {
                        if ($token->is(T_VARIABLE)) {
                            $varName = trim($token, '$ ');
                            $data['context'][$varName] = null;
                        } elseif ($token->is('&')) {
                            $data['hasRefs'] = true;
                        }
                    } elseif ($token->is(T_USE)) {
                        $insideUse++;
                    }
                    if ($token->is('{')) {
                        $step++;
                        $braceLevel++;
                    }
                    break;
                // Handle tokens inside the function body.
                case 2:
                    $data['tokens'][] = $token;
                    if ($token->is('{')) {
                        $braceLevel++;
                    } elseif ($token->is('}')) {
                        $braceLevel--;
                        if ($braceLevel === 0) {
                            $step++;
                        }
                    }
                    break;
                // Handle tokens after the function declaration.
                case 3:
                    if ($token->is(T_FUNCTION)) {
                        throw new ClosureAnalysisException('Multiple closures '
                            . 'were declared on the same line of code. Could not '
                            . 'determine which closure was the intended target.'
                        );
                    }
                    break;
            }
        }
    }

    private function determinePotentialTokens(\ReflectionFunction $reflection)
    {
        // Load the file containing the code for the function.
        $fileName = $reflection->getFileName();
        if (!is_readable($fileName)) {
            throw new ClosureAnalysisException(
                "Cannot read the file containing the closure: \"{$fileName}\"."
            );
        }

        $code = '';
        $file = new \SplFileObject($fileName);
        $file->seek($reflection->getStartLine() - 1);
        while ($file->key() < $reflection->getEndLine()) {
            $code .= $file->current();
            $file->next();
        }

        $code = trim($code);
        if (strpos($code, '<?php') !== 0) {
            $code = "<?php\n" . $code;
        }

        return token_get_all($code);
    }

    protected function determineContext(array &$data)
    {
        // Get the values of the variables that are closed upon in "use".
        $values = $data['reflection']->getStaticVariables();

        // Construct the context by combining the variable names and values.
        foreach ($data['context'] as $name => &$value) {
            if (isset($values[$name])) {
                $value = $values[$name];
            }
        }
    }
}
