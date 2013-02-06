<?php
/**
 * File containing a test class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server\Tests\Output\ValueObjectVisitor;

use eZ\Publish\Core\REST\Common\Tests\Output\ValueObjectVisitorBaseTest;

use eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor;
use eZ\Publish\Core\Repository\Values\User;
use eZ\Publish\Core\REST\Common;

class PolicyTest extends ValueObjectVisitorBaseTest
{
    /**
     * Test the Policy visitor
     *
     * @return string
     */
    public function testVisit()
    {
        $visitor   = $this->getPolicyVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument( null );

        $contentTypeLimitation = new \eZ\Publish\API\Repository\Values\User\Limitation\ContentTypeLimitation();
        $contentTypeLimitation->limitationValues = array( 1, 2, 3 );

        $policy = new User\Policy(
            array(
                'id'       => 42,
                'roleId'   => '84',
                'module'   => 'content',
                'function' => 'delete',
                'limitations' => array(
                    'Class' => $contentTypeLimitation
                )
            )
        );

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $policy
        );

        $result = $generator->endDocument( null );

        $this->assertNotNull( $result );

        return $result;
    }

    /**
     * Test if result contains Policy element
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsPolicyElement( $result )
    {
        $this->assertTag(
            array(
                'tag'      => 'Policy',
                'children' => array(
                    'less_than' => 5,
                    'greater_than' => 2
                )
            ),
            $result,
            'Invalid <Policy> element.',
            false
        );
    }

    /**
     * Test if result contains Policy element attributes
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsPolicyAttributes( $result )
    {
        $this->assertTag(
            array(
                'tag'      => 'Policy',
                'attributes' => array(
                    'media-type' => 'application/vnd.ez.api.Policy+xml',
                    'href'       => '/user/roles/84/policies/42',
                )
            ),
            $result,
            'Invalid <Policy> attributes.',
            false
        );
    }

    /**
     * Test if result contains id value element
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsIdValueElement( $result )
    {
        $this->assertTag(
            array(
                'tag'      => 'id',
                'content'  => '42'
            ),
            $result,
            'Invalid or non-existing <Policy> id value element.',
            false
        );
    }

    /**
     * Test if result contains module value element
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsModuleValueElement( $result )
    {
        $this->assertTag(
            array(
                'tag'      => 'module',
                'content'  => 'content'
            ),
            $result,
            'Invalid or non-existing <Policy> module value element.',
            false
        );
    }

    /**
     * Test if result contains function value element
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsFunctionValueElement( $result )
    {
        $this->assertTag(
            array(
                'tag'      => 'function',
                'content'  => 'delete'
            ),
            $result,
            'Invalid or non-existing <Policy> function value element.',
            false
        );
    }

    /**
     * Test if result contains limitations element
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
            'Invalid <limitations> element.',
            false
        );
    }

    /**
     * Test if result contains limitations attributes
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsLimitationsAttributes( $result )
    {
        $this->assertTag(
            array(
                'tag'      => 'limitations'
            ),
            $result,
            'Invalid <limitations> attributes.',
            false
        );
    }

    /**
     * Get the Policy visitor
     *
     * @return \eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor\Policy
     */
    protected function getPolicyVisitor()
    {
        return new ValueObjectVisitor\Policy(
            new Common\UrlHandler\eZPublish()
        );
    }
}
