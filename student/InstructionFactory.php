<?php

namespace IPP\Student;

use IPP\Student\Exception\XMLStructureException;
use IPP\Core\Interface\InputReader;
use IPP\Core\Interface\OutputWriter;

require_once "Instruction.php";

class InstructionFactory
{
    private InputReader $stdin;
    private OutputWriter $stdout;
    private OutputWriter $stderr;

    public function __construct(InputReader $stdin, OutputWriter $stdout, OutputWriter $stderr)
    {
        $this->stdin = $stdin;
        $this->stdout = $stdout;
        $this->stderr = $stderr;
    }


    /**
     * @throws XMLStructureException
     */
    public function createInstruction(InstructionData $instructionData): Instruction{
        $opcode = $instructionData->opcode;
        return match (strtoupper($opcode)) {
            OP_codes::MOVE => new InstructionMove($instructionData),
            OP_codes::CREATEFRAME => new InstructionCreateFrame($instructionData),
            OP_codes::PUSHFRAME => new InstructionPushFrame($instructionData),
            OP_codes::POPFRAME => new InstructionPopFrame($instructionData),
            OP_codes::DEFVAR => new InstructionDefVar($instructionData),
            OP_codes::CALL => new InstructionCall($instructionData),
            OP_codes::RETURN => new InstructionReturn($instructionData),
            OP_codes::PUSHS => new InstructionPushs($instructionData),
            OP_codes::POPS => new InstructionPops($instructionData),
            OP_codes::ADD => new InstructionAdd($instructionData),
            OP_codes::SUB => new InstructionSub($instructionData),
            OP_codes::MUL => new InstructionMul($instructionData),
            OP_codes::IDIV => new InstructionIDiv($instructionData),
            OP_codes::LT => new InstructionLt($instructionData),
            OP_codes::GT => new InstructionGt($instructionData),
            OP_codes::EQ => new InstructionEq($instructionData),
            OP_codes::AND => new InstructionAnd($instructionData),
            OP_codes::OR => new InstructionOr($instructionData),
            OP_codes::NOT => new InstructionNot($instructionData),
            OP_codes::INT2CHAR => new InstructionInt2Char($instructionData),
            OP_codes::STRI2INT => new InstructionStri2Int($instructionData),
            OP_codes::READ => new InstructionRead($instructionData, $this->stdin),
            OP_codes::WRITE => new InstructionWrite($instructionData, $this->stdout),
            OP_codes::CONCAT => new InstructionConcat($instructionData),
            OP_codes::STRLEN => new InstructionStrlen($instructionData),
            OP_codes::GETCHAR => new InstructionGetChar($instructionData),
            OP_codes::SETCHAR => new InstructionSetChar($instructionData),
            OP_codes::TYPE => new InstructionType($instructionData),
            OP_codes::LABEL => new InstructionLabel($instructionData),
            OP_codes::JUMP => new InstructionJump($instructionData),
            OP_codes::JUMPIFEQ => new InstructionJumpIfEQ($instructionData),
            OP_codes::JUMPIFNEQ => new InstructionJumpIfNEQ($instructionData),
            OP_codes::EXIT => new InstructionExit($instructionData),
            OP_codes::DPRINT => new InstructionDprint($instructionData, $this->stderr),
            OP_codes::BREAK => new InstructionBreak($instructionData, $this->stderr),
            default => throw new XMLStructureException("Unknown opcode: $opcode"),
        };
    }
}


/**
 * Enum of opcodes
 */
class OP_codes {
    const string MOVE = "MOVE";
    const string CREATEFRAME = "CREATEFRAME";
    const string PUSHFRAME = "PUSHFRAME";
    const string POPFRAME = "POPFRAME";
    const string DEFVAR = "DEFVAR";
    const string CALL = "CALL";
    const string RETURN = "RETURN";
    const string PUSHS = "PUSHS";
    const string POPS = "POPS";
    const string ADD = "ADD";
    const string SUB = "SUB";
    const string MUL = "MUL";
    const string IDIV = "IDIV";
    const string LT = "LT";
    const string GT = "GT";
    const string EQ = "EQ";
    const string AND = "AND";
    const string OR = "OR";
    const string NOT = "NOT";
    const string INT2CHAR = "INT2CHAR";
    const string STRI2INT = "STRI2INT";
    const string READ = "READ";
    const string WRITE = "WRITE";
    const string CONCAT = "CONCAT";
    const string STRLEN = "STRLEN";
    const string GETCHAR = "GETCHAR";
    const string SETCHAR = "SETCHAR";
    const string TYPE = "TYPE";
    const string LABEL = "LABEL";
    const string JUMP = "JUMP";
    const string JUMPIFEQ = "JUMPIFEQ";
    const string JUMPIFNEQ = "JUMPIFNEQ";
    const string EXIT = "EXIT";
    const string DPRINT = "DPRINT";
    const string BREAK = "BREAK";
}

