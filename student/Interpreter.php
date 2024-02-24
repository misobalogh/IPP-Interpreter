<?php

namespace IPP\Student;

use IPP\Core\AbstractInterpreter;
use IPP\Core\Exception\NotImplementedException;

class Interpreter extends AbstractInterpreter
{
    public function execute(): int
    {
        // TODO: Start your code here
        // Check \IPP\Core\AbstractInterpreter for predefined I/O objects:
        // $dom = $this->source->getDOMDocument();
        // $val = $this->input->readString();
        // $this->stdout->writeString("stdout");
        // $this->stderr->writeString("stderr");

        $dom = $this->source->getDOMDocument();
        $instructions = $dom->getElementsByTagName('instruction');

        foreach ($instructions as $instruction) {
            $opcode = $this->getOpcode($instruction);

            echo "Opcode: $opcode\n";
        }

        $program = $dom->getElementsByTagName('program')->item(0);

        if ($program) {
            $language = $program->getAttribute('language');

            echo "Language: $language\n";
        } else {
            echo "Program element not found\n";
        }
        $var = "var";

        return 0;

        // throw new NotImplementedException;
    }


    private function getOpcode($instruction)
    {
        return $instruction->getAttribute('opcode');
    }

    
}
