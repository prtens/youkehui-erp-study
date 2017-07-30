<?php

namespace AppBundle\Listener;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\Debug\Exception\FlattenException;

class ExceptionListener
{
    private $logger;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $exception = $event->getException();
        $request = $event->getRequest();

        if ($request->isXmlHttpRequest()) {
            $error = array(
                'data' => false,
                'message' => $exception->getMessage(),
                'error_code' => $exception->getCode(),
            );

            $biz = $this->container->get('biz');
            if ($biz['debug']) {
                $error['previous'] = $this->getPreviousErrors($exception);
            }

            $event->setResponse(new JsonResponse($error));
        }
    }

    protected function getPreviousErrors($exception)
    {
        $previousErrors = array();

        if (!$exception instanceof FlattenException) {
            $exception = FlattenException::create($exception);
        }

        $flags = PHP_VERSION_ID >= 50400 ? ENT_QUOTES | ENT_SUBSTITUTE : ENT_QUOTES;

        $count = count($exception->getAllPrevious());
        $total = $count + 1;
        foreach ($exception->toArray() as $position => $e) {
            $previous = array();

            $ind = $count - $position + 1;

            $previous['message'] = "{$ind}/{$total} {$e['class']}: {$e['message']}";
            $previous['trace'] = array();

            foreach ($e['trace'] as $position => $trace) {
                $content = sprintf('%s. ', $position + 1);
                if ($trace['function']) {
                    $content .= sprintf('at %s%s%s(%s)', $trace['class'], $trace['type'], $trace['function'], '...args...');
                }
                if (isset($trace['file']) && isset($trace['line'])) {
                    $content .= sprintf(' in %s line %d', htmlspecialchars($trace['file'], $flags, 'UTF-8'), $trace['line']);
                }

                $previous['trace'][] = $content;
            }

            $previousErrors[] = $previous;
        }

        return $previousErrors;
    }
}
