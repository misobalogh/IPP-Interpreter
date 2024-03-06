<?php

namespace IPP\Student;

use IPP\Core\Exception\InternalErrorException;

require_once "Instruction.php";


class InstructionFactory
{
    public function createInstruction(string $opcode, array $args): Instruction{
        switch ($opcode) {
            case OP_codes::MOVE:
                return new InstructionMove($args);
            case OP_codes::CREATEFRAME:
                return new InstructionCreateFrame($args);
            case OP_codes::PUSHFRAME:
                return new InstructionPushFrame($args);
            case OP_codes::POPFRAME:
                return new InstructionPopFrame($args);
            case OP_codes::DEFVAR:
                return new InstructionDefVar($args);
            case OP_codes::CALL:
                return new InstructionCall($args);
            case OP_codes::RETURN:
                return new InstructionReturn($args);
            case OP_codes::PUSHS:
                return new InstructionPushs($args);
            case OP_codes::POPS:
                return new InstructionPops($args);
            case OP_codes::ADD:
                return new InstructionAdd($args);
            case OP_codes::SUB:
                return new InstructionSub($args);
            case OP_codes::MUL:
                return new InstructionMul($args);
            case OP_codes::IDIV:
                return new InstructionIDiv($args);
            case OP_codes::LT:
                return new InstructionLt($args);
            case OP_codes::GT:
                return new InstructionGt($args);
            case OP_codes::EQ:
                return new InstructionEq($args);
            case OP_codes::AND:
                return new InstructionAnd($args);
            case OP_codes::OR:
                return new InstructionOr($args);
            case OP_codes::NOT:
                return new InstructionNot($args);
            case OP_codes::INT2CHAR:
                return new InstructionInt2Char($args);
            case OP_codes::STRI2INT:
                return new InstructionStri2Int($args);
            case OP_codes::READ:
                return new InstructionRead($args);
            case OP_codes::WRITE:
                return new InstructionWrite($args);
            case OP_codes::CONCAT:
                return new InstructionConcat($args);
            case OP_codes::STRLEN:
                return new InstructionStrlen($args);
            case OP_codes::GETCHAR:
                return new InstructionGetChar($args);
            case OP_codes::SETCHAR:
                return new InstructionSetChar($args);
            case OP_codes::TYPE:
                return new InstructionType($args);
            case OP_codes::LABEL:
                return new InstructionLabel($args);
            case OP_codes::JUMP:
                return new InstructionJump($args);
            case OP_codes::JUMPIFEQ:
                return new InstructionJumpIfEQ($args);
            case OP_codes::JUMPIFNEQ:
                return new InstructionJumpIfNEQ($args);
            case OP_codes::EXIT:
                return new InstructionExit($args);
            case OP_codes::DPRINT:
                return new InstructionDprint($args);
            case OP_codes::BREAK:
                return new InstructionBreak($args);
            default:
                throw new InternalErrorException("Unknown opcode: $opcode");
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

