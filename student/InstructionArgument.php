<?php
/*
Michal Balogh, xbalog06
IPP - project 2
VUT FIT 2024
*/


namespace IPP\Student;

use IPP\Student\Exception\FrameAccessException;
use IPP\Student\Exception\XMLStructureException;

/**
 * Class InstructionArgument
 *
 * Class for storing argument of instruction
 *
 * @property string $type
 * @property string $frame
 * @property int|bool|string $value
 */
class InstructionArgument
{
    public string $type;
    public ?string $frame;
    /** @var int|bool|string */
    public string|int|bool $value;

    /**
     * InstructionArgument constructor.
     * @param string $type
     * @param string|null $frame
     * @param bool|int|string $value
     * @throws XMLStructureException
     */
    public function __construct(string $type, ?string $frame, bool|int|string $value)
    {
        $this->type = $type;
        $this->frame = $frame;
        if ($type === Types\DataType::INT) {
            if (!is_numeric($value)) {
                throw new XMLStructureException("Invalid value for int type");
            }
            $this->value = (int)$value;
        } else if ($type === Types\DataType::BOOL) {
            $this->value = $value === "true";
        } else {
            $this->value = $value;
        }
    }

    /**
     * @throws FrameAccessException
     * 
     * Check if the argument is defined in the frame
     */
    public function isDefined(): bool
    {
        if ($this->isVar()) {
            // check if the variable is defined in the frame or if not, check if it is defined in the global frame            
            return ProgramFlow::getFrame($this->frame)?->keyExists($this->value) || ProgramFlow::getGlobalFrame()->keyExists($this->value);
        } else if ($this->isLabel()) {
            // in case of label, check if the label exists
            return ProgramFlow::labelExists($this->value);
        } else {
            return true;
        }

    }

    /**
     * @throws FrameAccessException
     * 
     * Get the value of the argument from the frame
     */
    public function getValue(): mixed
    {
        if ($this->isVar()) {
            return ProgramFlow::getFrame($this->frame)->getData($this->value)["value"];
        } else {
            return $this->value;
        }
    }

    /**
     * @throws FrameAccessException
     * 
     * Get the type of the argument from the frame
     */
    public function getType(): mixed
    {
        if ($this->isVar()) {
            return ProgramFlow::getFrame($this->frame)->getData($this->value)["type"];
        } else {
            return $this->type;
        }
    }

    public function isVar(): bool
    {
        return $this->type === Types\DataType::VAR;
    }

    public function isLabel(): bool
    {
        return $this->type === Types\DataType::LABEL;
    }

    public function isType(): bool
    {
        return $this->type === Types\DataType::TYPE;
    }
}