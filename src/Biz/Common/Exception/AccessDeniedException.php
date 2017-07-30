<?php

namespace Biz\Common\Exception;

class AccessDeniedException extends ServiceException
{
    public function __construct($message = 'Access Denied', $code = 403, $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
