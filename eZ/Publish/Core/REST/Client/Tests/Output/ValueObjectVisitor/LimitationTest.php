<?php
/**
 * File containing a LimitationTest class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Client\Tests\Output\ValueObjectVisitor;

use eZ\Publish\Core\REST\Common\Tests\Output\ValueObjectVisitorBaseTest;

use eZ\Publish\Core\REST\Client\Output\ValueObjectVisitor;
use eZ\Publish\Core\REST\Common;

class LimitationTest extends ValueObjectVisitorBaseTest
{
    /**
     * Tests the Limitation visitor
     *
     * @return string
     */
    public function testVisit()
    {
        $visitor   = $this->getLimitationVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument( null );

        $contentTypeLimitation = new \eZ\Publish\API\Repository\Values\User\Limitation\ContentTypeLimitation();
        $contentTypeLimitation->limitationValues = array( 1, 2, 3 );

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $contentTypeLimitation
        );

        $result = $generator->endDocument( null );

        $this->assertNotNull( $result );

        return $result;
    }

    /**
     * Tests that the result contains limitation element
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsLimitationElement( $result )
    {
        $this->assertTag(
            array(
                'tag'      => 'limitation',
                'children' => array(
                    'count' => 1
                )
            ),
            $result,
            'Invalid <limitation> element.',
            false
        );
    }

    /**
     * Tests that the result contains limitation attributes
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsLimitationAttributes( $result )
    {
        $this->assertTag(
            array(
                'tag'      => 'limitation',
                'attributes' => array(
                    'media-type' => 'application/vnd.ez.api.limitation+xml',
                    'identifier' => 'Class',
                )
            ),
            $result,
            'Invalid <limitation> attributes.',
            false
        );
    }

    /**
     * Tests that the result contains values element
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsValuesElement( $result )
    {
        $this->assertTag(
            array(
                'tag' => 'values',
                'children' => array(
                    'count' => 3
                )
            ),
            $result,
            'Invalid or non-existing <limitation> values element.',
            false
        );
    }

    /**
     * Gets the Limitation visitor
     *
     * @return \eZ\Publish\Core\REST\Client\Output\ValueObjectVisitor\Limitation
     */
    protected function getLimitationVisitor()
    {
        return new ValueObjectVisitor\Limitation(
            new Common\UrlHandler\eZPublish()
        );
    }
}
