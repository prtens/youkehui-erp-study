<?php

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

abstract class BaseCommand extends ContainerAwareCommand
{
    protected function createService($alias)
    {
        $biz = $this->getBiz();

        return $biz->service($alias);
    }

    protected function getBiz()
    {
        return $this->getContainer()->get('biz');
    }
}
