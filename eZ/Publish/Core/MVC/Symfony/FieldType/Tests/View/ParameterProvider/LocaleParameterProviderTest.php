<?php

/**
 * File containing the ParameterProviderTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\FieldType\Tests\View\ParameterProvider;

use eZ\Publish\Core\MVC\Symfony\FieldType\View\ParameterProvider\LocaleParameterProvider;
use eZ\Publish\API\Repository\Values\Content\Field;
use eZ\Publish\Core\MVC\Symfony\Locale\LocaleConverterInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\ParameterBag;

class LocaleParameterProviderTest extends TestCase
{
    public function providerForTestGetViewParameters()
    {
        return [
            [true, 'fr_FR'],
            [false, 'hr_HR'],
        ];
    }

    /**
     * @dataProvider providerForTestGetViewParameters
     */
    public function testGetViewParameters($hasRequestLocale, $expectedLocale)
    {
        $field = new Field(['languageCode' => 'cro-HR']);
        $parameterProvider = new LocaleParameterProvider($this->getLocaleConverterMock());
        $parameterProvider->setRequestStack($this->getRequestStackMock($hasRequestLocale));
        $this->assertSame(
            ['locale' => $expectedLocale],
            $parameterProvider->getViewParameters($field)
        );
    }

    protected function getRequestStackMock($hasLocale)
    {
        $requestStack = new RequestStack();
        $parameterBagMock = $this->createMock(ParameterBag::class);

        $parameterBagMock->expects($this->any())
            ->method('has')
            ->with($this->equalTo('_locale'))
            ->will($this->returnValue($hasLocale));

        $parameterBagMock->expects($this->any())
            ->method('get')
            ->with($this->equalTo('_locale'))
            ->will($this->returnValue('fr_FR'));

        $requestMock = $this->createMock(Request::class);
        $requestMock->attributes = $parameterBagMock;

        $requestStack->push($requestMock);

        return $requestStack;
    }

    protected function getLocaleConverterMock()
    {
        $mock = $this->createMock(LocaleConverterInterface::class);

        $mock->expects($this->any())
            ->method('convertToPOSIX')
            ->with($this->equalTo('cro-HR'))
            ->will($this->returnValue('hr_HR'));

        return $mock;
    }
}
