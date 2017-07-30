<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

abstract class BaseController extends Controller
{
    protected function extractFormArrayFields(array &$fields, array $keys)
    {
        $extracted = array();

        foreach ($keys as $key) {
            foreach ($fields[$key] as $index => $value) {
                $extracted[$index][$key] = $value;
            }
            unset($fields[$key]);
        }

        return $extracted;
    }

    public function getCurrentUser()
    {
        $biz = $this->getBiz();

        return $biz['user'];
    }

    protected function createJsonCleanResponse($data = '')
    {
        return new JsonResponse($data);
    }

    protected function createJsonResponse($data = '', $message = '', $errorCode = 0)
    {
        return new JsonResponse(array(
            'data' => $data,
            'message' => $message,
            'error_code' => $errorCode === false ? 1 : $errorCode,
        ));
    }

    protected function createService($alias)
    {
        $biz = $this->getBiz();

        return $biz->service($alias);
    }

    protected function getBiz()
    {
        return $this->get('biz');
    }
}
