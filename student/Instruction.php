<?php

namespace IPP\Student;

use IPP\Student\Exception\SemanticErrorException;
use IPP\Student\Exception\WrongOperandTypeException;
use IPP\Student\Exception\UndefinedVariableException;
use IPP\Student\Exception\OperandValueException;
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
        return 0;
    }
}

class InstructionPushFrame extends Instruction
{
    public function execute(): int{
        return 0;
    }
}

class InstructionPopFrame extends Instruction
{
    public function execute(): int{
        return 0;
    }
}

class InstructionDefVar extends Instruction
{
    public function execute(): int{
        ProgramFlow::addToFrame($this->args[0]->frame, $this->args[0]->value, null, null);     
        return 0;
    }
}

class InstructionCall extends Instruction
{
    public function execute(): int{
        return 0;
    }
}

class InstructionReturn extends Instruction
{
    public function execute(): int{
        return 0;
    }
}



#=========== Data stack ===========

class InstructionPushs extends Instruction
{
    public function execute(): int{
        return 0;
    }
}

class InstructionPops extends Instruction
{
    public function execute(): int{
        return 0;
    }
}



#=========== Arithmetic, relational, boolean and conversion ===========

class InstructionAdd extends Instruction
{
    public function execute(): int{
        $this->checkArgs();

        $symbol1_value = $this->args[1]->getValue();
        $symbol1_type = $this->args[1]->getType();

        $symbol2_value = $this->args[2]->getValue();
        $symbol2_type = $this->args[2]->getType();

        if ($symbol1_type !== DataType::INT || $symbol2_type !== DataType::INT) {
            throw new WrongOperandTypeException("ADD accepts only integers");
        }

        $valueSet = $this->args[0]->value;
        $frameSet = $this->args[0]->frame;

        ProgramFlow::getFrame($frameSet)->setData($valueSet, DataType::INT, $symbol1_value + $symbol2_value);
        return 0;
    }
}

class InstructionSub extends Instruction
{
    public function execute(): int{
        $this->checkArgs();

        $symbol1_value = $this->args[1]->getValue();
        $symbol1_type = $this->args[1]->getType();

        $symbol2_value = $this->args[2]->getValue();
        $symbol2_type = $this->args[2]->getType();

        if ($symbol1_type !== DataType::INT || $symbol2_type !== DataType::INT) {
            throw new WrongOperandTypeException("SUB accepts only integers");
        }

        $valueSet = $this->args[0]->value;
        $frameSet = $this->args[0]->frame;
        
        ProgramFlow::getFrame($frameSet)->setData($valueSet, DataType::INT, $symbol1_value - $symbol2_value);

        return 0;
    }
}

class InstructionMul extends Instruction
{
    public function execute(): int{
        $this->checkArgs();

        $symbol1_value = $this->args[1]->getValue();
        $symbol1_type = $this->args[1]->getType();

        $symbol2_value = $this->args[2]->getValue();
        $symbol2_type = $this->args[2]->getType();

        if ($symbol1_type !== DataType::INT || $symbol2_type !== DataType::INT) {
            throw new WrongOperandTypeException("MUL accepts only integers");
        }

        $valueSet = $this->args[0]->value;
        $frameSet = $this->args[0]->frame;
        
        ProgramFlow::getFrame($frameSet)->setData($valueSet, DataType::INT, $symbol1_value * $symbol2_value);

        return 0;
    }
}

class InstructionIDiv extends Instruction
{
    public function execute(): int{
        $this->checkArgs();

        $symbol1_value = $this->args[1]->getValue();
        $symbol1_type = $this->args[1]->getType();

        $symbol2_value = $this->args[2]->getValue();
        $symbol2_type = $this->args[2]->getType();

        if ($symbol1_type !== DataType::INT || $symbol2_type !== DataType::INT) {
            throw new WrongOperandTypeException("IDIV accepts only integers");
        }

        if ($symbol2_value === 0) {
            throw new OperandValueException("Division by zero");
        }

        $valueSet = $this->args[0]->value;
        $frameSet = $this->args[0]->frame;
        
        ProgramFlow::getFrame($frameSet)->setData($valueSet, DataType::INT, intdiv($symbol1_value, $symbol2_value));
        
        return 0;
    }
}

class InstructionLt extends Instruction
{
    public function execute(): int{
        $this->checkArgs();
        
        $symbol1_type = $this->args[1]->getType();
        $symbol2_type = $this->args[2]->getType();
        
        if ($symbol1_type === DataType::NIL || $symbol2_type === DataType::NIL) {
            throw new WrongOperandTypeException("Cannot compare with nil");
        }

        if ($symbol1_type !== $symbol2_type) {
            throw new WrongOperandTypeException("Cannot compare different types");
        }

        $valueSet = $this->args[0]->value;
        $frameSet = $this->args[0]->frame;
        
        ProgramFlow::getFrame($frameSet)->setData($valueSet, DataType::BOOL, $this->args[1]->getValue() < $this->args[2]->getValue());

        return 0;
    }
}

class InstructionGt extends Instruction
{
    public function execute(): int{
        $this->checkArgs();

        $symbol1_type = $this->args[1]->getType();
        $symbol2_type = $this->args[2]->getType();
        
        if ($symbol1_type === DataType::NIL || $symbol2_type === DataType::NIL) {
            throw new WrongOperandTypeException("Cannot compare with nil");
        }

        if ($symbol1_type !== $symbol2_type) {
            throw new WrongOperandTypeException("Cannot compare different types");
        }

        $valueSet = $this->args[0]->value;
        $frameSet = $this->args[0]->frame;
        
        ProgramFlow::getFrame($frameSet)->setData($valueSet, DataType::BOOL, $this->args[1]->getValue() > $this->args[2]->getValue());

        return 0;
    }
}

