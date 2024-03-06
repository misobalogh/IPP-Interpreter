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
                if (strpos($argName, '@') !== false) {
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
    public string $value;

    public function __construct(string $type, ?string $frame, string $value)
    {
        $this->type = $type;
        $this->frame = $frame;
        $this->value = $value;
    }

    public function isDefined(): bool
    {
        if ($this->type === DataType::VAR)
        {
            return ProgramFlow::getFrame($this->frame)->keyExists($this->value);
        }
        else if ($this->type === DataType::LABEL)
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
        if ($this->type === DataType::VAR)
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
        if ($this->type === DataType::VAR)
        {
            return ProgramFlow::getFrame($this->frame)->getData($this->value)["type"];
        }
        else
        {
            return $this->type;
        }
    }

    public function isType(string $type): bool
    {
        return $this->type === $type;
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