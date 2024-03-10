<?php

namespace IPP\Student;

use IPP\Core\AbstractInterpreter;
use IPP\Core\Exception\NotImplementedException;
use IPP\Student\InstructionFactory;
use IPP\Student\Exception\XMLFormatException;
use IPP\Student\Exception\XMLStructureException;


class Interpreter extends AbstractInterpreter
{
    private array $instructions;

    public function execute(): int
    {
        // TODO: Start your code here
        // Check \IPP\Core\AbstractInterpreter for predefined I/O objects:
        // $dom = $this->source->getDOMDocument();
        // $val = $this->input->readString();
        // $this->stdout->writeString("stdout");
        // $this->stderr->writeString("stderr");

        $dom = $this->source->getDOMDocument();
        $this->checkHeader($dom);
        
        $this->setInstructionsData($dom);
        
        $instructionFactory = new InstructionFactory($this->source, $this->input, $this->stdout, $this->stderr);

        ProgramFlow::initialize($this->instructions);


        while (ProgramFlow::continue()) {
            $instructionData = $this->instructions[ProgramFlow::getPointer()];
            $instruction = $instructionFactory->createInstruction($instructionData);
            $instruction->execute();
            ProgramFlow::increment();
        }
       
        return 0;
    }

    private function usortInstructions($inst1, $inst2)
    {
        return $inst1->order - $inst2->order;
    }

    private function setInstructionsData($dom)
    {
        $instructionsArray = array();
        foreach ($dom->getElementsByTagName('instruction') as $instruction) {
            if (!$instruction->hasAttribute('order') || !$instruction->hasAttribute('opcode')){
                throw new XMLStructureException("Invalid XML format");
            }

            if (!is_numeric($instruction->getAttribute('order'))) {
                throw new XMLStructureException("Invalid XML format");
            }

            foreach ($instruction->childNodes as $childNode) {
                if ($childNode->nodeType === XML_ELEMENT_NODE && !in_array($childNode->nodeName, ['arg1', 'arg2', 'arg3'])) {
                    throw new XMLStructureException("Unexpected child node");
                }
            }
            
            $instructionData = new InstructionData($instruction);
            $instructionsArray[] = $instructionData;
        }

        // sort instructions by order and check if the order is ascending
        usort($instructionsArray, array($this, 'usortInstructions'));
        $previousOrder = 0;
        foreach ($instructionsArray as $instruction) {
            if ($instruction->order <= $previousOrder) {
                throw new XMLStructureException("Order of instructions is not ascending");
            }
            $previousOrder = $instruction->order;   
        }

        $this->instructions = $instructionsArray;
    } 
    
    private function checkHeader($dom)
    {
        $rootElement = $dom->documentElement;
        if ($rootElement->nodeName !== 'program' || $rootElement->getAttribute('language') !== 'IPPcode24') {
            throw new XMLStructureException("Invalid header");
        }

        foreach ($rootElement->childNodes as $childNode) {
            if ($childNode->nodeType === XML_ELEMENT_NODE && $childNode->nodeName !== 'instruction') {
                throw new XMLStructureException("Unexpected child node");
            }
        }
    }
    
}
