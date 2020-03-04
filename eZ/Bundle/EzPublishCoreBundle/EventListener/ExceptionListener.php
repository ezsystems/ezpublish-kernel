<?php
/**
 * This file is part of the eZ Publish kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\EventListener;

use eZ\Publish\API\Repository\Exceptions\BadStateException;
use eZ\Publish\API\Repository\Exceptions\InvalidArgumentException;
use eZ\Publish\API\Repository\Exceptions\NotFoundException;
use eZ\Publish\API\Repository\Exceptions\UnauthorizedException;
use eZ\Publish\Core\Base\Translatable;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Exception;
use Symfony\Contracts\Translation\TranslatorInterface;

class ExceptionListener implements EventSubscriberInterface
{
    /** @var TranslatorInterface */
    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::EXCEPTION => ['onKernelException', 10],
        ];
    }

    public function onKernelException(ExceptionEvent $event)
    {
        $exception = $event->getThrowable();

        if ($exception instanceof NotFoundException) {
            $event->setThrowable(new NotFoundHttpException($this->getTranslatedMessage($exception), $exception));
        } elseif ($exception instanceof UnauthorizedException) {
            $event->setThrowable(new AccessDeniedException($this->getTranslatedMessage($exception), $exception));
        } elseif ($exception instanceof BadStateException || $exception instanceof InvalidArgumentException) {
            $event->setThrowable(new BadRequestHttpException($this->getTranslatedMessage($exception), $exception));
        } elseif ($exception instanceof Translatable) {
            $event->setThrowable(
                new HttpException(
                    Response::HTTP_INTERNAL_SERVER_ERROR,
                    get_class($exception) . ': ' . $this->getTranslatedMessage($exception),
                    $exception
                )
            );
        }
    }

    /**
     * Translates the exception message if it is translatable.
     *
     * @param Exception $exception
     *
     * @return string
     */
    private function getTranslatedMessage(Exception $exception)
    {
        $message = $exception->getMessage();
        if ($exception instanceof Translatable) {
            $message = $this->translator->trans($exception->getMessageTemplate(), $exception->getParameters(), 'repository_exceptions');
        }

        return $message;
    }
}
