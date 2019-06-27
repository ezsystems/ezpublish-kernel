<?php

/**
 * File containing the LocaleListenerTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Tests\EventListener;

use eZ\Bundle\EzPublishCoreBundle\EventListener\LocaleListener;
use eZ\Publish\Core\MVC\ConfigResolverInterface;
use eZ\Publish\Core\MVC\Symfony\Locale\LocaleConverterInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LocaleListenerTest extends TestCase
{
    /** @var LocaleConverterInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $localeConverter;

    /** @var ConfigResolverInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $configResolver;

    /** @var ConfigResolverInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $requestStack;

    protected function setUp()
    {
        parent::setUp();
        $this->container = $this->createMock(ContainerInterface::class);
        $this->localeConverter = $this->createMock(LocaleConverterInterface::class);
        $this->configResolver = $this->createMock(ConfigResolverInterface::class);

        $this->requestStack = new RequestStack();
        $parameterBagMock = $this->createMock(ParameterBag::class);
        $parameterBagMock->expects($this->never())->method($this->anything());

        $requestMock = $this->createMock(Request::class);
        $requestMock->attributes = $parameterBagMock;

//        $requestMock->expects($this->any())
//            ->method('__get')
//            ->with($this->equalTo('attributes'))
//            ->will($this->returnValue($parameterBagMock));

        $this->requestStack->push($requestMock);
    }

    /**
     * @dataProvider onKernelRequestProvider
     */
    public function testOnKernelRequest(array $configuredLanguages, array $convertedLocalesValueMap, $expectedLocale)
    {
        $this->configResolver
            ->expects($this->once())
            ->method('getParameter')
            ->with('languages')
            ->will($this->returnValue($configuredLanguages));

        $this->localeConverter
            ->expects($this->atLeastOnce())
            ->method('convertToPOSIX')
            ->will(
                $this->returnValueMap($convertedLocalesValueMap)
            );

        $defaultLocale = 'en';
        $localeListener = new LocaleListener($this->requestStack, $defaultLocale);
        $localeListener->setConfigResolver($this->configResolver);
        $localeListener->setLocaleConverter($this->localeConverter);

        $request = new Request();
        $localeListener->onKernelRequest(
            new GetResponseEvent(
                $this->createMock(HttpKernelInterface::class),
                $request,
                HttpKernelInterface::MASTER_REQUEST
            )
        );
        $this->assertSame($expectedLocale, $request->attributes->get('_locale'));
    }

    public function onKernelRequestProvider()
    {
        return [
            [
                ['eng-GB'],
                [
                    ['eng-GB', 'en_GB'],
                ],
                'en_GB',
            ],
            [
                ['eng-DE'],
                [
                    ['eng-DE', null],
                ],
                // Default locale
                null,
            ],
            [
                ['fre-CA', 'fre-FR', 'eng-US'],
                [
                    ['fre-CA', null],
                    ['fre-FR', 'fr_FR'],
                ],
                'fr_FR',
            ],
            [
                ['fre-CA', 'fre-FR', 'eng-US'],
                [
                    ['fre-CA', null],
                    ['fre-FR', null],
                    ['eng-US', null],
                ],
                null,
            ],
            [
                ['esl-ES', 'eng-GB'],
                [
                    ['esl-ES', 'es_ES'],
                    ['eng-GB', 'en_GB'],
                ],
                'es_ES',
            ],
        ];
    }
}
