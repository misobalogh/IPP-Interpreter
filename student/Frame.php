<?php

namespace IPP\Student;

class Frame 
{
    private array $data;

    public function __construct()
    {
        $this->data = [];
    }

    public function setData(string $key, ?string $type, $value): void
    {
        $this->data[$key] = [
            "type" => $type,
            "value" => $value
        ];
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

