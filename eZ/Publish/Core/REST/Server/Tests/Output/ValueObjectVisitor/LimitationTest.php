<?php
/**
 * File containing a test class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server\Tests\Output\ValueObjectVisitor;
use eZ\Publish\Core\REST\Common\Tests\Output\ValueObjectVisitorBaseTest;

use eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor;
use eZ\Publish\Core\Repository\Values\User;
use eZ\Publish\Core\REST\Common;

class LimitationTest extends ValueObjectVisitorBaseTest
{
    /**
     * Test the Limitation visitor
     *
     * @return string
     */
    public function testVisit()
    {
        $visitor   = $this->getLimitationVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument( null );

        $limitation = new \eZ\Publish\API\Repository\Values\User\Limitation\ContentTypeLimitation();
        $limitation->limitationValues = array( 1, 2, 3 );

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $limitation
        );

        $result = $generator->endDocument( null );

        $this->assertNotNull( $result );

        return $result;
    }

    /**
     * Test if result contains limitation element
     *
     * @param string $result
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
     * Test if result contains limitation element attributes
     *
     * @param string $result
     * @depends testVisit
     */
    public function testResultContainsLimitationAttributes( $result )
    {
        $this->assertTag(
            array(
                'tag'      => 'limitation',
                'attributes' => array(
                    'identifier' => 'Class',
                    'media-type' => 'application/vnd.ez.api.limitation+xml'
                )
            ),
            $result,
            'Invalid <limitation> attributes.',
            false
        );
    }

    /**
     * Test if result contains values element
     *
     * @param string $result
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
            'Invalid <values> element.',
            false
        );
    }

    /**
     * Get the Limitation visitor
     *
     * @return \eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor\Limitation
     */
    protected function getLimitationVisitor()
    {
        return new ValueObjectVisitor\Limitation(
            new Common\UrlHandler\eZPublish()
        );
    }
}
