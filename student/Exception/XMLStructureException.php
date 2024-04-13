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


class XMLStructureException extends IPPException
{
    public function __construct(string $message = "Wrong XML structure", ?Throwable $previous = null)
    {
        parent::__construct($message, ReturnCode::INVALID_SOURCE_STRUCTURE, $previous, false);
    }
}
