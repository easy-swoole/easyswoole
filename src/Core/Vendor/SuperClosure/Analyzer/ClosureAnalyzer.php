<?php namespace SuperClosure\Analyzer;

use SuperClosure\Exception\ClosureAnalysisException;

abstract class ClosureAnalyzer
{
    /**
     * Analyzer a given closure.
     *
     * @param \Closure $closure
     *
     * @throws ClosureAnalysisException
     *
     * @return array
     */
    public function analyze(\Closure $closure)
    {
        $data = [
            'reflection' => new \ReflectionFunction($closure),
            'code'       => null,
            'hasThis'    => false,
            'context'    => [],
            'hasRefs'    => false,
            'binding'    => null,
            'scope'      => null,
            'isStatic'   => $this->isClosureStatic($closure),
        ];

        $this->determineCode($data);
        $this->determineContext($data);
        $this->determineBinding($data);

        return $data;
    }

    abstract protected function determineCode(array &$data);

    /**
     * Returns the variables that are in the "use" clause of the closure.
     *
     * These variables are referred to as the "used variables", "static
     * variables", "closed upon variables", or "context" of the closure.
     *
     * @param array $data
     */
    abstract protected function determineContext(array &$data);

    private function determineBinding(array &$data)
    {
        $data['binding'] = $data['reflection']->getClosureThis();
        if ($scope = $data['reflection']->getClosureScopeClass()) {
            $data['scope'] = $scope->getName();
        }
    }

    private function isClosureStatic(\Closure $closure)
    {
        $closure = @$closure->bindTo(new \stdClass);

        if ($closure === null) {
            return true;
        }

        $rebound = new \ReflectionFunction($closure);

        return $rebound->getClosureThis() === null;
    }
}
