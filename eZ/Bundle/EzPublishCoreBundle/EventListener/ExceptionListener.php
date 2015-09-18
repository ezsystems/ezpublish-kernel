<?php
/**
 * This file is part of the eZ Publish kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */
namespace eZ\Bundle\EzPublishCoreBundle\EventListener;

use eZ\Publish\API\Repository\Exceptions\BadStateException;
use eZ\Publish\API\Repository\Exceptions\ForbiddenException;
use eZ\Publish\API\Repository\Exceptions\NotFoundException;
use eZ\Publish\API\Repository\Exceptions\UnauthorizedException;
use eZ\Publish\Core\Base\Exceptions\TranslatableExceptionInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Translation\TranslatorInterface;
use Exception;

class ExceptionListener implements EventSubscriberInterface
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::EXCEPTION => ['onKernelException', -90],
        ];
    }

    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $exception = $event->getException();

        if ($exception instanceof NotFoundException) {
            $event->setException(new NotFoundHttpException($this->getTranslatedMessage($exception), $exception));
        } elseif ($exception instanceof UnauthorizedException || $exception instanceof ForbiddenException) {
            $event->setException(new AccessDeniedHttpException($this->getTranslatedMessage($exception), $exception));
        } elseif ($exception instanceof BadStateException) {
            $event->setException(new BadRequestHttpException($this->getTranslatedMessage($exception), $exception));
        } elseif ($exception instanceof TranslatableExceptionInterface) {
            $event->setException(
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
        if ($exception instanceof TranslatableExceptionInterface) {
            $message = $this->translator->trans($exception->getMessageTemplate(), $exception->getParameters(), 'repository_exceptions');
        }

        return $message;
    }
}
