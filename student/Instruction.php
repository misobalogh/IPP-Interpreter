<?php
/*
Michal Balogh, xbalog06
IPP - project 2
VUT FIT 2024
*/


namespace IPP\Student;

use IPP\Core\Interface\InputReader;
use IPP\Core\Interface\OutputWriter;
use IPP\Student\Exception\FrameAccessException;
use IPP\Student\Exception\OperandValueException;
use IPP\Student\Exception\SemanticErrorException;
use IPP\Student\Exception\StringOperationException;
use IPP\Student\Exception\UndefinedVariableException;
use IPP\Student\Exception\ValueException;
use IPP\Student\Exception\WrongOperandTypeException;
use IPP\Student\Exception\XMLStructureException;
use IPP\Student\Types\DataType;

#=========== Abstract class for instructions ===========


/**
 * Abstract class for instructions
 */
abstract class Instruction
{
    protected ?InstructionArgument $arg1;
    protected ?InstructionArgument $arg2;
    protected ?InstructionArgument $arg3;

    /**
     * @param InstructionData $instructionData
     */
    public function __construct(InstructionData $instructionData)
    {
        $this->arg1 = $instructionData->arg1;
        $this->arg2 = $instructionData->arg2;
        $this->arg3 = $instructionData->arg3;
    }

    /**
     * @throws UndefinedVariableException
     * 
     * Check if all arguments are defined in the frame
     */
    final public function checkArgs() : void
    {
        foreach ([$this->arg1, $this->arg2, $this->arg3] as $arg) {
            if ($arg !== null && !$arg->isDefined() && $arg->type === DataType::VAR) {
                throw new UndefinedVariableException("Undefined variable");
            }
        }
    }

    /**
     * Execute instruction
     */
    abstract public function execute(): void;
}


#=========== Memory frames, function calls ===========

class InstructionMove extends Instruction
{
    /**
     * @throws UndefinedVariableException
     * @throws XMLStructureException
     * @throws FrameAccessException
     */
    public function execute(): void
    {
        $this->checkArgs();
        if ($this->arg1 == null || $this->arg2 == null || $this->arg3 !== null) {
            throw new XMLStructureException("Missing argument");
        }

        $valueGet = $this->arg2->getValue();
        $typeGet = $this->arg2->getType();
        
        $valueSet = $this->arg1->value;
        $frameSet = $this->arg1->frame;

        ProgramFlow::GetFrame($frameSet)->setData($valueSet, $typeGet, $valueGet);

    }
}


class InstructionCreateFrame extends Instruction
{
    public function execute(): void
    {
        ProgramFlow::deleteTemporaryFrame();
        $frame = new Frame();
        ProgramFlow::setTemporaryFrame($frame);
    }
}

class InstructionPushFrame extends Instruction
{
    /**
     * @throws FrameAccessException
     */
    public function execute(): void
    {
        $temporaryFrame = ProgramFlow::getTemporaryFrame();
        if ($temporaryFrame === null) {
            throw new FrameAccessException("Cannot push frame from empty stack");
        }

        ProgramFlow::pushFrame($temporaryFrame);
        ProgramFlow::deleteTemporaryFrame();

    }
}

class InstructionPopFrame extends Instruction
{
    /**
     * @throws FrameAccessException
     */
    public function execute(): void
    {
        $frame = ProgramFlow::popFrame();
        if ($frame === null) {
            throw new FrameAccessException("Cannot pop frame from empty stack");
        }

        ProgramFlow::setTemporaryFrame($frame);

    }
}

class InstructionDefVar extends Instruction
{
    /**
     * @return void
     * @throws FrameAccessException
     * @throws SemanticErrorException
     * @throws XMLStructureException
     */
    public function execute(): void
    {
        if ($this->arg1 === null || $this->arg2 !== null || $this->arg3 !== null) {
            throw new XMLStructureException("Missing argument");
        }
        ProgramFlow::addToFrame($this->arg1->frame, $this->arg1->value, null, null);
    }
}

