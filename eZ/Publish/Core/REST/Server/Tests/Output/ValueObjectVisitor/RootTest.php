<?php

/**
 * File containing a test class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Tests\Output\ValueObjectVisitor;

use eZ\Publish\Core\REST\Common\Tests\Output\ValueObjectVisitorBaseTest;
use eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor;
use eZ\Publish\Core\REST\Server\Service\ExpressionRouterRootResourceBuilder;

class RootTest extends ValueObjectVisitorBaseTest
{
    protected function getRootResourceBuilder()
    {
        $resourceConfig = [
            'Router' => [
                'mediaType' => '',
                'href' => 'router.generate("ezpublish_rest_createContent")',
            ],
            'RouterWithAttributes' => [
                'mediaType' => 'UserRefList',
                'href' => 'router.generate("ezpublish_rest_loadUsers")',
            ],
            'TemplateRouter' => [
                'mediaType' => '',
                'href' => 'templateRouter.generate("ezpublish_rest_redirectContent", {remoteId: "{remoteId}"})',
            ],
            'TemplateRouterWithAttributes' => [
                'mediaType' => 'UserRefList',
                'href' => 'templateRouter.generate("ezpublish_rest_loadUsers", {roleId: "{roleId}"})',
            ],
        ];

        $this->addRouteExpectation('ezpublish_rest_createContent', [], '/content/objects');
        $this->addTemplatedRouteExpectation('ezpublish_rest_redirectContent', ['remoteId' => '{remoteId}'], '/content/objects');
        $this->addRouteExpectation('ezpublish_rest_loadUsers', [], '/user/users');
        $this->addTemplatedRouteExpectation('ezpublish_rest_loadUsers', ['roleId' => '{roleId}'], '/user/users{?roleId}');

        return new ExpressionRouterRootResourceBuilder($this->getRouterMock(), $this->getTemplatedRouterMock(), $resourceConfig);
    }

    /**
     * Test the Role visitor.
     *
     * @return string
     */
    public function testVisit()
    {
        $visitor = $this->getVisitor();
        $generator = $this->getGenerator();
        $rootResourceBuilder = $this->getRootResourceBuilder();

        $generator->startDocument(null);

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $rootResourceBuilder->buildRootResource()
        );

        $result = $generator->endDocument(null);

        $this->assertNotNull($result);

        return $result;
    }

    /**
     * @depends testVisit
     */
    public function testResultContainsRootElement($result)
    {
        $this->assertXMLTag(
            ['tag' => 'Root'],
            $result,
            'Invalid <Root> element.',
            false
        );
    }

    /**
     * Test if result contains Role element attributes.
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsRootAttributes($result)
    {
        $this->assertXMLTag(
            [
                'tag' => 'Root',
                'attributes' => [
                    'media-type' => 'application/vnd.ez.api.Root+xml',
                ],
            ],
            $result,
            'Invalid <Root> attributes.',
            false
        );
    }

    /**
     * @depends testVisit
     */
    public function testResultContainsRouterTag($result)
    {
        $this->assertXMLTag(
            [
                'tag' => 'Router',
            ],
            $result,
            'Invalid <Router> element.',
            false
        );
    }

    /**
     * @depends testVisit
     */
    public function testResultContainsRouterWithAttributes($result)
    {
        $this->assertXMLTag(
            [
                'tag' => 'RouterWithAttributes',
                'attributes' => [
                    'media-type' => 'application/vnd.ez.api.UserRefList+xml',
                ],
            ],
            $result,
            'Invalid <RouterWithAttributes> element.',
            false
        );
    }

    /**
     * @depends testVisit
     */
    public function testResultContainsTemplateRouterTag($result)
    {
        $this->assertXMLTag(
            [
                'tag' => 'TemplateRouter',
            ],
            $result,
            'Invalid <TemplateRouter> element.',
            false
        );
    }

    /**
     * @depends testVisit
     */
    public function testResultContainsTemplateRouterWithAttributes($result)
    {
        $this->assertXMLTag(
            [
                'tag' => 'TemplateRouterWithAttributes',
                'attributes' => [
                    'media-type' => 'application/vnd.ez.api.UserRefList+xml',
                ],
            ],
            $result,
            'Invalid <TemplateRouterWithAttributes> element.',
            false
        );
    }

    /**
     * Get the Role visitor.
     *
     * @return \eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor\Root
     */
    protected function internalGetVisitor()
    {
        return new ValueObjectVisitor\Root();
    }
}
