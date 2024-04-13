<?php

namespace IPP\Student;

class Frame 
{
    /**
     * @var array<string, mixed> $data
     */
    private array $data;

    public function __construct()
    {
        $this->data = [];
    }

    public function setData(string $key, ?string $type, mixed $value): void
    {
        $this->data[$key] = [
            "type" => $type,
            "value" => $value
        ];
    }

    public function getData(string $key) : mixed
    {
        return $this->data[$key] ?? null;
    }


    public function keyExists(string $key): bool
    {
        return array_key_exists($key, $this->data);
    }
}
