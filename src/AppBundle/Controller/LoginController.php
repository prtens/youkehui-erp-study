<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;

class LoginController extends BaseController
{
    public function indexAction(Request $request)
    {
        $user = $this->getCurrentUser();
        if ($user->isLogin()) {
            return $this->redirect($this->generateUrl('dashboard'));
        }

        $authenticationUtils = $this->get('security.authentication_utils');

        $error = $authenticationUtils->getLastAuthenticationError();

        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('AppBundle:login:index.html.twig', array(
            'last_username' => $lastUsername,
            'error' => $error,
        ));
    }

    /**
     * @return \Biz\User\Service\UserService
     */
    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }
}
