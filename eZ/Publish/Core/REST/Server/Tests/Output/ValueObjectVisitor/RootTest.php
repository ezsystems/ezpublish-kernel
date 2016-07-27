<?php

/**
 * File containing a test class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\Core\REST\Server\Tests\Output\ValueObjectVisitor;

use eZ\Publish\Core\REST\Common\Tests\Output\ValueObjectVisitorBaseTest;
use eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor;
use eZ\Publish\Core\REST\Server\Service\ExpressionRouterRootResourceBuilder;

class RootTest extends ValueObjectVisitorBaseTest
{
    protected function getRootResourceBuilder()
    {
        $resourceConfig = array(
            'Router' => array(
                'mediaType' => '',
                'href' => 'router.generate("ezpublish_rest_createContent")',
            ),
            'RouterWithAttributes' => array(
                'mediaType' => 'UserRefList',
                'href' => 'router.generate("ezpublish_rest_loadUsers")',
            ),
            'TemplateRouter' => array(
                'mediaType' => '',
                'href' => 'templateRouter.generate("ezpublish_rest_redirectContent", {remoteId: "{remoteId}"})',
            ),
            'TemplateRouterWithAttributes' => array(
                'mediaType' => 'UserRefList',
                'href' => 'templateRouter.generate("ezpublish_rest_loadUsers", {roleId: "{roleId}"})',
            ),
        );

        $this->addRouteExpectation('ezpublish_rest_createContent', array(), '/content/objects');
        $this->addTemplatedRouteExpectation('ezpublish_rest_redirectContent', array('remoteId' => '{remoteId}'), '/content/objects');
        $this->addRouteExpectation('ezpublish_rest_loadUsers', array(), '/user/users');
        $this->addTemplatedRouteExpectation('ezpublish_rest_loadUsers', array('roleId' => '{roleId}'), '/user/users{?roleId}');

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
            array('tag' => 'Root'),
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
            array(
                'tag' => 'Root',
                'attributes' => array(
                    'media-type' => 'application/vnd.ez.api.Root+xml',
                ),
            ),
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
            array(
                'tag' => 'Router',
            ),
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
            array(
                'tag' => 'RouterWithAttributes',
                'attributes' => array(
                    'media-type' => 'application/vnd.ez.api.UserRefList+xml',
                ),
            ),
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
            array(
                'tag' => 'TemplateRouter',
            ),
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
            array(
                'tag' => 'TemplateRouterWithAttributes',
                'attributes' => array(
                    'media-type' => 'application/vnd.ez.api.UserRefList+xml',
                ),
            ),
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
