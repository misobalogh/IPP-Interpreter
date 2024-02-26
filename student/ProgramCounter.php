<?php

namespace IPP\Student;

class ProgramCounter
{
    private int $counter;

    public function __construct()
    {
        $this->counter = 0;
    }

    public function increment(): void
    {
        $this->counter++;
    }

    public function getCounter(): int
    {
        return $this->counter;
    }

    public function jumpTo(int $position): void
    {
        $this->counter = $position;
    }
}