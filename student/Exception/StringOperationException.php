<?php
/*
Michal Balogh, xbalog06
IPP - project 2
VUT FIT 2024
*/
namespace IPP\Student\Exception;

use IPP\Core\Exception\IPPException;
use IPP\Core\ReturnCode;
use Throwable;


class StringOperationException extends IPPException
{
    public function __construct(string $message = "String operation error", ?Throwable $previous = null)
    {
        parent::__construct($message, ReturnCode::STRING_OPERATION_ERROR, $previous, false);
    }
}
