<?php

namespace IPP\Student;

class Frame 
{
    private array $data;

    public function __construct()
    {
        $this->data = [];
    }

    public function setData(string $key, $value): void
    {
        $this->data[$key] = $value;
    }

    public function getData(string $key)
    {
        return $this->data[$key] ?? null;
    }

    public function getAllData(): array
    {
        return $this->data;
    }
}