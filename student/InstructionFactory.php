<?php

namespace IPP\Student;

use IPP\Core\Exception\InternalErrorException;
use IPP\Student\Exception\XMLStructureException;
use IPP\Core\StreamWriter;
use IPP\Core\FileInputReader;
use IPP\Core\FileSourceReader;


require_once "Instruction.php";


class InstructionFactory
{
    private FileInputReader $stdin;
    private StreamWriter $stdout;
    private StreamWriter $stderr;

    public function __construct(FileInputReader $stdin, StreamWriter $stdout, StreamWriter $stderr)
    {
        $this->stdin = $stdin;
        $this->stdout = $stdout;
        $this->stderr = $stderr;
    }


    public function createInstruction(InstructionData $instructionData): Instruction{
        $opcode = $instructionData->opcode;
        switch (strtoupper($opcode)) {
            case OP_codes::MOVE:
                return new InstructionMove($instructionData);
            case OP_codes::CREATEFRAME:
                return new InstructionCreateFrame($instructionData);
            case OP_codes::PUSHFRAME:
                return new InstructionPushFrame($instructionData);
            case OP_codes::POPFRAME:
                return new InstructionPopFrame($instructionData);
            case OP_codes::DEFVAR:
                return new InstructionDefVar($instructionData);
            case OP_codes::CALL:
                return new InstructionCall($instructionData);
            case OP_codes::RETURN:
                return new InstructionReturn($instructionData);
            case OP_codes::PUSHS:
                return new InstructionPushs($instructionData);
            case OP_codes::POPS:
                return new InstructionPops($instructionData);
            case OP_codes::ADD:
                return new InstructionAdd($instructionData);
            case OP_codes::SUB:
                return new InstructionSub($instructionData);
            case OP_codes::MUL:
                return new InstructionMul($instructionData);
            case OP_codes::IDIV:
                return new InstructionIDiv($instructionData);
            case OP_codes::LT:
                return new InstructionLt($instructionData);
            case OP_codes::GT:
                return new InstructionGt($instructionData);
            case OP_codes::EQ:
                return new InstructionEq($instructionData);
            case OP_codes::AND:
                return new InstructionAnd($instructionData);
            case OP_codes::OR:
                return new InstructionOr($instructionData);
            case OP_codes::NOT:
                return new InstructionNot($instructionData);
            case OP_codes::INT2CHAR:
                return new InstructionInt2Char($instructionData);
            case OP_codes::STRI2INT:
                return new InstructionStri2Int($instructionData);
            case OP_codes::READ:
                return new InstructionRead($instructionData, $this->stdin);
            case OP_codes::WRITE:
                return new InstructionWrite($instructionData, $this->stdout);
            case OP_codes::CONCAT:
                return new InstructionConcat($instructionData);
            case OP_codes::STRLEN:
                return new InstructionStrlen($instructionData);
            case OP_codes::GETCHAR:
                return new InstructionGetChar($instructionData);
            case OP_codes::SETCHAR:
                return new InstructionSetChar($instructionData);
            case OP_codes::TYPE:
                return new InstructionType($instructionData);
            case OP_codes::LABEL:
                return new InstructionLabel($instructionData);
            case OP_codes::JUMP:
                return new InstructionJump($instructionData);
            case OP_codes::JUMPIFEQ:
                return new InstructionJumpIfEQ($instructionData);
            case OP_codes::JUMPIFNEQ:
                return new InstructionJumpIfNEQ($instructionData);
            case OP_codes::EXIT:
                return new InstructionExit($instructionData);
            case OP_codes::DPRINT:
                return new InstructionDprint($instructionData, $this->stderr);
            case OP_codes::BREAK:
                return new InstructionBreak($instructionData, $this->stderr);
            default:
                throw new XMLStructureException("Unknown opcode: $opcode");
        }
    }
}


/**
 * Enum of opcodes
 */
class OP_codes {
    const MOVE = "MOVE";
    const CREATEFRAME = "CREATEFRAME";
    const PUSHFRAME = "PUSHFRAME";
    const POPFRAME = "POPFRAME";
    const DEFVAR = "DEFVAR";
    const CALL = "CALL";
    const RETURN = "RETURN";
    const PUSHS = "PUSHS";
    const POPS = "POPS";
    const ADD = "ADD";
    const SUB = "SUB";
    const MUL = "MUL";
    const IDIV = "IDIV";
    const LT = "LT";
    const GT = "GT";
    const EQ = "EQ";
    const AND = "AND";
    const OR = "OR";
    const NOT = "NOT";
    const INT2CHAR = "INT2CHAR";
    const STRI2INT = "STRI2INT";
    const READ = "READ";
    const WRITE = "WRITE";
    const CONCAT = "CONCAT";
    const STRLEN = "STRLEN";
    const GETCHAR = "GETCHAR";
    const SETCHAR = "SETCHAR";
    const TYPE = "TYPE";
    const LABEL = "LABEL";
    const JUMP = "JUMP";
    const JUMPIFEQ = "JUMPIFEQ";
    const JUMPIFNEQ = "JUMPIFNEQ";
    const EXIT = "EXIT";
    const DPRINT = "DPRINT";
    const BREAK = "BREAK";
}

