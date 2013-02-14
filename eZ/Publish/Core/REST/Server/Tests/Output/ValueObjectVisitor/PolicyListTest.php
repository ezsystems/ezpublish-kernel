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
use eZ\Publish\Core\REST\Server\Values\PolicyList;
use eZ\Publish\Core\Repository\Values\User;
use eZ\Publish\Core\REST\Common;

class PolicyListTest extends ValueObjectVisitorBaseTest
{
    /**
     * Test the PolicyList visitor
     *
     * @return string
     */
    public function testVisit()
    {
        $visitor   = $this->getPolicyListVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument( null );

        $policyList = new PolicyList( array(), '/user/roles/42/policies' );

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $policyList
        );

        $result = $generator->endDocument( null );

        $this->assertNotNull( $result );

        return $result;
    }

    /**
     * Test if result contains PolicyList element
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsPolicyListElement( $result )
    {
        $this->assertTag(
            array(
                'tag'      => 'PolicyList',
            ),
            $result,
            'Invalid <PolicyList> element.',
            false
        );
    }

    /**
     * Test if result contains PolicyList element attributes
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsPolicyListAttributes( $result )
    {
        $this->assertTag(
            array(
                'tag'      => 'PolicyList',
                'attributes' => array(
                    'media-type' => 'application/vnd.ez.api.PolicyList+xml',
                    'href'       => '/user/roles/42/policies',
                )
            ),
            $result,
            'Invalid <PolicyList> attributes.',
            false
        );
    }

    /**
     * Test if PolicyList visitor visits the children
     */
    public function testPolicyListVisitsChildren()
    {
        $visitor   = $this->getPolicyListVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument( null );

        $policyList = new PolicyList(
            array(
                new User\Policy(),
                new User\Policy(),
            ),
            42
        );

        $this->getVisitorMock()->expects( $this->exactly( 2 ) )
            ->method( 'visitValueObject' )
            ->with( $this->isInstanceOf( 'eZ\\Publish\\API\\Repository\\Values\\User\\Policy' ) );

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $policyList
        );
    }

    /**
     * Get the PolicyList visitor
     *
     * @return \eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor\PolicyList
     */
    protected function getPolicyListVisitor()
    {
        return new ValueObjectVisitor\PolicyList(
            new Common\UrlHandler\eZPublish()
        );
    }
}
