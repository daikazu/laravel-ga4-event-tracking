<?php

namespace Daikazu\GA4\Exceptions;

use Exception;

class MissingClientIdException extends Exception
{
    protected $message = 'Missing Client ID. Please set a client ID or use the provided blade directive.';
}
