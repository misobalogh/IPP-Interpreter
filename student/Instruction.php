<?php

namespace IPP\Student;

use IPP\Student\Exception\SemanticErrorException;
use IPP\Student\Exception\WrongOperandTypeException;
use IPP\Student\Exception\UndefinedVariableException;
use IPP\Student\Exception\OperandValueException;
use IPP\Student\Exception\FrameAccessException;
use IPP\Student\Exception\XMLStructureException;
use IPP\Student\Exception\StringOperationException;
use IPP\Student\Exception\ValueException;
use IPP\Core\StreamWriter;
use IPP\Core\FileInputReader;

#=========== Abstract class for instructions ===========

abstract class Instruction
{
    protected ?InstructionArgument $arg1;
    protected ?InstructionArgument $arg2;
    protected ?InstructionArgument $arg3;

    public function __construct(InstructionData $instructionData)
    {
        $this->arg1 = $instructionData->arg1;
        $this->arg2 = $instructionData->arg2;
        $this->arg3 = $instructionData->arg3;
    }

    final public function checkArgs() : void
    {
        foreach ([$this->arg1, $this->arg2, $this->arg3] as $arg) {
            if ($arg !== null && !$arg->isDefined() && $arg->type === DataType::VAR) {
                echo $arg->value."\n";
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
        if ($this->arg1 == null || $this->arg2 == null || $this->arg3 !== null) {
            throw new XMLStructureException("Missing argument");
        }

        $valueGet = $this->arg2->getValue();
        $typeGet = $this->arg2->type;
        
        $valueSet = $this->arg1->value;
        $frameSet = $this->arg1->frame;
        
        ProgramFlow::GetFrame($frameSet)->setData($valueSet, $typeGet, $valueGet);
        return 0;
    }
}

class InstructionCreateFrame extends Instruction
{
    public function execute(): int{
        ProgramFlow::deleteTemporaryFrame();
        $frame = new Frame();
        ProgramFlow::setTemporaryFrame($frame);

        return 0;
    }
}

class InstructionPushFrame extends Instruction
{
    public function execute(): int{
        $temporaryFrame = ProgramFlow::getTemporaryFrame();
        if ($temporaryFrame === null) {
            throw new FrameAccessException("Cannot push frame from empty stack");
        }

        ProgramFlow::pushFrame($temporaryFrame);
        ProgramFlow::deleteTemporaryFrame();

        return 0;
    }
}

class InstructionPopFrame extends Instruction
{
    public function execute(): int{
        $frame = ProgramFlow::popFrame();
        if ($frame === null) {
            throw new FrameAccessException("Cannot pop frame from empty stack");
        }

        ProgramFlow::setTemporaryFrame($frame);        

        return 0;
    }
}

class InstructionDefVar extends Instruction
{
    public function execute(): int {
        if ($this->arg1 === null || $this->arg2 !== null || $this->arg3 !== null) {
            throw new XMLStructureException("Missing argument");
        }
        ProgramFlow::addToFrame($this->arg1->frame, $this->arg1->value, null, null);     
        return 0;
    }
}

class InstructionCall extends Instruction
{
    public function execute(): int{
        $this->checkArgs();
        if ($this->arg1 === null || $this->arg2 !== null || $this->arg3 !== null) {
            throw new XMLStructureException("Missing argument");
        }

        ProgramFlow::pushToCallStack(ProgramFlow::getPointer());
        ProgramFlow::jumpTo($this->arg1->value);

        return 0;
    }
}

class InstructionReturn extends Instruction
{
    public function execute(): int{
        $pointer = ProgramFlow::popFromCallStack();
        if ($pointer === null) {
            throw new ValueException("Cannot return from empty call stack");
        }

        ProgramFlow::setPointer($pointer);

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
        if ($this->arg1 === null || $this->arg2 === null || $this->arg3 === null) {
            throw new XMLStructureException("Missing argument");
        }

        $symbol1_value = $this->arg2->getValue();
        $symbol1_type = $this->arg2->getType();

        $symbol2_value = $this->arg3->getValue();
        $symbol2_type = $this->arg3->getType();

        if ($symbol1_type !== DataType::INT || $symbol2_type !== DataType::INT) {
            throw new WrongOperandTypeException("ADD accepts only integers");
        }

        $valueSet = $this->arg1->value;
        $frameSet = $this->arg1->frame;

        ProgramFlow::getFrame($frameSet)->setData($valueSet, DataType::INT, $symbol1_value + $symbol2_value);
        return 0;
    }
}

class InstructionSub extends Instruction
{
    public function execute(): int{
        $this->checkArgs();
        if ($this->arg1 === null || $this->arg2 === null || $this->arg3 === null) {
            throw new XMLStructureException("Missing argument");
        }

        $symbol1_value = $this->arg2->getValue();
        $symbol1_type = $this->arg2->getType();

        $symbol2_value = $this->arg3->getValue();
        $symbol2_type = $this->arg3->getType();

        if ($symbol1_type !== DataType::INT || $symbol2_type !== DataType::INT) {
            throw new WrongOperandTypeException("SUB accepts only integers");
        }

        $valueSet = $this->arg1->value;
        $frameSet = $this->arg1->frame;
        
        ProgramFlow::getFrame($frameSet)->setData($valueSet, DataType::INT, $symbol1_value - $symbol2_value);

        return 0;
    }
}

class InstructionMul extends Instruction
{
    public function execute(): int{
        $this->checkArgs();
        if ($this->arg1 === null || $this->arg2 === null || $this->arg3 === null) {
            throw new XMLStructureException("Missing argument");
        }

        $symbol1_value = $this->arg2->getValue();
        $symbol1_type = $this->arg2->getType();

        $symbol2_value = $this->arg3->getValue();
        $symbol2_type = $this->arg3->getType();

        if ($symbol1_type !== DataType::INT || $symbol2_type !== DataType::INT) {
            throw new WrongOperandTypeException("MUL accepts only integers");
        }

        $valueSet = $this->arg1->value;
        $frameSet = $this->arg1->frame;
        
        ProgramFlow::getFrame($frameSet)->setData($valueSet, DataType::INT, $symbol1_value * $symbol2_value);

        return 0;
    }
}

class InstructionIDiv extends Instruction
{
    public function execute(): int{
        $this->checkArgs();
        if ($this->arg1 === null || $this->arg2 === null || $this->arg3 === null) {
            throw new XMLStructureException("Missing argument");
        }

        $symbol1_value = $this->arg2->getValue();
        $symbol1_type = $this->arg2->getType();

        $symbol2_value = $this->arg3->getValue();
        $symbol2_type = $this->arg3->getType();

        if ($symbol1_type !== DataType::INT || $symbol2_type !== DataType::INT) {
            throw new WrongOperandTypeException("IDIV accepts only integers");
        }

        if ($symbol2_value === 0) {
            throw new OperandValueException("Division by zero");
        }

        $valueSet = $this->arg1->value;
        $frameSet = $this->arg1->frame;
        
        ProgramFlow::getFrame($frameSet)->setData($valueSet, DataType::INT, intdiv($symbol1_value, $symbol2_value));
        
        return 0;
    }
}

class InstructionLt extends Instruction
{
    public function execute(): int{
        $this->checkArgs();
        if ($this->arg1 === null || $this->arg2 === null || $this->arg3 === null) {
            throw new XMLStructureException("Missing argument");
        }
        
        $symbol1_type = $this->arg2->getType();
        $symbol2_type = $this->arg3->getType();
        
        if ($symbol1_type === DataType::NIL || $symbol2_type === DataType::NIL) {
            throw new WrongOperandTypeException("Cannot compare with nil");
        }

        if ($symbol1_type !== $symbol2_type) {
            throw new WrongOperandTypeException("Cannot compare different types");
        }

        $valueSet = $this->arg1->value;
        $frameSet = $this->arg1->frame;
        
        ProgramFlow::getFrame($frameSet)->setData($valueSet, DataType::BOOL, $this->arg2->getValue() < $this->arg3->getValue());

        return 0;
    }
}

class InstructionGt extends Instruction
{
    public function execute(): int{
        $this->checkArgs();
        if ($this->arg1 === null || $this->arg2 === null || $this->arg3 === null) {
            throw new XMLStructureException("Missing argument");
        }

        $symbol1_type = $this->arg2->getType();
        $symbol2_type = $this->arg3->getType();
        
        if ($symbol1_type === DataType::NIL || $symbol2_type === DataType::NIL) {
            throw new WrongOperandTypeException("Cannot compare with nil");
        }

        if ($symbol1_type !== $symbol2_type) {
            throw new WrongOperandTypeException("Cannot compare different types");
        }

        $valueSet = $this->arg1->value;
        $frameSet = $this->arg1->frame;
        
        ProgramFlow::getFrame($frameSet)->setData($valueSet, DataType::BOOL, $this->arg2->getValue() > $this->arg3->getValue());

        return 0;
    }
}

class InstructionEq extends Instruction
{
    public function execute(): int{
        $this->checkArgs();
        if ($this->arg1 === null || $this->arg2 === null || $this->arg3 === null) {
            throw new XMLStructureException("Missing argument");
        }
        $symbol1_type = $this->arg2->getType();
        $symbol2_type = $this->arg3->getType();
        
        if ($symbol1_type !== $symbol2_type && $symbol1_type !== DataType::NIL && $symbol2_type !== DataType::NIL) {
            throw new WrongOperandTypeException("Cannot compare different types");
        }

        $valueSet = $this->arg1->value;
        $frameSet = $this->arg1->frame;
        
        ProgramFlow::getFrame($frameSet)->setData($valueSet, DataType::BOOL, $this->arg2->getValue() === $this->arg3->getValue());

        return 0;
    }
}

class InstructionAnd extends Instruction
{
    public function execute(): int{
        $this->checkArgs();
        if ($this->arg1 === null || $this->arg2 === null || $this->arg3 === null) {
            throw new XMLStructureException("Missing argument");
        }

        $symbol1_type = $this->arg2->getType();
        $symbol2_type = $this->arg3->getType();
        
        if ($symbol1_type !== DataType::BOOL || $symbol2_type !== DataType::BOOL) {
            throw new WrongOperandTypeException("AND accepts only booleans");
        }

        $valueSet = $this->arg1->value;
        $frameSet = $this->arg1->frame;
        
        ProgramFlow::getFrame($frameSet)->setData($valueSet, DataType::BOOL, $this->arg2->getValue() && $this->arg3->getValue());

        return 0;
    }
}

class InstructionOr extends Instruction
{
    public function execute(): int{
        $this->checkArgs();
        if ($this->arg1 === null || $this->arg2 === null || $this->arg3 === null) {
            throw new XMLStructureException("Missing argument");
        }

        $symbol1_type = $this->arg2->getType();
        $symbol2_type = $this->arg3->getType();
        
        if ($symbol1_type !== DataType::BOOL || $symbol2_type !== DataType::BOOL) {
            throw new WrongOperandTypeException("OR accepts only booleans");
        }

        $valueSet = $this->arg1->value;
        $frameSet = $this->arg1->frame;
        
        ProgramFlow::getFrame($frameSet)->setData($valueSet, DataType::BOOL, $this->arg2->getValue() || $this->arg3->getValue());

        return 0;
    }
}   

class InstructionNot extends Instruction
{
    public function execute(): int{
        $this->checkArgs();
        if ($this->arg1 === null || $this->arg2 === null || $this->arg3 !== null) {
            throw new XMLStructureException("Missing argument");
        }

        $symbol1_type = $this->arg2->getType();
        
        if ($symbol1_type !== DataType::BOOL) {
            throw new WrongOperandTypeException("NOT accepts only booleans");
        }

        $valueSet = $this->arg1->value;
        $frameSet = $this->arg1->frame;
        
        ProgramFlow::getFrame($frameSet)->setData($valueSet, DataType::BOOL, !$this->arg2->getValue());

        return 0;
    }
}

class InstructionInt2Char extends Instruction
{
    public function execute(): int{
        /*
        INT2CHAR ⟨var⟩ ⟨symb⟩ Převod celého čísla na znak
        Číselná hodnota ⟨symb⟩ je dle Unicode převedena na znak, který tvoří jednoznakový řetězec
        přiřazený do ⟨var⟩. Není-li ⟨symb⟩ validní ordinální hodnota znaku v Unicode (viz funkce mb_chr
        v PHP 8), dojde k chybě 58.
        */
        $this->checkArgs();
        if ($this->arg1 === null || $this->arg2 === null || $this->arg3 !== null) {
            throw new XMLStructureException("Missing argument");
        }

        $symbol1_type = $this->arg2->getType();
        if ($symbol1_type !== DataType::INT) {
            throw new WrongOperandTypeException("INT2CHAR accepts only integers");
        }

        $symbol1_value = $this->arg2->getValue();
        $converted = mb_chr($symbol1_value);
        if ($converted === false) {
            throw new StringOperationException("Invalid ordinal value");
        }

        $valueSet = $this->arg1->value;
        $frameSet = $this->arg1->frame;

        ProgramFlow::getFrame($frameSet)->setData($valueSet, DataType::STRING, $converted);
    
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
    public function __construct(InstructionData $instructionData, private FileInputReader $stdin)
    {
        parent::__construct($instructionData);

    }

    public function execute(): int{
        $this->checkArgs();
        if ($this->arg1 === null || $this->arg2 === null || $this->arg3 !== null) {
            throw new XMLStructureException("Missing argument");
        }
        
        $type = $this->arg2->getValue();
        $valueSet = $this->arg1->value;
        $frameSet = $this->arg1->frame;

        if ($type === DataType::INT) {
            $input = $this->stdin->readInt();
        }

        else if ($type === DataType::BOOL) {
            $input = $this->stdin->readBool();
        }

        else if ($type === DataType::STRING) {
            $input = $this->stdin->readString();
        }
        else {
            $input = false;
        }

        if ($input === false) {
            ProgramFlow::getFrame($frameSet)->setData($valueSet, DataType::NIL, 'nil');
            return 0;
        }

        ProgramFlow::getFrame($frameSet)->setData($valueSet, $type, $input);
        return 0;
    }
}

class InstructionWrite extends Instruction
{
    public function __construct(InstructionData $instructionData, private StreamWriter $stdout)
    {
        parent::__construct($instructionData);

    }

    public function execute(): int{
        $this->checkArgs();       
        if ($this->arg1 === null || $this->arg2 !== null || $this->arg3 !== null) {
            throw new XMLStructureException("Missing argument");
        }
        
        $value = $this->arg1->getValue();
        $type = $this->arg1->getType();

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
                    return chr((int)$matches[1]);
                },
                $value
            ));
        }
        else if ($type === DataType::TYPE) {
            $this->stdout->writeString($value);
        }

