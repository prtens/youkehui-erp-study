<?php

namespace AppBundle\Extension\DataTag;

use Codeages\Biz\Framework\Context\BizAware;

abstract class BaseDataTag extends BizAware implements DataTagInterface
{
    abstract public function getData(array $arguments);

    /**
     * @return Biz\User\CurrentUser
     */
    public function getCurrentUser()
    {
        return $this->biz['user'];
    }

    /**
     * @return \Biz\BaseService
     */
    protected function createService($alias)
    {
        return $this->biz->service($alias);
    }
}
