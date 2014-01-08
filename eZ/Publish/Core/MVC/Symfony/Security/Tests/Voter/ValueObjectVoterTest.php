<?php
/**
 * File containing the ValueObjectVoterTest class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\Security\Tests\Voter;

use eZ\Publish\Core\MVC\Symfony\Security\Authorization\Attribute;
use eZ\Publish\Core\MVC\Symfony\Security\Authorization\Voter\ValueObjectVoter;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use PHPUnit_Framework_TestCase;

class ValueObjectVoterTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\eZ\Publish\API\Repository\Repository
     */
    private $repository;

    /**
     * @var \Closure
     */
    private $lazyRepository;

    protected function setUp()
    {
        parent::setUp();
        $repository = $this->repository = $this->getMock( 'eZ\Publish\API\Repository\Repository' );
        $this->lazyRepository = function () use ( $repository )
        {
            return $repository;
        };
    }

    /**
     * @dataProvider supportsAttributeProvider
     */
    public function testSupportsAttribute( $attribute, $expectedResult )
    {
        $voter = new ValueObjectVoter( $this->lazyRepository );
        $this->assertSame( $expectedResult, $voter->supportsAttribute( $attribute ) );
    }

    public function supportsAttributeProvider()
    {
        return array(
            array( 'foo', false ),
            array( new Attribute( 'foo', 'bar' ), false ),
            array( new Attribute( 'foo', 'bar', array( 'some' => 'thing' ) ), false ),
            array( new \stdClass(), false ),
            array( array( 'foo' ), false ),
            array(
                new Attribute(
                    'foo',
                    'bar',
                    array( 'valueObject' => $this->getMockForAbstractClass( 'eZ\Publish\API\Repository\Values\ValueObject' ) )
                ),
                true
            ),
        );
    }

    /**
     * @dataProvider supportsClassProvider
     */
    public function testSupportsClass( $class )
    {
        $voter = new ValueObjectVoter( $this->lazyRepository );
        $this->assertTrue( $voter->supportsClass( $class ) );
    }

    public function supportsClassProvider()
    {
        return array(
            array( 'foo' ),
            array( 'bar' ),
            array( 'eZ\Publish\API\Repository\Values\ValueObject' ),
            array( 'eZ\Publish\Core\MVC\Symfony\Controller\Content\ViewController' ),
        );
    }

    /**
     * @dataProvider voteInvalidAttributeProvider
     */
    public function testVoteInvalidAttribute( array $attributes )
    {
        $voter = new ValueObjectVoter( $this->lazyRepository );
        $this->assertSame(
            VoterInterface::ACCESS_ABSTAIN,
            $voter->vote(
                $this->getMock( 'Symfony\Component\Security\Core\Authentication\Token\TokenInterface' ),
                new \stdClass,
                $attributes
            )
        );
    }

    public function voteInvalidAttributeProvider()
    {
        return array(
            array( array() ),
            array( array( 'foo' ) ),
            array( array( 'foo', 'bar', array( 'some' => 'thing' ) ) ),
            array( array( new \stdClass ) ),
            array( array( new Attribute( 'content', 'read' ) ) ),
        );
    }

    /**
     * @dataProvider voteProvider
     */
    public function testVote( Attribute $attribute, $repositoryCanUser, $expectedResult )
    {
        $voter = new ValueObjectVoter( $this->lazyRepository );
        $targets = isset( $attribute->limitations['targets'] ) ? $attribute->limitations['targets'] : null;
        $this->repository
            ->expects( $this->once() )
            ->method( 'canUser' )
            ->with( $attribute->module, $attribute->function, $attribute->limitations['valueObject'], $targets )
            ->will( $this->returnValue( $repositoryCanUser ) );

        $this->assertSame(
            $expectedResult,
            $voter->vote(
                $this->getMock( 'Symfony\Component\Security\Core\Authentication\Token\TokenInterface' ),
                new \stdClass,
                array( $attribute )
            )
        );
    }

    public function voteProvider()
    {
        return array(
            array(
                new Attribute( 'content', 'read', array( 'valueObject' => $this->getMockForAbstractClass( 'eZ\Publish\API\Repository\Values\ValueObject' ) ) ),
                true,
                VoterInterface::ACCESS_GRANTED
            ),
            array(
                new Attribute( 'content', 'read', array( 'valueObject' => $this->getMockForAbstractClass( 'eZ\Publish\API\Repository\Values\ValueObject' ) ) ),
                false,
                VoterInterface::ACCESS_DENIED
            ),
            array(
                new Attribute(
                    'content',
                    'read',
                    array(
                        'valueObject' => $this->getMockForAbstractClass( 'eZ\Publish\API\Repository\Values\ValueObject' ),
                        'targets' => $this->getMockForAbstractClass( 'eZ\Publish\API\Repository\Values\ValueObject' )
                    )
                ),
                true,
                VoterInterface::ACCESS_GRANTED
            ),
            array(
                new Attribute(
                    'content',
                    'read',
                    array(
                        'valueObject' => $this->getMockForAbstractClass( 'eZ\Publish\API\Repository\Values\ValueObject' ),
                        'targets' => array( $this->getMockForAbstractClass( 'eZ\Publish\API\Repository\Values\ValueObject' ) )
                    )
                ),
                true,
                VoterInterface::ACCESS_GRANTED
            ),
            array(
                new Attribute(
                    'content',
                    'read',
                    array(
                        'valueObject' => $this->getMockForAbstractClass( 'eZ\Publish\API\Repository\Values\ValueObject' ),
                        'targets' => $this->getMockForAbstractClass( 'eZ\Publish\API\Repository\Values\ValueObject' )
                    )
                ),
                false,
                VoterInterface::ACCESS_DENIED
            ),
            array(
                new Attribute(
                    'content',
                    'read',
                    array(
                        'valueObject' => $this->getMockForAbstractClass( 'eZ\Publish\API\Repository\Values\ValueObject' ),
                        'targets' => array( $this->getMockForAbstractClass( 'eZ\Publish\API\Repository\Values\ValueObject' ) )
                    )
                ),
                false,
                VoterInterface::ACCESS_DENIED
            ),
        );
    }
}