        return 0;
    }
}



#=========== Strings ===========

class InstructionConcat extends Instruction
{
    public function execute(): int{
        $this->checkArgs();
        if ($this->arg1 === null || $this->arg2 === null || $this->arg3 === null) {
            throw new XMLStructureException("Missing argument");
        }

        $symbol1_value = $this->arg2->getValue();
        $symbol1_type = $this->arg2->getType();

        $symbol2_value = $this->arg3->getValue();
        $symbol2_type = $this->arg3->getType();

        if ($symbol1_type !== DataType::STRING || $symbol2_type !== DataType::STRING) {
            throw new WrongOperandTypeException("Cannot concatenate other types than strings");
        }

        $valueSet = $this->arg1->value;
        $frameSet = $this->arg1->frame;

        ProgramFlow::getFrame($frameSet)->setData($valueSet, DataType::STRING, $symbol1_value . $symbol2_value);
        
        return 0;
    }
}

class InstructionStrlen extends Instruction
{
    public function execute(): int{
        $this->checkArgs();
        if ($this->arg1 === null || $this->arg2 === null || $this->arg3 !== null) {
            throw new XMLStructureException("Missing argument");
        }
        $symbol1_type = $this->arg2->getType();
        if ($symbol1_type !== DataType::STRING) {
            throw new WrongOperandTypeException("STRLEN accepts only strings");
        }

        $symbol1_vlaue = $this->arg2->getValue();

        $valueSet = $this->arg1->value;
        $frameSet = $this->arg1->frame;

        ProgramFlow::getFrame($frameSet)->setData($valueSet, DataType::INT, strlen($symbol1_vlaue));
        
        return 0;
    }
}

