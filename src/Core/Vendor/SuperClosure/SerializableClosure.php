<?php namespace SuperClosure;

use Closure;
use SuperClosure\Exception\ClosureUnserializationException;

/**
 * This class acts as a wrapper for a closure, and allows it to be serialized.
 *
 * With the combined power of the Reflection API, code parsing, and the infamous
 * `eval()` function, you can serialize a closure, unserialize it somewhere
 * else (even a different PHP process), and execute it.
 */
class SerializableClosure implements \Serializable
{
    /**
     * The closure being wrapped for serialization.
     *
     * @var Closure
     */
    private $closure;

    /**
     * The serializer doing the serialization work.
     *
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * The data from unserialization.
     *
     * @var array
     */
    private $data;

    /**
     * Create a new serializable closure instance.
     *
     * @param Closure                  $closure
     * @param SerializerInterface|null $serializer
     */
    public function __construct(
        \Closure $closure,
        SerializerInterface $serializer = null
    ) {
        $this->closure = $closure;
        $this->serializer = $serializer ?: new Serializer;
    }

    /**
     * Return the original closure object.
     *
     * @return Closure
     */
    public function getClosure()
    {
        return $this->closure;
    }

    /**
     * Delegates the closure invocation to the actual closure object.
     *
     * Important Notes:
     *
     * - `ReflectionFunction::invokeArgs()` should not be used here, because it
     *   does not work with closure bindings.
     * - Args passed-by-reference lose their references when proxied through
     *   `__invoke()`. This is an unfortunate, but understandable, limitation
     *   of PHP that will probably never change.
     *
     * @return mixed
     */
    public function __invoke()
    {
        return call_user_func_array($this->closure, func_get_args());
    }

    /**
     * Clones the SerializableClosure with a new bound object and class scope.
     *
     * The method is essentially a wrapped proxy to the Closure::bindTo method.
     *
     * @param mixed $newthis  The object to which the closure should be bound,
     *                        or NULL for the closure to be unbound.
     * @param mixed $newscope The class scope to which the closure is to be
     *                        associated, or 'static' to keep the current one.
     *                        If an object is given, the type of the object will
     *                        be used instead. This determines the visibility of
     *                        protected and private methods of the bound object.
     *
     * @return SerializableClosure
     * @link http://www.php.net/manual/en/closure.bindto.php
     */
    public function bindTo($newthis, $newscope = 'static')
    {
        return new self(
            $this->closure->bindTo($newthis, $newscope),
            $this->serializer
        );
    }

    /**
     * Serializes the code, context, and binding of the closure.
     *
     * @return string|null
     * @link http://php.net/manual/en/serializable.serialize.php
     */
    public function serialize()
    {
        try {
            $this->data = $this->data ?: $this->serializer->getData($this->closure, true);
            return serialize($this->data);
        } catch (\Exception $e) {
            trigger_error(
                'Serialization of closure failed: ' . $e->getMessage(),
                E_USER_NOTICE
            );
            // Note: The serialize() method of Serializable must return a string
            // or null and cannot throw exceptions.
            return null;
        }
    }

    /**
     * Unserializes the closure.
     *
     * Unserializes the closure's data and recreates the closure using a
     * simulation of its original context. The used variables (context) are
     * extracted into a fresh scope prior to redefining the closure. The
     * closure is also rebound to its former object and scope.
     *
     * @param string $serialized
     *
     * @throws ClosureUnserializationException
     * @link http://php.net/manual/en/serializable.unserialize.php
     */
    public function unserialize($serialized)
    {
        // Unserialize the closure data and reconstruct the closure object.
        $this->data = unserialize($serialized);
        $this->closure = __reconstruct_closure($this->data);

        // Throw an exception if the closure could not be reconstructed.
        if (!$this->closure instanceof Closure) {
            throw new ClosureUnserializationException(
                'The closure is corrupted and cannot be unserialized.'
            );
        }

        // Rebind the closure to its former binding and scope.
        if ($this->data['binding'] || $this->data['isStatic']) {
            $this->closure = $this->closure->bindTo(
                $this->data['binding'],
                $this->data['scope']
            );
        }
    }

    /**
     * Returns closure data for `var_dump()`.
     *
     * @return array
     */
    public function __debugInfo()
    {
        return $this->data ?: $this->serializer->getData($this->closure, true);
    }
}

/**
 * Reconstruct a closure.
 *
 * HERE BE DRAGONS!
 *
 * The infamous `eval()` is used in this method, along with the error
 * suppression operator, and variable variables (i.e., double dollar signs) to
 * perform the unserialization logic. I'm sorry, world!
 *
 * This is also done inside a plain function instead of a method so that the
 * binding and scope of the closure are null.
 *
 * @param array $__data Unserialized closure data.
 *
 * @return Closure|null
 * @internal
 */
function __reconstruct_closure(array $__data)
{
    // Simulate the original context the closure was created in.
    foreach ($__data['context'] as $__var_name => &$__value) {
        if ($__value instanceof SerializableClosure) {
            // Unbox any SerializableClosures in the context.
            $__value = $__value->getClosure();
        } elseif ($__value === Serializer::RECURSION) {
            // Track recursive references (there should only be one).
            $__recursive_reference = $__var_name;
        }

        // Import the variable into this scope.
        ${$__var_name} = $__value;
    }

    // Evaluate the code to recreate the closure.
    try {
        if (isset($__recursive_reference)) {
            // Special handling for recursive closures.
            @eval("\${$__recursive_reference} = {$__data['code']};");
            $__closure = ${$__recursive_reference};
        } else {
            @eval("\$__closure = {$__data['code']};");
        }
    } catch (\ParseError $e) {
        // Discard the parse error.
    }

    return isset($__closure) ? $__closure : null;
}
