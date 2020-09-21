<?php


namespace App\EventListener;

use ApiPlatform\Core\Exception\InvalidArgumentException;
use ApiPlatform\Core\Exception\ItemNotFoundException;
use ApiPlatform\Core\Bridge\Symfony\Validator\Exception\ValidationException;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\EventListener\ErrorListener as BaseExceptionListener;
use Symfony\Component\Serializer\Exception\UnexpectedValueException;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;

class ExceptionListener extends BaseExceptionListener
{
    public function onKernelException(ExceptionEvent $event)
    {
        $request = $event->getRequest();
        // Normalize exceptions only for routes managed by API Platform
        if ('html' === $request->getRequestFormat('') ||
            (!$request->attributes->has('_api_resource_class') &&
                !$request->attributes->has('_api_respond') &&
                !$request->attributes->has('_graphql'))) {
            return;
        }

        $exception = $event->getThrowable();
        /*$reflection = new \ReflectionClass($exception);
        var_dump($reflection->getName());
        return;*/

        if ($exception instanceof UnexpectedValueException &&
            $exception->getPrevious() instanceof ItemNotFoundException) {
            $violations = new ConstraintViolationList(
                [
                    new ConstraintViolation(
                        $exception->getMessage(),
                        null,
                        [],
                        '',
                        '',
                        ''
                    )
                ]
            );

            $e = new ValidationException($violations);
            $event->setThrowable($e);

            return;
        }
    }
}