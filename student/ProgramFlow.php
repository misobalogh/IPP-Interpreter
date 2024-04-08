<?php

namespace IPP\Student;

use IPP\Student\Exception\SemanticErrorException;


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

    public static int $executedInstructionCount = 0;

    /**
     * @param InstructionData[] $instructionList
     */
    public static function initialize($instructionList): void
    {
        self::$instructionPointer = 0;
        self::$instructionList = $instructionList;
        self::$labels = self::findLabels();
        self::$frames = [];
        self::$callStack = [];
        self::$temporaryFrame = null;
        // global frame
        self::$globalFrame = new Frame();
        // self::pushFrame(self::$globalFrame);
    }

    // IP
    public static function getPointer(): int
    {
        return self::$instructionPointer;
    }

    public static function increment(): void
    {
        self::$instructionPointer++;
        self::$executedInstructionCount++;
    }


    //  LABELS
    public static function jumpTo(string $label): void
    {
        self::$instructionPointer = self::getLabel($label);
    }

    private static function getLabel(string $label): int
    {
        if (!array_key_exists($label, self::$labels)) {
            throw new SemanticErrorException("Label $label not found");
        }
        return  (int)self::$labels[$label];
    }

    public static function labelExists(string $label) : bool {
        if (self::getLabel($label) == null) {
            return false;
        }
        return true;
    }

    /**
     * @return string[]
     */
    private static function findLabels(): array
    {
        $labels = []; 
        foreach (self::$instructionList as $index => $instruction) {
            if ($instruction->opcode == "LABEL") {
                $labels[$instruction->arg1->value] = $index;
            }
        }
        return $labels;        
    }
    
    // FRAMES
    public static function getFrame(string $frameType): ?Frame{
        if ($frameType == FrameType::GLOBAL) {
            return self::$globalFrame;
        }
        else if ($frameType == FrameType::TEMPORARY) {
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
     */
    public static function addToFrame(string $frameType, string $key, ?string $type, $value): void
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
        array_push(self::$frames, $frame);
    }

    public static function popFrame(): ?Frame
    {
        return array_pop(self::$frames);
    }

    public static function getCurrentFrame(): Frame
    {
        return end(self::$frames);
    }

    /**
     * @param string $key
     * @param string|null $type
     * @param mixed $value
     */
    public static function addToLocalFrame(string $key, ?string $type, $value): void
    {
        $currentFrame = self::getCurrentFrame();
        if ($currentFrame->keyExists($key)) {
            throw new SemanticErrorException("Rededfinition of variable $key");
        }
        else {
            $currentFrame->setData($key, $type, $value);
        }
    }

    /**
     * @param string $key
     * @param string|null $type
     * @param mixed $value
     */
    public static function addToGlobalFrame(string $key, ?string $type, $value): void
    {
        if (self::$globalFrame->keyExists($key)) {
            throw new SemanticErrorException("Rededfinition of variable $key");
        }
        else {
            self::$globalFrame->setData($key, $type, $value);
        }
    }
    
    public static function getGlobalFrame() : Frame
    {
        return self::$globalFrame;
    }   

    public static function createTemporaryFrame(): void
    {
        self::$temporaryFrame = new Frame();
    }

    /**
     * @param string $key
     * @param string|null $type
     * @param mixed $value
     */
    public static function addToTemporaryFrame(string $key, ?string $type, $value): void
    {
        if (self::$temporaryFrame->keyExists($key)) {
            throw new SemanticErrorException("Rededfinition of variable $key");
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

    public static function continue(): bool
    {
        return self::$instructionPointer < count(self::$instructionList);
    }

    public static function exit(int $value): void
    {
        exit($value);
    }
}
