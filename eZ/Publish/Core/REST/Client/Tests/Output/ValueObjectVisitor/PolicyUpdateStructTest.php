<?php
/**
 * File containing a PolicyUpdateStructTest class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Client\Tests\Output\ValueObjectVisitor;

use eZ\Publish\Core\REST\Common\Tests\Output\ValueObjectVisitorBaseTest;

use eZ\Publish\Core\REST\Client\Output\ValueObjectVisitor;
use eZ\Publish\Core\REST\Client\Values\User;
use eZ\Publish\Core\REST\Common;

class PolicyUpdateStructTest extends ValueObjectVisitorBaseTest
{
    /**
     * Tests the PolicyUpdateStruct visitor
     *
     * @return string
     */
    public function testVisit()
    {
        $visitor   = $this->getPolicyUpdateStructVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument( null );

        $contentTypeLimitation = new \eZ\Publish\API\Repository\Values\User\Limitation\ContentTypeLimitation();
        $contentTypeLimitation->limitationValues = array( 1, 2, 3 );

        $policyUpdateStruct = new User\PolicyUpdateStruct();
        $policyUpdateStruct->addLimitation( $contentTypeLimitation );

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $policyUpdateStruct
        );

        $result = $generator->endDocument( null );

        $this->assertNotNull( $result );

        return $result;
    }

    /**
     * Tests that the result contains PolicyUpdate element
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsPolicyUpdateElement( $result )
    {
        $this->assertTag(
            array(
                'tag'      => 'PolicyUpdate',
                'children' => array(
                    'count' => 1
                )
            ),
            $result,
            'Invalid <PolicyUpdate> element.',
            false
        );
    }

    /**
     * Tests that the result contains PolicyUpdate attributes
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsPolicyUpdateAttributes( $result )
    {
        $this->assertTag(
            array(
                'tag'      => 'PolicyUpdate',
                'attributes' => array(
                    'media-type' => 'application/vnd.ez.api.PolicyUpdate+xml',
                )
            ),
            $result,
            'Invalid <PolicyUpdate> attributes.',
            false
        );
    }

    /**
     * Tests that the result contains limitations element
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsLimitationsElement( $result )
    {
        $this->assertTag(
            array(
                'tag' => 'limitations'
            ),
            $result,
            'Invalid or non-existing <PolicyUpdate> limitations element.',
            false
        );
    }

    /**
     * Tests that the result contains limitations attributes
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsLimitationsAttributes( $result )
    {
        $this->assertTag(
            array(
                'tag'      => 'limitations',
                'attributes' => array(
                    'media-type' => 'application/vnd.ez.api.limitations+xml',
                )
            ),
            $result,
            'Invalid <PolicyUpdate> limitations attributes.',
            false
        );
    }

    /**
     * Gets the PolicyUpdateStruct visitor
     *
     * @return \eZ\Publish\Core\REST\Client\Output\ValueObjectVisitor\PolicyUpdateStruct
     */
    protected function getPolicyUpdateStructVisitor()
    {
        return new ValueObjectVisitor\PolicyUpdateStruct(
            new Common\UrlHandler\eZPublish()
        );
    }
}
