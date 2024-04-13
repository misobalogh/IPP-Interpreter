<?php
/*
Michal Balogh, xbalog06
IPP - project 2
VUT FIT 2024
*/


namespace IPP\Student;

use IPP\Core\AbstractInterpreter;
use IPP\Student\Exception\XMLStructureException;


class Interpreter extends AbstractInterpreter
{
    /**
     * @var InstructionData[]
     */
    private array $instructions;

    /**
     * @throws XMLStructureException
     * 
     * Main function of the interpreter
     * 
     * @return int exit code
     */
    public function execute(): int
    {
        $dom = $this->source->getDOMDocument();

        $this->checkHeader($dom);
        
        // extract instructions data from XML, check if it is valid and set it to work with
        $this->setInstructionsData($dom);

        $instructionFactory = new InstructionFactory($this->input, $this->stdout, $this->stderr);

        ProgramFlow::initialize($this->instructions);

        // execute instructions while the program flow allows it
        while (ProgramFlow::continue()) {
            $instructionData = $this->instructions[ProgramFlow::getPointer()];
            $instruction = $instructionFactory->createInstruction($instructionData);
            $instruction->execute();
            ProgramFlow::increment();
        }
       
        return 0;
    }

    /**
     * @param InstructionData $inst1
     * @param InstructionData $inst2
     * @return int
     * 
     * Helper function for usort to sort instructions by instruction order
     */
    private function usortInstructions(InstructionData $inst1, InstructionData $inst2) : int
    {
        return $inst1->order - $inst2->order;
    }

    /**
     * @param \DOMDocument $dom
     * @throws XMLStructureException
     */
    private function setInstructionsData(\DOMDocument $dom) : void
    {
        $instructionsArray = array();

        // check if all instructions have order and opcode attributes
        foreach ($dom->getElementsByTagName('instruction') as $instruction) {
            if (!$instruction->hasAttribute('order') || !$instruction->hasAttribute('opcode')){
                throw new XMLStructureException("Invalid XML format");
            }

            // check if order is numeric
            if (!is_numeric($instruction->getAttribute('order'))) {
                throw new XMLStructureException("Invalid XML format");
            }

            // check if child nodes are arg1, arg2, arg3  
            foreach ($instruction->childNodes as $childNode) {
                if ($childNode->nodeType === XML_ELEMENT_NODE && !in_array($childNode->nodeName, ['arg1', 'arg2', 'arg3'])) {
                    throw new XMLStructureException("Unexpected child node");
                }
            }
            
            // create InstructionData object and add it to array for each instruction
            $instructionData = new InstructionData($instruction);
            $instructionsArray[] = $instructionData;
        }

        // sort instructions by order and check if the order is ascending
        usort($instructionsArray, array($this, 'usortInstructions'));

        // check if order of instructions is ascending
        $previousOrder = 0;
        foreach ($instructionsArray as $instruction) {
            if ($instruction->order <= $previousOrder) {
                throw new XMLStructureException("Order of instructions is not ascending");
            }
            $previousOrder = $instruction->order;   
        }

        // set instructions array to work with
        $this->instructions = $instructionsArray;
    } 
    
    /**
     * @param \DOMDocument $dom
     * @throws XMLStructureException
     */
    private function checkHeader(\DOMDocument $dom) : void
    {
        // check if root element is program and has language attribute set to IPPcode24
        $rootElement = $dom->documentElement;
        if ($rootElement->nodeName !== 'program' || $rootElement->getAttribute('language') !== 'IPPcode24') {
            throw new XMLStructureException("Invalid header");
        }

        // check if there are no unexpected child nodes
        foreach ($rootElement->childNodes as $childNode) {
            if ($childNode->nodeType === XML_ELEMENT_NODE && $childNode->nodeName !== 'instruction') {
                throw new XMLStructureException("Unexpected child node");
            }
        }
    }
    
}