class InstructionCall extends Instruction
{
    /**
     * @return void
     * @throws SemanticErrorException
     * @throws UndefinedVariableException
     * @throws XMLStructureException
     */
    public function execute(): void
    {
        $this->checkArgs();
        if ($this->arg1 === null || $this->arg2 !== null || $this->arg3 !== null) {
            throw new XMLStructureException("Missing argument");
        }

        ProgramFlow::pushToCallStack(ProgramFlow::getPointer());
        ProgramFlow::jumpTo($this->arg1->value);

    }
}

class InstructionReturn extends Instruction
{
    /**
     * @throws ValueException
     */
    public function execute(): void
    {
        $pointer = ProgramFlow::popFromCallStack();
        if ($pointer === null) {
            throw new ValueException("Cannot return from empty call stack");
        }

        ProgramFlow::setPointer($pointer);

    }
}



#=========== Data stack ===========

class InstructionPushs extends Instruction
{
    /**
     * @throws UndefinedVariableException
     * @throws XMLStructureException|FrameAccessException
     */
    public function execute(): void
    {
        $this->checkArgs();

        if ($this->arg1 === null || $this->arg2 !== null || $this->arg3 !== null) {
            throw new XMLStructureException("Missing argument");
        }

        $data = [
            "value" => $this->arg1->getValue(),
            "type" => $this->arg1->getType()
        ];

        ProgramFlow::pushToDataStack($data);

    }
}

class InstructionPops extends Instruction
{
    /**
     * @throws UndefinedVariableException
     * @throws XMLStructureException
     * @throws ValueException
     * @throws FrameAccessException
     */
    public function execute(): void
    {
        $this->checkArgs();

        if ($this->arg1 === null || $this->arg2 !== null || $this->arg3 !== null) {
            throw new XMLStructureException("Missing argument");
        }

        $value = ProgramFlow::popFromDataStack();
        if ($value === null) {
            throw new ValueException("Cannot pop from empty data stack");
        }

        $valueSet = $this->arg1->value;
        $frameSet = $this->arg1->frame;
        $type = $value["type"];
        $value = $value["value"];
        
        ProgramFlow::getFrame($frameSet)->setData($valueSet, $type, $value);


    }
}



#=========== Arithmetic, relational, boolean and conversion ===========

class InstructionAdd extends Instruction
{
    /**
     * @throws UndefinedVariableException
     * @throws XMLStructureException
     * @throws WrongOperandTypeException
     * @throws FrameAccessException
     */
    public function execute(): void
    {
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
    }
}

class InstructionSub extends Instruction
{
    /**
     * @throws UndefinedVariableException
     * @throws XMLStructureException
     * @throws WrongOperandTypeException
     * @throws FrameAccessException
     */
    public function execute(): void
    {
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

    }
}

class InstructionMul extends Instruction
{
    /**
     * @throws UndefinedVariableException
     * @throws XMLStructureException
     * @throws WrongOperandTypeException
     * @throws FrameAccessException
     */
    public function execute(): void
    {
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

    }
}

class InstructionIDiv extends Instruction
{
    /**
     * @throws UndefinedVariableException
     * @throws XMLStructureException
     * @throws WrongOperandTypeException
     * @throws FrameAccessException
     * @throws OperandValueException
     */
    public function execute(): void
    {
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

    }
}

class InstructionLt extends Instruction
{
    /**
     * @throws UndefinedVariableException
     * @throws XMLStructureException
     * @throws WrongOperandTypeException
     * @throws FrameAccessException
     */
    public function execute(): void
    {
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

    }
}

class InstructionGt extends Instruction
{
    /**
     * @throws UndefinedVariableException
     * @throws XMLStructureException
     * @throws WrongOperandTypeException
     * @throws FrameAccessException
     */
    public function execute(): void
    {
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

    }
}

class InstructionEq extends Instruction
{
    /**
     * @throws UndefinedVariableException
     * @throws XMLStructureException
     * @throws WrongOperandTypeException
     * @throws FrameAccessException
     */
    public function execute(): void
    {
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

    }
}

class InstructionAnd extends Instruction
{
    /**
     * @throws UndefinedVariableException
     * @throws XMLStructureException
     * @throws WrongOperandTypeException
     * @throws FrameAccessException
     */
    public function execute(): void
    {
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

    }
}

