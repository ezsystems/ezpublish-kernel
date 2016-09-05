<?php
/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishRestBundle\Tests\Functional;

use eZ\Bundle\EzPublishRestBundle\Tests\Functional\TestCase as RESTFunctionalTestCase;
use eZ\Publish\Core\REST\Common\Tests\AssertXmlTagTrait;

class ResourceEmbeddingTest extends RESTFunctionalTestCase
{
    use AssertXmlTagTrait;

    public function testEmbed()
    {
        $request = $this->createHttpRequest('GET', '/api/ezp/v2/content/objects/1');
        $request->addHeader('x-ez-embed-value: Content.MainLocation,Content.Owner.Section');

        $response = $this->sendHttpRequest($request);

        self::assertHttpResponseCodeEquals($response, 200);

        $doc = new \DOMDocument();
        $doc->loadXML($response->getContent());

        $this->assertXPath($doc, '/Content/MainLocation/id');
        $this->assertXPath($doc, '/Content/Owner/name');
        $this->assertXPath($doc, '/Content/Owner/Section/sectionId');
    }

    protected function assertXPath(\DOMDocument $document, $xpathExpression)
    {
        $xpath = new \DOMXPath($document);

        $this->assertTrue(
            $xpath->evaluate("boolean({$xpathExpression})"),
            "XPath expression '{$xpathExpression}' resulted in an empty node set."
        );
    }
}
