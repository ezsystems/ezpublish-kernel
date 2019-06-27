<?php
/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Tests\EventListener;

use Exception;
use eZ\Bundle\EzPublishCoreBundle\EventListener\ExceptionListener;
use eZ\Publish\Core\Base\Exceptions\BadStateException;
use eZ\Publish\Core\Base\Exceptions\ContentFieldValidationException;
use eZ\Publish\Core\Base\Exceptions\ContentTypeFieldDefinitionValidationException;
use eZ\Publish\Core\Base\Exceptions\ContentTypeValidationException;
use eZ\Publish\Core\Base\Exceptions\ContentValidationException;
use eZ\Publish\Core\Base\Exceptions\ForbiddenException;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentType;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentValue;
use eZ\Publish\Core\Base\Exceptions\LimitationValidationException;
use eZ\Publish\Core\Base\Exceptions\MissingClass;
use eZ\Publish\Core\Base\Exceptions\NotFound\FieldTypeNotFoundException;
use eZ\Publish\Core\Base\Exceptions\NotFound\LimitationNotFoundException;
use eZ\Publish\Core\Base\Exceptions\NotFoundException;
use eZ\Publish\Core\Base\Exceptions\UnauthorizedException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class ExceptionListenerTest extends TestCase
{
    /** @var \PHPUnit\Framework\MockObject\MockObject|\Symfony\Component\Translation\TranslatorInterface */
    private $translator;

    /** @var ExceptionListener */
    private $listener;

    protected function setUp()
    {
        parent::setUp();
        $this->translator = $this->createMock(TranslatorInterface::class);
        $this->listener = new ExceptionListener($this->translator);
    }

    public function testGetSubscribedEvents()
    {
        self::assertSame(
            [KernelEvents::EXCEPTION => ['onKernelException', 10]],
            ExceptionListener::getSubscribedEvents()
        );
    }

    /**
     * @param Exception $exception
     * @return GetResponseForExceptionEvent
     */
    private function generateExceptionEvent(Exception $exception)
    {
        return new GetResponseForExceptionEvent(
            $this->createMock(HttpKernelInterface::class),
            new Request(),
            'master',
            $exception
        );
    }

    public function testNotFoundException()
    {
        $messageTemplate = 'some message template';
        $translationParams = ['some' => 'thing'];
        $exception = new NotFoundException('foo', 'bar');
        $exception->setMessageTemplate($messageTemplate);
        $exception->setParameters($translationParams);
        $event = $this->generateExceptionEvent($exception);

        $translatedMessage = 'translated message';
        $this->translator
            ->expects($this->once())
            ->method('trans')
            ->with($messageTemplate, $translationParams)
            ->willReturn($translatedMessage);

        $this->listener->onKernelException($event);
        $convertedException = $event->getException();
        self::assertInstanceOf(NotFoundHttpException::class, $convertedException);
        self::assertSame($exception, $convertedException->getPrevious());
        self::assertSame($translatedMessage, $convertedException->getMessage());
    }

    public function testUnauthorizedException()
    {
        $messageTemplate = 'some message template';
        $translationParams = ['some' => 'thing'];
        $exception = new UnauthorizedException('foo', 'bar');
        $exception->setMessageTemplate($messageTemplate);
        $exception->setParameters($translationParams);
        $event = $this->generateExceptionEvent($exception);

        $translatedMessage = 'translated message';
        $this->translator
            ->expects($this->once())
            ->method('trans')
            ->with($messageTemplate, $translationParams)
            ->willReturn($translatedMessage);

        $this->listener->onKernelException($event);
        $convertedException = $event->getException();
        self::assertInstanceOf(AccessDeniedException::class, $convertedException);
        self::assertSame($exception, $convertedException->getPrevious());
        self::assertSame($translatedMessage, $convertedException->getMessage());
    }

    /**
     * @dataProvider badRequestExceptionProvider
     *
     * @param Exception|\eZ\Publish\Core\Base\Translatable $exception
     */
    public function testBadRequestException(Exception $exception)
    {
        $messageTemplate = 'some message template';
        $translationParams = ['some' => 'thing'];
        $exception->setMessageTemplate($messageTemplate);
        $exception->setParameters($translationParams);
        $event = $this->generateExceptionEvent($exception);

        $translatedMessage = 'translated message';
        $this->translator
            ->expects($this->once())
            ->method('trans')
            ->with($messageTemplate, $translationParams)
            ->willReturn($translatedMessage);

        $this->listener->onKernelException($event);
        $convertedException = $event->getException();
        self::assertInstanceOf(BadRequestHttpException::class, $convertedException);
        self::assertSame($exception, $convertedException->getPrevious());
        self::assertSame($translatedMessage, $convertedException->getMessage());
    }

    public function badRequestExceptionProvider()
    {
        return [
            [new BadStateException('foo', 'bar')],
            [new InvalidArgumentException('foo', 'bar')],
            [new InvalidArgumentType('foo', 'bar')],
            [new InvalidArgumentValue('foo', 'bar')],
        ];
    }

    /**
     * @dataProvider otherExceptionProvider
     *
     * @param Exception|\eZ\Publish\Core\Base\Translatable $exception
     */
    public function testOtherRepositoryException(Exception $exception)
    {
        $messageTemplate = 'some message template';
        $translationParams = ['some' => 'thing'];
        $exception->setMessageTemplate($messageTemplate);
        $exception->setParameters($translationParams);
        $event = $this->generateExceptionEvent($exception);

        $translatedMessage = 'translated message';
        $this->translator
            ->expects($this->once())
            ->method('trans')
            ->with($messageTemplate, $translationParams)
            ->willReturn($translatedMessage);

        $this->listener->onKernelException($event);
        $convertedException = $event->getException();
        self::assertInstanceOf(HttpException::class, $convertedException);
        self::assertSame($exception, $convertedException->getPrevious());
        self::assertSame(get_class($exception) . ': ' . $translatedMessage, $convertedException->getMessage());
        self::assertSame(Response::HTTP_INTERNAL_SERVER_ERROR, $convertedException->getStatusCode());
    }

    public function otherExceptionProvider()
    {
        return [
            [new ForbiddenException('foo')],
            [new LimitationValidationException([])],
            [new MissingClass('foo')],
            [new ContentValidationException('foo')],
            [new ContentTypeValidationException('foo')],
            [new ContentFieldValidationException([])],
            [new ContentTypeFieldDefinitionValidationException([])],
            [new FieldTypeNotFoundException('foo')],
            [new LimitationNotFoundException('foo')],
        ];
    }

    public function testUntouchedException()
    {
        $exception = new \RuntimeException('foo');
        $event = $this->generateExceptionEvent($exception);
        $this->translator
            ->expects($this->never())
            ->method('trans');

        $this->listener->onKernelException($event);
        self::assertSame($exception, $event->getException());
    }
}
