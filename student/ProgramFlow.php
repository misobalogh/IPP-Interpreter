<?php
/*
Michal Balogh, xbalog06
IPP - project 2
VUT FIT 2024
*/


namespace IPP\Student;

use IPP\Student\Exception\FrameAccessException;
use IPP\Student\Exception\SemanticErrorException;
use IPP\Student\Types\FrameType;


/**
 * Class ProgramFlow
 *
 * Class for controlling the program flow,
 * including instruction pointer, labels,
 * frames and data stack
 */
class ProgramFlow
{
    private static int $instructionPointer;
    /**
     * @var InstructionData[]
     */
    private static array $instructionList;
    /**
     * @var string[]
     */
    private static array $labels;
    /**
     * @var Frame[]
     */
    private static array $frames;
    /**
     * @var Frame[]
     */
    private static array $callStack;

    private static Frame $globalFrame;
    
    private static ?Frame $temporaryFrame;

    /**
     * @var array<string, mixed>[]
     */
    private static array $dataStack;

    private static int $executedInstructionCount = 0;

    /**
     * @param InstructionData[] $instructionList
     * @throws SemanticErrorException
     * @throws SemanticErrorException
     * 
     * Initialize the program flow with the instruction list, global frame
     * and find and store all labels
     */
    public static function initialize(array $instructionList): void
    {
        self::$instructionPointer = 0;
        self::$instructionList = $instructionList;
        self::$labels = self::findLabels();
        self::$frames = [];
        self::$callStack = [];
        self::$temporaryFrame = null;
        self::$globalFrame = new Frame();
        self::$dataStack = [];
    }

    // IP - methods for controlling the instruction pointer
    public static function getPointer(): int
    {
        return self::$instructionPointer;
    }

    public static function setPointer(int $pointer): void
    {
        self::$instructionPointer = $pointer;
    }

    public static function increment(): void
    {
        self::$instructionPointer++;
        self::$executedInstructionCount++;
    }


    // CALL STACK - methods for working with call stack
    public static function pushToCallStack(int $instructionPointer): void
    {
        self::$callStack[] = $instructionPointer;
    }

    public static function popFromCallStack(): mixed
    {
        return array_pop(self::$callStack);
    }

    //  LABELS - methods for working with labels

    /**
     * @throws SemanticErrorException
     * 
     * Jump to the instruction with the given label
     */
    public static function jumpTo(string $label): void
    {
        self::$instructionPointer = self::getLabel($label);
    }

    /**
     * @throws SemanticErrorException
     * 
     * Get the index of the instruction with the given label
     */
    private static function getLabel(string $label): int
    {
        if (!array_key_exists($label, self::$labels)) {
            throw new SemanticErrorException("Label $label not found");
        }
        return  (int)self::$labels[$label];
    }

    /**
     * @throws SemanticErrorException
     */
    public static function labelExists(string $label) : bool {
        if (self::getLabel($label) == null) {
            return false;
        }
        return true;
    }

    /**
     * @return string[]
     * @throws SemanticErrorException
     * @throws SemanticErrorException
     */
    private static function findLabels(): array
    {
        $labels = []; 
        foreach (self::$instructionList as $index => $instruction) {
            if ($instruction->opcode == "LABEL") {
                if (array_key_exists($instruction->arg1->value, $labels)) {
                    throw new SemanticErrorException("Label {$instruction->arg1->value} already defined");
                }
                $labels[$instruction->arg1->value] = $index;
            }
        }
        return $labels;        
    }
    
    // FRAMES - methods for working with frames

    /**
     * @throws FrameAccessException
     * 
     * Get the frame based on the frame type
     */
    public static function getFrame(string $frameType): ?Frame{
        if ($frameType === FrameType::GLOBAL) {
            return self::$globalFrame;
        }
        else if ($frameType === FrameType::TEMPORARY) {
            if (self::$temporaryFrame === null) {
                throw new FrameAccessException("No temporary frame");
            }
            return self::$temporaryFrame;
        }
        else {
            return self::getCurrentFrame();
        }
    }


