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


class ValueException extends IPPException
{
    public function __construct(string $message = "Value error", ?Throwable $previous = null)
    {
        parent::__construct($message, ReturnCode::VALUE_ERROR, $previous, false);
    }
}
