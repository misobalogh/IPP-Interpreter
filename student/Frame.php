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


    /**
     * @return array<string, mixed>>
     */
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

    /**
     * @return array<string>
     */
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