    /**
     * @param string $frameType
     * @param string $key
     * @param string|null $type
     * @param mixed $value
     * @throws FrameAccessException
     * @throws SemanticErrorException
     * 
     * Add variable to the frame based on the frame type
     */
    public static function addToFrame(string $frameType, string $key, ?string $type, mixed $value): void
    {
        if ($frameType == FrameType::GLOBAL) {
            self::addToGlobalFrame($key, $type, $value);
        }
        else if ($frameType == FrameType::TEMPORARY) {
            self::addToTemporaryFrame($key, $type, $value);
        }
        else {
            self::addToLocalFrame($key, $type, $value);
        }
    }

    public static function pushFrame(Frame $frame): void
    {
        self::$frames[] = $frame;
    }

    public static function popFrame(): ?Frame
    {
        return array_pop(self::$frames);
    }

    /**
     * @return Frame|null
     * 
     * Get the frame from the top of the stack
     */
    public static function getCurrentFrame(): ?Frame
    {
        $frameToReturn = end(self::$frames);
        if ($frameToReturn === false) {
            return null;
        }
        return $frameToReturn;
    }

    /**
     * @param string $key
     * @param string|null $type
     * @param mixed $value
     * @throws FrameAccessException
     * @throws SemanticErrorException
     */
    public static function addToLocalFrame(string $key, ?string $type, mixed $value): void
    {
        $currentFrame = self::getCurrentFrame();
        if ($currentFrame === null) {
            throw new FrameAccessException("No frame to add variable $key");
        }

        if ($currentFrame->keyExists($key)) {
            throw new SemanticErrorException("Redefinition of variable $key");
        }
        else {
            $currentFrame->setData($key, $type, $value);
        }
    }

    /**
     * @param string $key
     * @param string|null $type
     * @param mixed $value
     * @throws SemanticErrorException
     * @throws SemanticErrorException
     */
    public static function addToGlobalFrame(string $key, ?string $type, mixed $value): void
    {
        if (self::$globalFrame->keyExists($key)) {
            throw new SemanticErrorException("Redefinition of variable $key");
        }
        else {
            self::$globalFrame->setData($key, $type, $value);
        }
    }
    
    public static function getGlobalFrame() : Frame
    {
        return self::$globalFrame;
    }   

    public static function setTemporaryFrame(Frame $frame): void
    {
        self::$temporaryFrame = $frame;
    }

    public static function deleteTemporaryFrame(): void
    {
        self::$temporaryFrame = null;
    }

    public static function getTemporaryFrame(): ?Frame
    {
        return self::$temporaryFrame;
    }

    /**
     * @param string $key
     * @param string|null $type
     * @param mixed $value
     * @throws FrameAccessException
     * @throws SemanticErrorException
     */
    public static function addToTemporaryFrame(string $key, ?string $type, mixed $value): void
    {
        if (self::$temporaryFrame === null) {
            throw new FrameAccessException("No temporary frame");
        }
        if (self::$temporaryFrame->keyExists($key)) {
            throw new SemanticErrorException("Redefinition of variable $key");
        }
        else {
            self::$temporaryFrame->setData($key, $type, $value);
        }
    }

    /**
     * @param string $key
     * @return array<string, mixed>|null
     */
    public static function getFromTemporaryFrame(string $key) : ?array
    {
        return self::$temporaryFrame->getData($key);
    }    

    public static function clearTemporaryFrame(): void
    {
        self::$temporaryFrame = null;
    }

    /**
     * @return bool
     * 
     * Check if the program flow can continue
     */
    public static function continue(): bool
    {
        return self::$instructionPointer < count(self::$instructionList);
    }

    /**
     * @param int $value
     * 
     * Exit the program with the given value
     */
    public static function exit(int $value): void
    {
        exit($value);
    }


    // STACK - for working with data stack

    /**
     * @param array<string, mixed> $data
     */
    public static function pushToDataStack(array $data): void
    {
        self::$dataStack[] = $data;
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function popFromDataStack() : ?array
    {
        return array_pop(self::$dataStack);
    }
}
