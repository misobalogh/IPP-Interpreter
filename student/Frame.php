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

    public function keyExists(string $key): bool
    {
        return array_key_exists($key, $this->data);
    }
}


class FrameType
{
    const GLOBAL = "GF";
    const LOCAL = "LF";
    const TEMPORARY = "TF";

    public static function validFrameTypes(): array
    {
        return [
            self::GLOBAL,
            self::LOCAL,
            self::TEMPORARY,
        ];
    }

    public static function isFrameType(string $frameType): bool
    {
        return in_array($frameType, self::validFrameTypes());
    }

}
