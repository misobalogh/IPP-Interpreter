<?php

namespace IPP\Student;

use IPP\Student\Exception\SemanticErrorException;
use IPP\Student\Exception\XMLStructureException;

class InstructionData
{
    /** @var \DOMElement */
    public $instruction;
    /** @var int */
    public $order;
    /** @var string */
    public $opcode;
    /** @var InstructionArgument */
    public $arg1 = null;
    /** @var InstructionArgument */
    public $arg2 = null;
    /** @var InstructionArgument */
    public $arg3 = null;

    /**
     * InstructionData constructor.
     * @param \DOMElement $instruction
     */
    final public function __construct($instruction)
    {
        $this->instruction = $instruction;
        $this->setOrder();
        $this->setOpcode();
        $this->setArgs(); 
    }

    private function setOrder() : void
    {
        $this->order = (int)$this->instruction->getAttribute('order');
    }

    private function setOpcode() : void
    {
        $this->opcode = $this->instruction->getAttribute('opcode'); 
    }

    private function setArgs() : void
    {
        $childNodes = $this->instruction->childNodes;

        foreach ($childNodes as $arg) {
            if ($arg->nodeType === XML_ELEMENT_NODE && strpos($arg->nodeName, 'arg') === 0 && $arg instanceof \DOMElement) {
                $type = trim($arg->getAttribute('type'));
                if (!in_array($type, [DataType::INT, DataType::BOOL, DataType::STRING, DataType::NIL, DataType::TYPE, DataType::LABEL, DataType::VAR])) {
                    throw new XMLStructureException("Invalid argument type");
                }
                $argName = trim($arg->nodeValue);
                if ($argName !== '') {
                    if ($type === DataType::VAR) {
                        list($frame, $value) = array_map('trim', explode('@', $argName));
                        if (!in_array($frame, ['GF', 'LF', 'TF'])) {
                            throw new XMLStructureException("Invalid frame");
                        }
                    } else {
                        $frame = null;
                        $value = $argName;
                    }

                } else {
                    throw new XMLStructureException("Argument value is empty");
                }

               

                switch ($arg->nodeName) {
                    case 'arg1':
                        if ($this->arg1 !== null) {
                            throw new XMLStructureException("Multiple arg1 arguments");
                        }
                        $this->arg1 = new InstructionArgument($type, $frame, $value);
                        break;
                    case 'arg2':
                        if ($this->arg2 !== null) {
                            throw new XMLStructureException("Multiple arg2 arguments");
                        }
                        $this->arg2 = new InstructionArgument($type, $frame, $value);
                        break;
                    case 'arg3':
                        if ($this->arg3 !== null) {
                            throw new XMLStructureException("Multiple arg3 arguments");
                        }
                        $this->arg3 = new InstructionArgument($type, $frame, $value);
                        break;
                    default:
                        break;
                }
            }
        }
    }
}




/**
 * Class InstructionArgument
 * 
 * Class for storing argument of instruction
 *
 * @property string $type
 * @property string $frame
 * @property int|bool|string $value
 */
class InstructionArgument
{
    public string $type;
    public ?string $frame;
    /** @var int|bool|string */
    public $value;

    /**
     * InstructionArgument constructor.
     * @param string $type
     * @param string|null $frame
     * @param int|bool|string $value
     */
    public function __construct(string $type, ?string $frame, $value)
    {
        $this->type = $type;
        $this->frame = $frame;
        if ($type === DataType::INT) {
            if (!is_numeric($value)) {
                throw new XMLStructureException("Invalid value for int type");
            }
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

    public function getValue() : mixed
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

    public function getType() : mixed
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