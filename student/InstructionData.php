<?php

namespace IPP\Student;

use DOMElement;
use IPP\Student\Exception\XMLStructureException;

class InstructionData
{
    public DOMElement $instruction;
    public int $order;
    public string $opcode;
    public ?InstructionArgument $arg1 = null;
    public ?InstructionArgument $arg2 = null;
    public ?InstructionArgument $arg3 = null;

    /**
     * InstructionData constructor.
     * @param DOMElement $instruction
     * @throws XMLStructureException
     */
    final public function __construct(DOMElement $instruction)
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

    /**
     * @throws XMLStructureException
     */
    private function setArgs() : void
    {
        $childNodes = $this->instruction->childNodes;

        foreach ($childNodes as $arg) {
            if ($arg->nodeType === XML_ELEMENT_NODE && str_starts_with($arg->nodeName, 'arg') && $arg instanceof DOMElement) {
                $type = trim($arg->getAttribute('type'));
                if (!in_array($type, [Types\DataType::INT, Types\DataType::BOOL, Types\DataType::STRING, Types\DataType::NIL, Types\DataType::TYPE, Types\DataType::LABEL, Types\DataType::VAR])) {
                    throw new XMLStructureException("Invalid argument type");
                }
                $argName = trim($arg->nodeValue);
                if ($argName !== '' || $type === Types\DataType::STRING) {
                    if ($type === Types\DataType::VAR) {
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

