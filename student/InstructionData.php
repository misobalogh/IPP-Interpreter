<?php

namespace IPP\Student;

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
                $args[] = new InstructionArgument($type, $argName);
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
 * @property string $value
 */
class InstructionArgument {
    public string $type;
    public string $value;

    public function __construct($type, $value) {
        $this->type = $type;
        $this->value = $value;
    }
}
