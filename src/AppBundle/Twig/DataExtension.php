<?php

namespace AppBundle\Twig;

class DataExtension extends \Twig_Extension
{
    protected $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('data', array($this, 'getData')),
            new \Twig_SimpleFunction('parameter', array($this, 'getParameter')),
        );
    }

    public function getData($name, $arguments)
    {
        $arguments['data_tag_name'] = $name;

        return $this->container->get('extension.manager')->get('data_tag')->payload($arguments);
    }

    public function getParameter($name)
    {
        return $this->container->getParameter($name);
    }

    public function getName()
    {
        return 'app_twig_extension_data';
    }
}
