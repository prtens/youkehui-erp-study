<?php

namespace AppBundle\Twig;

use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Yaml;
use Biz\Common\Exception\InvalidArgumentException;

class DictExtension extends \Twig_Extension
{
    protected $container;

    protected $dicts = array();

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('dict', array($this, 'getDict')),
        );
    }

    public function getDict($name, $key = null)
    {
        $dicts = $this->findAllDicts();

        if ($key === null) {
            if (!isset($dicts[$name])) {
                throw new InvalidArgumentException(sprintf('dict named #%s not exist', $name));
            }

            return $dicts[$name];
        }

        if (!isset($dicts[$name][$key])) {
            throw new InvalidArgumentException(sprintf('dict named #%s, key #%s not exist', $name, $key));
        }

        return $dicts[$name][$key];
    }

    protected function findAllDicts()
    {
        if (empty($this->dicts)) {
            $yamls = $this->findDictYamls();
            $dicts = array();
            foreach ($yamls as $yaml) {
                $parsedDicts = Yaml::parse(file_get_contents($yaml));
                $dicts = array_merge($dicts, $parsedDicts);
            }

            $this->dicts = $dicts;
        }

        return $this->dicts;
    }

    protected function findDictYamls()
    {
        $yamls = array();

        $biz = $this->container->get('biz');
        $rootDir = $biz['root_directory'];

        $finder = new Finder();
        $finder->files()->in($rootDir.'/src/*Bundle/Resources/config/')->name('dict.yml');

        foreach ($finder as $dir) {
            $filepath = $dir->getRealPath();
            if (file_exists($filepath)) {
                $yamls[] = $filepath;
            }
        }

        return $yamls;
    }

    public function getName()
    {
        return 'app_twig_extension_dict';
    }
}
