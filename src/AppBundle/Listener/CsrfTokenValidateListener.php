<?php

namespace AppBundle\Listener;

use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\HttpFoundation\JsonResponse;
use Biz\Common\Exception\RuntimeException;
use Symfony\Component\DependencyInjection\ContainerInterface;

class CsrfTokenValidateListener
{
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();

        if ($event->getRequestType() !== HttpKernelInterface::MASTER_REQUEST) {
            return;
        }

        if (!in_array($request->getMethod(), ['POST', 'PUT', 'DELETE'])) {
            return;
        }

        $name = $this->container->getParameter('app.csrf.token_form_name');

        if ($request->isXmlHttpRequest()) {
            $token = $request->headers->get('X-CSRF-Token');
        } else {
            $token = $request->request->get($name, '');
        }
        $request->request->remove($name);

        if (!$this->isCsrfTokenValid($this->container->getParameter('app.csrf.token_id.default'), $token)) {
            $error = array(
                'code' => 5,
                'message' => '页面已过期，请重新提交数据!',
            );

            if ($request->isXmlHttpRequest()) {
                $response = new JsonResponse($error, 400);
            } else {
                $response = $this->container->get('templating')->renderResponse('error.html.twig', $error);
            }

            $event->setResponse($response);
        }
    }

    protected function isCsrfTokenValid($id, $token)
    {
        if (!$this->container->has('security.csrf.token_manager')) {
            throw new RuntimeException('CSRF protection is not enabled in your application.');
        }

        return $this->container->get('security.csrf.token_manager')->isTokenValid(new CsrfToken($id, $token));
    }
}
