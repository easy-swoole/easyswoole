<?php namespace SuperClosure\Analyzer;

/**
 * A Token object represents and individual token parsed from PHP code.
 *
 * Each Token object is a normalized token created from the result of the
 * `get_token_all()`. function, which is part of PHP's tokenizer.
 *
 * @link http://us2.php.net/manual/en/tokens.php
 */
class Token
{
    /**
     * @var string The token name. Always null for literal tokens.
     */
    public $name;

    /**
     * @var int|null The token's integer value. Always null for literal tokens.
     */
    public $value;

    /**
     * @var string The PHP code of the token.
     */
    public $code;

    /**
     * @var int|null The line number of the token in the original code.
     */
    public $line;

    /**
     * Constructs a token object.
     *
     * @param string   $code
     * @param int|null $value
     * @param int|null $line
     *
     * @throws \InvalidArgumentException
     */
    public function __construct($code, $value = null, $line = null)
    {
        if (is_array($code)) {
            list($value, $code, $line) = array_pad($code, 3, null);
        }

        $this->code = $code;
        $this->value = $value;
        $this->line = $line;
        $this->name = $value ? token_name($value) : null;
    }

    /**
     * Determines if the token's value/code is equal to the specified value.
     *
     * @param mixed $value The value to check.
     *
     * @return bool True if the token is equal to the value.
     */
    public function is($value)
    {
        return ($this->code === $value || $this->value === $value);
    }

    public function __toString()
    {
        return $this->code;
    }
}