class InstructionGetChar extends Instruction
{
    public function execute(): int{
        $this->checkArgs();
        if ($this->arg1 === null || $this->arg2 === null || $this->arg3 === null) {
            throw new XMLStructureException("Missing argument");
        }

        $symbol1_type = $this->arg2->getType();
        if ($symbol1_type !== DataType::STRING) {
            throw new WrongOperandTypeException("Cannot get char from other type than string");
        }
        $symbol2_type = $this->arg3->getType();
        if ($symbol2_type !== DataType::INT) {
            throw new WrongOperandTypeException("Cannot index with other type than integer");
        }

        $symbol1_value = $this->arg2->getValue();
        $symbol2_value = $this->arg3->getValue();

        if ($symbol2_value < 0 || $symbol2_value >= strlen($symbol1_value)) {
            throw new StringOperationException("Index out of range");
        }

        $valueSet = $this->arg1->value;
        $frameSet = $this->arg1->frame;

        ProgramFlow::getFrame($frameSet)->setData($valueSet, DataType::STRING, $symbol1_value[$symbol2_value]);

        return 0;
    }
}

class InstructionSetChar extends Instruction
{
    public function execute(): int{
        $this->checkArgs();
        if ($this->arg1 === null || $this->arg2 === null || $this->arg3 === null) {
            throw new XMLStructureException("Missing argument");
        }

        $symbol1_type = $this->arg2->getType();
        if ($symbol1_type !== DataType::INT) {
            throw new WrongOperandTypeException("Cannot index with other type than integer");
        }

        $symbol2_type = $this->arg3->getType();
        if ($symbol2_type !== DataType::STRING) {
            throw new WrongOperandTypeException("Cannot set char with other type than string");
        }

        $symbol1_value = $this->arg2->getValue();
        $symbol2_value = $this->arg3->getValue();

        $valueSet = $this->arg1->value;
        $frameSet = $this->arg1->frame;

        $symbol1_value = (int)$symbol1_value;
        if ($symbol1_value < 0) {
            throw new StringOperationException("Index out of range");
        }

        $symbol2_value = (string)$symbol2_value;
        if ($symbol1_value >= strlen($symbol2_value)) {
            throw new StringOperationException("Index out of range");
        }

        $symbol2_value[$symbol1_value] = $symbol2_value;

        ProgramFlow::getFrame($frameSet)->setData($valueSet, DataType::STRING, $symbol2_value);

        return 0;
    }
}




