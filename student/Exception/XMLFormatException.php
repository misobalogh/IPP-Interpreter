<?php

namespace IPP\Student\Exception;

use IPP\Core\Exception\IPPException;
use IPP\Core\ReturnCode;
use Throwable;


class XMLFormatException extends IPPException
{
    public function __construct(string $message = "Wrong XML source file", ?Throwable $previous = null)
    {
        parent::__construct($message, ReturnCode::INVALID_XML_ERROR, $previous, false);
    }
}
