<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;

class DefaultController extends BaseController
{
    public function dashboardAction(Request $request)
    {
        // return $this->render('AppBundle:default:dashboard.html.twig');
        return $this->redirect('goods/group/product');
    }
}