#=========== Types ===========

class InstructionType extends Instruction
{
    public function execute(): int{
        $this->checkArgs();
        if ($this->arg1 === null || $this->arg2 === null || $this->arg3 !== null) {
            throw new XMLStructureException("Missing argument");
        }
        
        $type = $this->arg2->getType();
        $valueSet = $this->arg1->value;
        if (!$type) {
            $valueSet = "";
        }
        
        $frameSet = $this->arg1->frame; 
        
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
        $this->checkArgs();
        if ($this->arg1 === null || $this->arg2 !== null || $this->arg3 !== null) {
            throw new XMLStructureException("Missing argument");
        }
        ProgramFlow::jumpTo($this->arg1->value);
        return 0;
    }
}

class InstructionJumpIfEQ extends Instruction
{
    public function execute(): int{
        $this->checkArgs();
        if ($this->arg1 === null || $this->arg2 === null || $this->arg3 === null) {
            throw new XMLStructureException("Missing argument");
        }
        $label = $this->arg1->value;

        $symbol1_value = $this->arg2->getValue();
        $symbol1_type = $this->arg2->getType();

        $symbol2_value = $this->arg3->getValue();
        $symbol2_type = $this->arg3->getType();    

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
        $this->checkArgs();
        if ($this->arg1 === null || $this->arg2 === null || $this->arg3 === null) {
            throw new XMLStructureException("Missing argument");
        }
        $label = $this->arg1->value;

        $symbol1_value = $this->arg2->getValue();
        $symbol1_type = $this->arg2->getType();

        $symbol2_value = $this->arg3->getValue();
        $symbol2_type = $this->arg3->getType();    

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
        if ($this->arg1 === null || $this->arg2 !== null || $this->arg3 !== null) {
            throw new XMLStructureException("Missing argument");
        }

        if ($this->arg1->getType() !== DataType::INT) {
            throw new WrongOperandTypeException("EXIT accepts only integers");
        }

        if ($this->arg1->getValue() < 0 || $this->arg1->getValue() > 9) {
            throw new OperandValueException("EXIT accepts only integers in range 0-9");
        }

        ProgramFlow::exit($this->arg1->getValue());

        return 0;
    }
}



#=========== Debug ===========

class InstructionDprint extends Instruction
{
    public function __construct(InstructionData $instructionData, private StreamWriter $stderr)
    {
        parent::__construct($instructionData);
    }

    public function execute(): int{
        $this->checkArgs();
        if ($this->arg1 === null || $this->arg2 !== null || $this->arg3 !== null) {
            throw new XMLStructureException("Missing argument");
        }
        
        $value = $this->arg1->getValue();
        $type = $this->arg1->getType();

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
                    return chr((int)$matches[1]);
                },
                $value
            ));
        }
        return 0;
    }
}

class InstructionBreak extends Instruction
{
    public function __construct(InstructionData $instructionData, private StreamWriter $stderr)
    {
        parent::__construct($instructionData);
    }

    public function execute(): int{
        $this->stderr->writeString("Break");
        $this->stderr->writeString("Position in program: " . ProgramFlow::getPointer());
        return 0;
    }
}
