<?php

namespace AppBundle\Extension;

use Biz\Common\Exception\NotFoundException;

class ExtensionManager
{
    protected $extensions = array();

    public function add(ExtensionInterface $extension)
    {
        $this->extensions[$extension->getName()] = $extension;
    }

    public function get($extensionName)
    {
        if (empty($this->extensions[$extensionName])) {
            throw new NotFoundException(sprintf('Extension #%s not found', $extensionName));
        }

        return $this->extensions[$extensionName];
    }
}