class InstructionOr extends Instruction
{
    /**
     * @throws UndefinedVariableException
     * @throws XMLStructureException
     * @throws WrongOperandTypeException
     * @throws FrameAccessException
     */
    public function execute(): void
    {
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

    }
}   

class InstructionNot extends Instruction
{
    /**
     * @throws UndefinedVariableException
     * @throws XMLStructureException
     * @throws WrongOperandTypeException
     * @throws FrameAccessException
     */
    public function execute(): void
    {
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

    }
}

class InstructionInt2Char extends Instruction
{
    /**
     * @throws UndefinedVariableException
     * @throws XMLStructureException
     * @throws StringOperationException
     * @throws WrongOperandTypeException
     * @throws FrameAccessException
     */
    public function execute(): void
    {
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
        if (!$converted) {
            throw new StringOperationException("Invalid ordinal value");
        }

        $valueSet = $this->arg1->value;
        $frameSet = $this->arg1->frame;

        ProgramFlow::getFrame($frameSet)->setData($valueSet, DataType::STRING, $converted);

    }
}

class InstructionStri2Int extends Instruction
{
    /**
     * @throws UndefinedVariableException
     * @throws StringOperationException
     * @throws XMLStructureException
     * @throws WrongOperandTypeException
     * @throws FrameAccessException
     */
    public function execute(): void
    {
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

        $converted = mb_ord($symbol1_value[$symbol2_value]);
        if (!$converted) {
            throw new StringOperationException("Error during conversion");
        }

        $valueSet = $this->arg1->value;
        $frameSet = $this->arg1->frame;

        ProgramFlow::getFrame($frameSet)->setData($valueSet, DataType::INT, $converted);

    }
}



#=========== I/O ===========

class InstructionRead extends Instruction
{
    public function __construct(InstructionData $instructionData, private readonly InputReader $stdin)
    {
        parent::__construct($instructionData);
    }

    /**
     * @throws UndefinedVariableException
     * @throws XMLStructureException
     * @throws FrameAccessException
     */
    public function execute(): void
    {
        $this->checkArgs();
        if ($this->arg1 === null || $this->arg2 === null || $this->arg3 !== null) {
            throw new XMLStructureException("Missing argument");
        }
        
        if (!$this->arg2->isType()) {
            throw new XMLStructureException("Invalid argument type");
        }

        $type = $this->arg2->getValue();

        $valueSet = $this->arg1->value;
        $frameSet = $this->arg1->frame;

        $input = null;
        if (strcasecmp($type, DataType::INT) === 0) {
            $input = $this->stdin->readInt();
        } elseif (strcasecmp($type, DataType::BOOL) === 0) {
            $input = $this->stdin->readBool();
        } elseif (strcasecmp($type, DataType::STRING) === 0) {
            $input = $this->stdin->readString();
        }

        if ($input === null) {
            ProgramFlow::getFrame($frameSet)->setData($valueSet, DataType::NIL, 'nil');
        }
        else {
            ProgramFlow::getFrame($frameSet)->setData($valueSet, $type, $input);
        }
    }
}


class InstructionWrite extends Instruction
{
    public function __construct(InstructionData $instructionData, private readonly OutputWriter $stdout)
    {
        parent::__construct($instructionData);
    }

    /**
     * @throws UndefinedVariableException
     * @throws XMLStructureException|FrameAccessException
     */
    public function execute(): void
    {
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
    }
}



#=========== Strings ===========

class InstructionConcat extends Instruction
{
    /**
     * @throws UndefinedVariableException
     * @throws XMLStructureException
     * @throws WrongOperandTypeException
     * @throws FrameAccessException
     */
    public function execute(): void
    {
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

    }
}

