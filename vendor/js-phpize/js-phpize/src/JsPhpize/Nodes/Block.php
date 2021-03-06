<?php

namespace JsPhpize\Nodes;

use JsPhpize\Parser\Exception;

class Block extends Node
{
    /**
     * @var string
     */
    protected $type;

    /**
     * @var Value
     */
    protected $value;

    /**
     * @var array
     */
    protected $instructions;

    /**
     * @var bool
     */
    protected $inInstruction;

    /**
     * @var array
     */
    protected $letVariables = array();

    public function __construct($type)
    {
        $this->type = $type;
        $this->instructions = array();
        $this->inInstruction = false;
    }

    public function let($variable)
    {
        $this->letVariables[] = $variable;
    }

    public function getLetVariables()
    {
        $scope = $this;

        return array_map(function ($name) use ($scope) {
            $variable = new Variable($name, array());
            $variable->setScope($scope);

            return $variable;
        }, $this->letVariables);
    }

    public function isLet($variable)
    {
        return in_array($variable, $this->letVariables);
    }

    public function handleInstructions()
    {
        return $this->needParenthesis() || in_array($this->type, array(
            'main',
            'else',
            'try',
            'finally',
            'do',
            'interface',
            'class',
        ));
    }

    public function needParenthesis()
    {
        return in_array($this->type, array(
            'if',
            'elseif',
            'catch',
            'for',
            'while',
            'function',
        ));
    }

    public function addInstructions($instructions)
    {
        if (!$this->handleInstructions()) {
            throw new Exception($this->type . ' blocks cannot contains instructions.', 17);
        }

        $instructions = is_array($instructions) ? $instructions : func_get_args();
        if (count($instructions)) {
            if (!$this->inInstruction) {
                $this->inInstruction = true;
                $this->instructions[] = new Instruction();
            }
            foreach ($instructions as $instruction) {
                $this->instructions[count($this->instructions) - 1]->add($instruction);
            }
        }
    }

    public function addInstruction()
    {
        $this->addInstructions(func_get_args());
    }

    public function endInstruction()
    {
        $this->inInstruction = false;
    }

    public function setValue(Value $value)
    {
        if ($this->needParenthesis() && !($value instanceof Parenthesis)) {
            throw new Exception($this->type . ' blocks need to be followed by a parenthesis.', 18);
        }

        if ($this->type === 'for') {
            $value->setSeparator(';');
        }

        $this->value = $value;
    }
}