class InstructionEq extends Instruction
{
    public function execute(): int{
        $this->checkArgs();
        $symbol1_type = $this->args[1]->getType();
        $symbol2_type = $this->args[2]->getType();
        
        if ($symbol1_type !== $symbol2_type) {
            throw new WrongOperandTypeException("Cannot compare different types");
        }

        $valueSet = $this->args[0]->value;
        $frameSet = $this->args[0]->frame;
        
        ProgramFlow::getFrame($frameSet)->setData($valueSet, DataType::BOOL, $this->args[1]->getValue() === $this->args[2]->getValue());

        return 0;
    }
}

class InstructionAnd extends Instruction
{
    public function execute(): int{
        $this->checkArgs();

        $symbol1_type = $this->args[1]->getType();
        $symbol2_type = $this->args[2]->getType();
        
        if ($symbol1_type !== DataType::BOOL || $symbol2_type !== DataType::BOOL) {
            throw new WrongOperandTypeException("AND accepts only booleans");
        }

        $valueSet = $this->args[0]->value;
        $frameSet = $this->args[0]->frame;
        
        ProgramFlow::getFrame($frameSet)->setData($valueSet, DataType::BOOL, $this->args[1]->getValue() && $this->args[2]->getValue());

        return 0;
    }
}

class InstructionOr extends Instruction
{
    public function execute(): int{
        $this->checkArgs();

        $symbol1_type = $this->args[1]->getType();
        $symbol2_type = $this->args[2]->getType();
        
        if ($symbol1_type !== DataType::BOOL || $symbol2_type !== DataType::BOOL) {
            throw new WrongOperandTypeException("OR accepts only booleans");
        }

        $valueSet = $this->args[0]->value;
        $frameSet = $this->args[0]->frame;
        
        ProgramFlow::getFrame($frameSet)->setData($valueSet, DataType::BOOL, $this->args[1]->getValue() || $this->args[2]->getValue());

        return 0;
    }
}   

class InstructionNot extends Instruction
{
    public function execute(): int{
        $this->checkArgs();

        $symbol1_type = $this->args[1]->getType();
        
        if ($symbol1_type !== DataType::BOOL) {
            throw new WrongOperandTypeException("NOT accepts only booleans");
        }

        $valueSet = $this->args[0]->value;
        $frameSet = $this->args[0]->frame;
        
        ProgramFlow::getFrame($frameSet)->setData($valueSet, DataType::BOOL, !$this->args[1]->getValue());

        return 0;
    }
}

class InstructionInt2Char extends Instruction
{
    public function execute(): int{
        return 0;
    }
}

class InstructionStri2Int extends Instruction
{
    public function execute(): int{
        return 0;
    }
}



#=========== I/O ===========

class InstructionRead extends Instruction
{
    public function execute(): int{
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
            throw new WrongOperandTypeException("Cannot concatenate other types than strings");
        }

        $valueSet = $this->args[0]->value;
        $frameSet = $this->args[0]->frame;

        ProgramFlow::getFrame($frameSet)->setData($valueSet, DataType::STRING, $symbol1_value . $symbol2_value);
        
        return 0;
    }
}

class InstructionStrlen extends Instruction
{
    public function execute(): int{
        $symbol1_type = $this->args[1]->getType();
        if ($symbol1_type !== DataType::STRING) {
            throw new WrongOperandTypeException("STRLEN accepts only strings");
        }

        $symbol1_vlaue = $this->args[1]->getValue();

        $valueSet = $this->args[0]->value;
        $frameSet = $this->args[0]->frame;

        ProgramFlow::getFrame($frameSet)->setData($valueSet, DataType::INT, strlen($symbol1_vlaue));
    }
}

class InstructionGetChar extends Instruction
{
    public function execute(): int{
        return 0;
    }
}

class InstructionSetChar extends Instruction
{
    public function execute(): int{
        return 0;
    }
}




#=========== Types ===========

class InstructionType extends Instruction
{
    public function execute(): int{
        $this->checkArgs();
        
        $type = $this->args[1]->getType();
        
        $valueSet = $this->args[0]->value;
        if (!$type) {
            $valueSet = "";
        }
        
        $frameSet = $this->args[0]->frame; 
        
        ProgramFlow::getFrame($frameSet)->setData($valueSet, DataType::TYPE, $type);


        return 0;
    }
}



#=========== Flow control ===========

class InstructionLabel extends Instruction
{
    public function execute(): int{
        return 0;
    }
}

class InstructionJump extends Instruction
{
    public function execute(): int{
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
        return 0;
    }
}

class InstructionExit extends Instruction
{
    public function execute(): int{
        $this->checkArgs();

        if ($this->args[0]->getType() !== DataType::INT) {
            throw new WrongOperandTypeException("EXIT accepts only integers");
        }

        if ($this->args[0]->getValue() < 0 || $this->args[0]->getValue() > 9) {
            throw new OperandValueException("EXIT accepts only integers in range 0-9");
        }

        ProgramFlow::exit($this->args[0]->getValue());
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
    public function __construct(array $args, private StreamWriter $stderr)
    {
        parent::__construct($args);
    }

    public function execute(): int{
        $this->stderr->writeString("Break");
        $this->stderr->writeString("Position in program: " . ProgramFlow::getPointer());
        return 0;
    }
}
