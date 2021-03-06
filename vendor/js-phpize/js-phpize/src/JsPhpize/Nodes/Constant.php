<?php

namespace JsPhpize\Nodes;

use JsPhpize\Parse\Exception;

class Constant extends Value implements Assignable
{
    /**
     * @var string
     */
    protected $type;

    /**
     * @var string
     */
    protected $value;

    public function __construct($type, $value)
    {
        if (!in_array($type, array('constant', 'number', 'string'))) {
            throw new Exception("The given type [$type] is not a valid constant type.", 8);
        }
        $this->type = $type;
        $this->value = $value;
    }

    public function getNonAssignableReason()
    {
        if ($this->type !== 'constant') {
            return "{$this->type} is not assignable.";
        }
        if (in_array($this->value, array('NAN', 'INF'))) {
            return "{$this->value} is not assignable.";
        }
        if (substr($this->value, 0, 2) === 'M_') {
            return "'M_' prefix is reserved to mathematical constants.";
        }
    }

    public function isAssignable()
    {
        return !$this->getNonAssignableReason();
    }
}
