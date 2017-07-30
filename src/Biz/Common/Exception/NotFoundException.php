<?php

namespace Biz\Common\Exception;

class NotFoundException extends ServiceException
{
    public function __construct($message, $code = 404, $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
