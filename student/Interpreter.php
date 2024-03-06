<?php

namespace IPP\Student;

use IPP\Core\AbstractInterpreter;
use IPP\Core\Exception\NotImplementedException;
use IPP\Student\InstructionFactory;

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

        $instructions = $this->getInstructionsData($dom);
        
        $instructionFactory = new InstructionFactory($this->source, $this->input, $this->stdout, $this->stderr);

        ProgramFlow::initialize($this->instructions);

        

        while (ProgramFlow::continue()) {
            $instructionData = $this->instructions[ProgramFlow::getPointer()];
            $instruction = $instructionFactory->createInstruction($instructionData->opcode, $instructionData->args);
            $instruction->execute();
            // print_r($instructionData);
            ProgramFlow::increment();
            // echo "Pointer at: " . ProgramFlow::getPointer() . "\n";
        }
        // $instruction = $instructionFactory->createInstruction('MOVE', ["GF@result", "GF@var1", "GF@var2"]);
        // $instruction->execute();

        // $program = $dom->getElementsByTagName('program')->item(0);

        // if ($program) {
        //     $language = $program->getAttribute('language');

        //     echo "Language: $language\n";
        // } else {
        //     echo "Program element not found\n";
        // }
     
        return 0;
    }

    private function getInstructionsData($dom)
    {
        $instructionsArray = array();
        foreach ($dom->getElementsByTagName('instruction') as $instruction) {
            $instructionData = new InstructionData($instruction);
            $instructionsArray[] = $instructionData;
        }
        $this->instructions = $instructionsArray;
    } 
    
}
