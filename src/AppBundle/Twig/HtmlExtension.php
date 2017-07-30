<?php

namespace AppBundle\Twig;

use Codeages\Biz\Framework\DataStructure\UniquePriorityQueue;

class HtmlExtension extends \Twig_Extension
{
    protected $scripts = array();

    protected $csses = array();

    protected $container;

    public function __construct($container)
    {
        $this->container = $container;
        $this->scripts = new UniquePriorityQueue();
        $this->csses = new UniquePriorityQueue();
    }

    public function getFunctions()
    {
        $options = array('is_safe' => array('html'));

        return array(
            new \Twig_SimpleFunction('script', array($this, 'script')),
            new \Twig_SimpleFunction('css', array($this, 'css')),
            new \Twig_SimpleFunction('select_options', array($this, 'selectOptions'), $options),
            new \Twig_SimpleFunction('radios', array($this, 'radios'), $options),
            new \Twig_SimpleFunction('checkboxs', array($this, 'checkboxs'), $options),
            new \Twig_SimpleFunction('filepath', array($this, 'getFilePath')),
            new \Twig_SimpleFunction('fileurl', array($this, 'getFileUrl')),
            new \Twig_SimpleFunction('setting', array($this, 'getSetting')),
            new \Twig_SimpleFunction('form_csrf_token', array($this, 'rendorFormCsrfToken'), array('is_safe' => array('html'))),
        );
    }

    public function script($paths = null, $priority = 0)
    {
        if (empty($paths)) {
            return $this->scripts;
        }

        if (!is_array($paths)) {
            $paths = array($paths);
        }

        foreach ($paths as $path) {
            $this->scripts->insert($path, $priority);
        }
    }

    public function css($paths = null, $priority = 0)
    {
        if (empty($paths)) {
            return $this->csses;
        }

        if (!is_array($paths)) {
            $paths = array($paths);
        }

        foreach ($paths as $path) {
            $this->csses->insert($path, $priority);
        }
    }

    public function selectOptions($choices, $selected = null, $empty = null)
    {
        $html = '';

        if (!is_null($empty)) {
            if (is_array($empty)) {
                foreach ($empty as $key => $value) {
                    $html .= "<option value=\"{$key}\">{$value}</option>";
                }
            } else {
                $html .= "<option value=\"\">{$empty}</option>";
            }
        }

        foreach ($choices as $value => $name) {
            if ($selected == $value) {
                $html .= "<option value=\"{$value}\" selected=\"selected\">{$name}</option>";
            } else {
                $html .= "<option value=\"{$value}\">{$name}</option>";
            }
        }

        return $html;
    }

    public function radios($name, $choices, $checked = null, $disable = null)
    {
        $html = '';

        foreach ($choices as $value => $label) {
            if ($checked == $value) {
                $html .= "<label><input type=\"radio\" name=\"{$name}\" value=\"{$value}\" {$disable} checked=\"checked\"> {$label}</label>";
            } else {
                $html .= "<label><input type=\"radio\" name=\"{$name}\" value=\"{$value}\" {$disable}> {$label}</label>";
            }
        }

        return $html;
    }

    public function checkboxs($name, $choices, $checkeds = array())
    {
        $html = '';

        if (!is_array($checkeds)) {
            $checkeds = array($checkeds);
        }

        foreach ($choices as $value => $label) {
            if (in_array($value, $checkeds)) {
                $html .= "<label><input type=\"checkbox\" name=\"{$name}[]\" value=\"{$value}\" checked=\"checked\"> {$label}</label>";
            } else {
                $html .= "<label><input type=\"checkbox\" name=\"{$name}[]\" value=\"{$value}\"> {$label}</label>";
            }
        }

        return $html;
    }

    public function getSetting($name)
    {
        return $this->getSettingService()->get($name);
    }

    public function getFileUrl($path, $default = '')
    {
        return $this->getFilePublicPath($path, $default, true);
    }

    public function getFilePath($path, $default = '')
    {
        return $this->getFilePublicPath($path, $default, false);
    }

    private function getFilePublicPath($uri, $default, $absolute = false)
    {
        if (empty($uri)) {
            if (empty($default)) {
                // hardcode a default picture
                $default = 'default.jpg';
            }

            return $this->container->get('templating.helper.assets')->getUrl('assets/img/default/'.$default);
        }

        if (strpos($uri, 'http://') !== false || strpos($uri, 'https://') !== false) {
            return $uri;
        }

        $path = '';

        if (strpos($uri, '://')) {
            $parsed = $this->getUploadFileService()->parseFileUri($uri);

            if ($parsed['scope'] == 'public') {
                $path = $parsed['path'];
            }
        } else {
            $path = $uri;
        }

        $path = $this->container->getParameter('upload.public_url_path').'/'.$path;

        if ($absolute) {
            $request = $this->container->get('request');
            $path = $request->getSchemeAndHttpHost().$path;
        }

        return $path;
    }

    public function rendorFormCsrfToken($id = null)
    {
        if (empty($id)) {
            $id = $this->container->getParameter('app.csrf.token_id.default');
        }

        $token = $this->container->get('security.csrf.token_manager')->getToken($id)->getValue();

        return sprintf('<input type="hidden" name="%s" value="%s">', $this->container->getParameter('app.csrf.token_form_name'), $token);
    }

    /**
     * @return \Biz\File\Service\UploadFileService
     */
    protected function getUploadFileService()
    {
        $biz = $this->container->get('biz');

        return $biz->service('File:UploadFileService');
    }

    /**
     * @return \Biz\System\Service\SettingService
     */
    protected function getSettingService()
    {
        return $this->container->get('biz')->service('System:SettingService');
    }

    public function getName()
    {
        return 'app_twig_extension_html';
    }
}
