<?php

namespace AppBundle\Extension;

interface ExtensionInterface
{
    /**
     * load data of the extension should load.
     *
     * @param array $arguments
     *
     * @return array
     */
    public function payload(array $arguments);

    /**
     * a unique name of extension.
     *
     * @return string
     */
    public function getName();
}
