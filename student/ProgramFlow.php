<?php

namespace IPP\Student;

use IPP\Student\Exception\SemanticErrorException;


class ProgramFlow
{
    private static int $instructionPointer;
    private static array $instructionList;
    private static array $labels;
    private static array $frames;
    // private static array $dataStack;
    private static array $callStack;
    private static Frame $globalFrame;
    private static Frame $temporaryFrame;

    public static function initialize($instructionList): void
    {
        self::$instructionPointer = 0;
        self::$instructionList = $instructionList;
        self::$labels = self::findLabels();
        self::$frames = [];
        self::$callStack = [];
        // global frame
        self::$globalFrame = new Frame();
        self::pushFrame(self::$globalFrame);
    }

    // IP
    public static function getPointer(): int
    {
        return self::$instructionPointer;
    }

    public static function increment(): void
    {
        self::$instructionPointer++;
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
        return self::$labels[$label];
    }

    private static function findLabels(): array
    {
        $labels = []; 
        foreach (self::$instructionList as $index => $instruction) {
            if ($instruction->opcode == "LABEL") {
                $labels[$instruction->args[0]->value] = $index;
            }
        }
        return $labels;        
    }
    
    // FRAMES
    public static function getFrame(string $frameType): Frame{
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

    public static function popFrame(): Frame
    {
        return array_pop(self::$frames);
    }

    public static function getCurrentFrame(): Frame
    {
        return end(self::$frames);
    }

    public static function addToLocalFrame(string $key, ?string $type, $value): void
    {
        $currentFrame = self::getCurrentFrame();
        if (array_key_exists($key, $currentFrame->getAllData())) {
            throw new SemanticErrorException("Rededfinition of variable $key");
        }
        else {
            $currentFrame->setData($key, $type, $value);
        }
    }

    // public static function isGlobalFrame(): bool
    // {
    //     return count(self::$frames) == 1;
    // }

    public static function addToGlobalFrame(string $key, ?string $type, $value): void
    {
        if (array_key_exists($key, self::$globalFrame->getAllData())) {
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

    public static function addToTemporaryFrame(string $key, ?string $type, $value): void
    {
        if (array_key_exists($key, self::$temporaryFrame->getAllData())) {
            throw new SemanticErrorException("Rededfinition of variable $key");
        }
        else {
            self::$temporaryFrame->setData($key, $type, $value);
        }
    }

    public static function getFromTemporaryFrame(string $key)
    {
        return self::$temporaryFrame->getData($key);
    }    

    public static function clearTemporaryFrame(): void
    {
        self::$temporaryFrame = null;
    }
}
