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

    public static function initialize($instructionList): void
    {
        self::$instructionPointer = 0;
        self::$instructionList = $instructionList;
        self::$labels = self::setLabels();
        self::$frames = [];
        self::$callStack = [];
        // global frame
        self::pushFrame(new Frame());
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

    private static function setLabels(): array
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

    public static function isGlobalFrame(): bool
    {
        return count(self::$frames) == 1;
    }
}
