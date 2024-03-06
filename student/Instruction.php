<?php

namespace IPP\Student;

use IPP\Student\Exception\SemanticErrorException;
use IPP\Student\Exception\WrongOperandTypeException;
use IPP\Student\Exception\UndefinedVariableException;
use IPP\Core\StreamWriter;

#=========== Abstract class for instructions ===========

abstract class Instruction
{
    protected array $args;

    public function __construct(array $args)
    {
        $this->args = $args;
    }

    final public function checkArgs() : void
    {
        foreach ($this->args as $arg) {
            if (!$arg->isDefined()) {
                throw new UndefinedVariableException("Undefined variable");
            }
        }
    }

    abstract public function execute(): int;
}


#=========== Memory frames, function calls ===========

class InstructionMove extends Instruction
{
    public function execute(): int{
        $this->checkArgs();

        $valueGet = $this->args[1]->getValue();
        $typeGet = $this->args[1]->type;
        
        $valueSet = $this->args[0]->value;
        $frameSet = $this->args[0]->frame;

        ProgramFlow::GetFrame($frameSet)->setData($valueSet, $typeGet, $valueGet);
        return 0;
    }
}

class InstructionCreateFrame extends Instruction
{
    public function execute(): int{
        echo "CREATEFRAME\n";
        return 0;
    }
}

class InstructionPushFrame extends Instruction
{
    public function execute(): int{
        echo "PUSHFRAME\n";
        return 0;
    }
}

class InstructionPopFrame extends Instruction
{
    public function execute(): int{
        echo "POPFRAME\n";
        return 0;
    }
}

class InstructionDefVar extends Instruction
{
    public function execute(): int{
        echo "DEFVAR\n";
        ProgramFlow::addToFrame($this->args[0]->frame, $this->args[0]->value, null, null);     
        return 0;
    }
}

class InstructionCall extends Instruction
{
    public function execute(): int{
        echo "CALL\n";
        return 0;
    }
}

class InstructionReturn extends Instruction
{
    public function execute(): int{
        echo "RETURN\n";
        return 0;
    }
}



#=========== Data stack ===========

class InstructionPushs extends Instruction
{
    public function execute(): int{
        echo "PUSHS\n";
        return 0;
    }
}

class InstructionPops extends Instruction
{
    public function execute(): int{
        echo "POPS\n";
        return 0;
    }
}



#=========== Arithmetic, relational, boolean and conversion ===========

class InstructionAdd extends Instruction
{
    public function execute(): int{
        echo "ADD\n";
        return 0;
    }
}

class InstructionSub extends Instruction
{
    public function execute(): int{
        echo "SUB\n";
        return 0;
    }
}

class InstructionMul extends Instruction
{
    public function execute(): int{
        echo "MUL\n";
        return 0;
    }
}

class InstructionIDiv extends Instruction
{
    public function execute(): int{
        echo "IDIV\n";
        return 0;
    }
}

class InstructionLt extends Instruction
{
    public function execute(): int{
        echo "LT\n";
        return 0;
    }
}

class InstructionGt extends Instruction
{
    public function execute(): int{
        echo "GT\n";
        return 0;
    }
}

class InstructionEq extends Instruction
{
    public function execute(): int{
        echo "EQ\n";
        return 0;
    }
}

class InstructionAnd extends Instruction
{
    public function execute(): int{
        echo "AND\n";
        return 0;
    }
}

class InstructionOr extends Instruction
{
    public function execute(): int{
        echo "OR\n";
        return 0;
    }
}   

class InstructionNot extends Instruction
{
    public function execute(): int{
        echo "NOT\n";
        return 0;
    }
}

class InstructionInt2Char extends Instruction
{
    public function execute(): int{
        echo "INT2CHAR\n";
        return 0;
    }
}

class InstructionStri2Int extends Instruction
{
    public function execute(): int{
        echo "STRI2INT\n";
        return 0;
    }
}



#=========== I/O ===========

class InstructionRead extends Instruction
{
    public function execute(): int{
        echo "READ\n";
        return 0;
    }
}

class InstructionWrite extends Instruction
{
    public function __construct(array $args, private StreamWriter $stdout)
    {
        parent::__construct($args);
    }

    public function execute(): int{
        $this->checkArgs();
        
        $value = $this->args[0]->getValue();
        $type = $this->args[0]->getType();

        if ($type === DataType::NIL) {
            $this->stdout->writeString("");            
        }

        else if ($type === DataType::BOOL) {
            $this->stdout->writeBool($value);
        }

        else if ($type === DataType::INT) {
            $this->stdout->writeInt($value);
        }

        else if ($type === DataType::STRING) {
            // $this->stdout->writeString(stripcslashes($value));
            $this->stdout->writeString(preg_replace_callback('/\\\\([0-9]{3})/', 
                function ($matches) {
                    return chr($matches[1]);
                },
                $value
            ));
        }
        return 0;
    }