class InstructionStrlen extends Instruction
{
    /**
     * @throws UndefinedVariableException
     * @throws XMLStructureException
     * @throws WrongOperandTypeException
     * @throws FrameAccessException
     */
    public function execute(): void
    {
        $this->checkArgs();
        if ($this->arg1 === null || $this->arg2 === null || $this->arg3 !== null) {
            throw new XMLStructureException("Missing argument");
        }
        $symbol1_type = $this->arg2->getType();
        if ($symbol1_type !== DataType::STRING) {
            throw new WrongOperandTypeException("STRLEN accepts only strings");
        }

        $symbol1_value = $this->arg2->getValue();

        $valueSet = $this->arg1->value;
        $frameSet = $this->arg1->frame;

        ProgramFlow::getFrame($frameSet)->setData($valueSet, DataType::INT, strlen($symbol1_value));

    }
}

class InstructionGetChar extends Instruction
{
    /**
     * @throws UndefinedVariableException
     * @throws XMLStructureException
     * @throws StringOperationException
     * @throws WrongOperandTypeException
     * @throws FrameAccessException
     */
    public function execute(): void
    {
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

    }
}

class InstructionSetChar extends Instruction
{
    /**
     * @throws UndefinedVariableException
     * @throws XMLStructureException
     * @throws StringOperationException
     * @throws WrongOperandTypeException
     * @throws FrameAccessException
     */
    public function execute(): void
    {
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
        if (strlen($symbol2_value) > 1) {
            $symbol2_value = $symbol2_value[0];
        }

        $valueSet = $this->arg1->value;
        $frameSet = $this->arg1->frame;
        $stringToModify = $this->arg1->getValue();

        $symbol1_value = (int)$symbol1_value;
        $symbol2_value = (string)$symbol2_value;
        if ($symbol1_value < 0 || $symbol1_value >= strlen($stringToModify)) {
            throw new StringOperationException("Index out of range");
        } 

        $stringToModify[$symbol1_value] = $symbol2_value;

        ProgramFlow::getFrame($frameSet)->setData($valueSet, DataType::STRING, $stringToModify);

    }
}




#=========== Types ===========

class InstructionType extends Instruction
{
    /**
     * @throws UndefinedVariableException
     * @throws XMLStructureException
     * @throws FrameAccessException
     */
    public function execute(): void
    {
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


    }
}



#=========== Flow control ===========

class InstructionLabel extends Instruction
{
    public function execute(): void
    {
    }
}

class InstructionJump extends Instruction
{
    /**
     * @return void
     * @throws SemanticErrorException
     * @throws UndefinedVariableException
     * @throws XMLStructureException
     */
    public function execute(): void
    {
        $this->checkArgs();
        if ($this->arg1 === null || $this->arg2 !== null || $this->arg3 !== null) {
            throw new XMLStructureException("Missing argument");
        }
        ProgramFlow::jumpTo($this->arg1->value);
    }
}

class InstructionJumpIfEQ extends Instruction
{
    /**
     * @return void
     * @throws FrameAccessException
     * @throws SemanticErrorException
     * @throws UndefinedVariableException
     * @throws WrongOperandTypeException
     * @throws XMLStructureException
     */
    public function execute(): void
    {
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

    }
}

class InstructionJumpIfNEQ extends Instruction
{
    /**
     * @return void
     * @throws SemanticErrorException
     * @throws FrameAccessException
     * @throws UndefinedVariableException
     * @throws WrongOperandTypeException
     * @throws XMLStructureException
     */
    public function execute(): void
    {
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
    }
}

class InstructionExit extends Instruction
{
    /**
     * @throws UndefinedVariableException
     * @throws XMLStructureException
     * @throws WrongOperandTypeException
     * @throws OperandValueException|FrameAccessException
     */
    public function execute(): void
    {
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

    }
}



#=========== Debug ===========

class InstructionDprint extends Instruction
{
    public function __construct(InstructionData $instructionData, private readonly OutputWriter $stderr)
    {
        parent::__construct($instructionData);
    }

    /**
     * @throws UndefinedVariableException
     * @throws XMLStructureException|FrameAccessException
     */
    public function execute(): void
    {
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
    }
}

class InstructionBreak extends Instruction
{
    public function __construct(InstructionData $instructionData, private readonly OutputWriter $stderr)
    {
        parent::__construct($instructionData);
    }

    public function execute(): void
    {
        $this->stderr->writeString("Break");
        $this->stderr->writeString("Position in program: " . ProgramFlow::getPointer());
    }
}
