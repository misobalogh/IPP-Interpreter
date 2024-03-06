<?php

namespace IPP\Student;

use IPP\Student\Exception\SemanticErrorException;

class InstructionData
{
    public $instruction;
    public $order;
    public $opcode;
    public $args;

    final public function __construct($instruction)
    {
        $this->instruction = $instruction;
        $this->order = $this->getOrder();
        $this->opcode = $this->getOpcode();
        $this->args = $this->getArgs(); 
    }

    private function getOrder()
    {
        return (int)$this->instruction->getAttribute('order');
    }

    private function getOpcode()
    {
        return $this->instruction->getAttribute('opcode'); 
    }

    private function getArgs()
    {
        $args = array();
        $childNodes = $this->instruction->childNodes;
        foreach ($childNodes as $arg) {
            // if element starts with arg (is one of arg1, arg2, arg3)
            if ($arg->nodeType === XML_ELEMENT_NODE && strpos($arg->nodeName, 'arg') === 0) {
                $type = $arg->getAttribute('type');
                $argName = $arg->nodeValue;
                if ($type === DataType::VAR) {
                    list($frame, $value) = explode('@', $argName);
                }
                else {
                    $frame = null;
                    $value = $argName;
                }
                $args[] = new InstructionArgument($type, $frame, $value);
            }
        }

        return $args;
    }
}




/**
 * Class InstructionArgument
 * 
 * Class for storing argument of instruction
 *
 * @property string $type
 * @property string $frame
 * @property string $value
 */
class InstructionArgument
{
    public string $type;
    public ?string $frame;
    public $value;

    public function __construct(string $type, ?string $frame, string $value)
    {
        $this->type = $type;
        $this->frame = $frame;
        if ($type === DataType::INT) {
            $this->value = (int)$value;
        }
        else if ($type === DataType::BOOL) {
            $this->value = $value === "true";
        }
        else {
            $this->value = $value;
        }
    }

    public function isDefined(): bool
    {
        if ($this->isVar())
        {
            return ProgramFlow::getFrame($this->frame)->keyExists($this->value) || ProgramFlow::getGlobalFrame()->keyExists($this->value);
        }
        else if ($this->isLabel())
        {
            return ProgramFlow::labelExists($this->value);
        }
        else
        {
            return true;
        }
        
    }

    public function getValue()
    {
        if ($this->isVar())
        {
            return ProgramFlow::getFrame($this->frame)->getData($this->value)["value"];
        }
        else
        {
            return $this->value;
        }
    }

    public function getType()
    {
        if ($this->isVar())
        {
            return ProgramFlow::getFrame($this->frame)->getData($this->value)["type"];
        }
        else
        {
            return $this->type;
        }
    }

    public function isVar(): bool
    {
        return $this->type === DataType::VAR;
    }

    public function isLabel(): bool
    {
        return $this->type === DataType::LABEL;
    }

    public function isType(): bool
    {
        return $this->type === DataType::TYPE;
    }

    public function isNil(): bool
    {
        return $this->type === DataType::NIL;
    }

    public function isBool(): bool
    {
        return $this->type === DataType::BOOL;
    }

    public function isInt(): bool
    {
        return $this->type === DataType::INT;
    }

    public function isString(): bool
    {
        return $this->type === DataType::STRING;
    }
}

class DataType
{
    const INT = "int";
    const BOOL = "bool";
    const STRING = "string";
    const NIL = "nil";
    const TYPE = "type";
    const LABEL = "label";
    const VAR = "var";
}