    private function replace_ascii($matches) {
        return chr($matches[1]);
    }
}



#=========== Strings ===========

class InstructionConcat extends Instruction
{
    public function execute(): int{
        $this->checkArgs();

        $symbol1_value = $this->args[1]->getValue();
        $symbol1_type = $this->args[1]->getType();

        $symbol2_value = $this->args[2]->getValue();
        $symbol2_type = $this->args[2]->getType();

        if ($symbol1_type !== DataType::STRING || $symbol2_type !== DataType::STRING) {
            throw new WrongOperandTypeException("Cannot concatenate non-strings");
        }

        $valueSet = $this->args[0]->value;

        ProgramFlow::getGlobalFrame()->setData($valueSet, DataType::STRING, $symbol1_value . $symbol2_value);
        
        // echo "CONCAT\n";
        return 0;
    }
}

class InstructionStrlen extends Instruction
{
    public function execute(): int{
        echo "STRLEN\n";
        return 0;
    }
}

class InstructionGetChar extends Instruction
{
    public function execute(): int{
        echo "GETCHAR\n";
        return 0;
    }
}

class InstructionSetChar extends Instruction
{
    public function execute(): int{
        echo "SETCHAR\n";
        return 0;
    }
}




#=========== Types ===========

class InstructionType extends Instruction
{
    public function execute(): int{
        echo "TYPE\n";
        return 0;
    }
}



#=========== Flow control ===========

class InstructionLabel extends Instruction
{
    public function execute(): int{
        echo "LABEL\n";
        return 0;
    }
}

class InstructionJump extends Instruction
{
    public function execute(): int{
        // echo "JUMP\n";
        ProgramFlow::jumpTo($this->args[0]->value);
        return 0;
    }
}

class InstructionJumpIfEQ extends Instruction
{
    public function execute(): int{
        $label = $this->args[0]->value;

        $symbol1_value = $this->args[1]->getValue();
        $symbol1_type = $this->args[1]->getType();

        $symbol2_value = $this->args[2]->getValue();
        $symbol2_type = $this->args[2]->getType();    

        if ($symbol1_type !== $symbol2_type && $symbol1_type !== DataType::NIL && $symbol2_type !== DataType::NIL) {
            throw new WrongOperandTypeException("Cannot compare different types");
        }

        if ($symbol1_value === $symbol2_value) {
            ProgramFlow::jumpTo($label);
        }
        
        // echo "JUMPIFEQ\n";

        return 0;
    }
}

class InstructionJumpIfNEQ extends Instruction
{
    public function execute(): int{
        $label = $this->args[0]->value;

        $symbol1_value = $this->args[1]->getValue();
        $symbol1_type = $this->args[1]->getType();

        $symbol2_value = $this->args[2]->getValue();
        $symbol2_type = $this->args[2]->getType();    

        if ($symbol1_type !== $symbol2_type && $symbol1_type !== DataType::NIL && $symbol2_type !== DataType::NIL) {
            throw new WrongOperandTypeException("Cannot compare different types");
        }

        if ($symbol1_value !== $symbol2_value) {
            ProgramFlow::jumpTo($label);
        }
        // echo "JUMPIFNEQ\n";
        return 0;
    }
}

class InstructionExit extends Instruction
{
    public function execute(): int{
        echo "EXIT\n";
        return 0;
    }
}



#=========== Debug ===========

class InstructionDprint extends Instruction
{
    public function __construct(array $args, private StreamWriter $stderr)
    {
        parent::__construct($args);
    }

    public function execute(): int{
        $this->checkArgs();
        
        $value = $this->args[0]->getValue();
        $type = $this->args[0]->getType();

        if ($type === DataType::NIL) {
            $this->stderr->writeString("");            
        }

        else if ($type === DataType::BOOL) {
            $this->stderr->writeBool($value);
        }

        else if ($type === DataType::INT) {
            $this->stderr->writeInt($value);
        }

        else if ($type === DataType::STRING) {
            $this->stderr->writeString(preg_replace_callback('/\\\\([0-9]{3})/', 
                function ($matches) {
                    return chr($matches[1]);
                },
                $value
            ));
        }
        return 0;
    }
}

class InstructionBreak extends Instruction
{
    public function execute(): int{
        echo "BREAK\n";
        return 0;
    }
}
