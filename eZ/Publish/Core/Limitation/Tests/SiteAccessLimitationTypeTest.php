<?php
/**
 * File containing a Test Case for LimitationType class
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Limitation\Tests;

use eZ\Publish\API\Repository\Values\ValueObject;
use eZ\Publish\API\Repository\Values\User\Limitation;
use eZ\Publish\API\Repository\Values\User\Limitation\SiteAccessLimitation;
use eZ\Publish\API\Repository\Values\User\Limitation\ObjectStateLimitation;
use eZ\Publish\Core\Limitation\SiteAccessLimitationType;
use eZ\Publish\Core\MVC\Symfony\SiteAccess;

/**
 * Test Case for LimitationType
 */
class SiteAccessLimitationTypeTest extends Base
{
    /**
     * @return \eZ\Publish\Core\Limitation\SiteAccessLimitationType
     */
    public function testConstruct()
    {
        return new SiteAccessLimitationType();
    }

    /**
     * @return array
     */
    public function providerForTestAcceptValue()
    {
        return array(
            array( new SiteAccessLimitation() ),
            array( new SiteAccessLimitation( array() ) ),
            array(
                new SiteAccessLimitation(
                    array(
                        'limitationValues' => array(
                            "2339567439",
                            "2582995467",
                            "1817462202"
                        )
                    )
                )
            ),
        );
    }

    /**
     * @depends testConstruct
     * @dataProvider providerForTestAcceptValue
     *
     * @param \eZ\Publish\API\Repository\Values\User\Limitation\SiteAccessLimitation $limitation
     * @param \eZ\Publish\Core\Limitation\SiteAccessLimitationType $limitationType
     */
    public function testAcceptValue( SiteAccessLimitation $limitation, SiteAccessLimitationType $limitationType )
    {
        $limitationType->acceptValue( $limitation );
    }

    /**
     * @return array
     */
    public function providerForTestAcceptValueException()
    {
        return array(
            array( new ObjectStateLimitation() ),
            array( new SiteAccessLimitation( array( 'limitationValues' => array( true ) ) ) ),
        );
    }

    /**
     * @depends testConstruct
     * @dataProvider providerForTestAcceptValueException
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     *
     * @param \eZ\Publish\API\Repository\Values\User\Limitation $limitation
     * @param \eZ\Publish\Core\Limitation\SiteAccessLimitationType $limitationType
     */
    public function testAcceptValueException( Limitation $limitation, SiteAccessLimitationType $limitationType )
    {
        $limitationType->acceptValue( $limitation );
    }

    /**
     * @return array
     */
    public function providerForTestValidateError()
    {
        return array(
            array( new SiteAccessLimitation(), 0 ),
            array( new SiteAccessLimitation( array() ), 0 ),
            array(
                new SiteAccessLimitation(
                    array(
                        'limitationValues' => array( "2339567439" )
                    )
                ),
                0
            ),
            array( new SiteAccessLimitation( array( 'limitationValues' => array( true ) ) ), 1 ),
            array(
                new SiteAccessLimitation(
                    array(
                        'limitationValues' => array(
                            "2339567439",
                            false
                        )
                    )
                ),
                1
            ),
            array(
                new SiteAccessLimitation(
                    array(
                        'limitationValues' => array(
                            null,
                            array()
                        )
                    )
                ),
                2
            ),
            array(
                new SiteAccessLimitation(
                    array(
                        'limitationValues' => array(
                            "2339567439",
                            "2582995467",
                            "1817462202"
                        )
                    )
                ),
                0
            ),
        );
    }

    /**
     * @dataProvider providerForTestValidateError
     * @depends testConstruct
     *
     * @param \eZ\Publish\API\Repository\Values\User\Limitation\SiteAccessLimitation $limitation
     * @param int $errorCount
     * @param \eZ\Publish\Core\Limitation\SiteAccessLimitationType $limitationType
     */
    public function testValidateError( SiteAccessLimitation $limitation, $errorCount, SiteAccessLimitationType $limitationType )
    {
        self::markTestSkipped( "Method validate() is not implemented" );
        $validationErrors = $limitationType->validate( $limitation );
        self::assertCount( $errorCount, $validationErrors );
    }

    /**
     * @depends testConstruct
     *
     * @param \eZ\Publish\Core\Limitation\SiteAccessLimitationType $limitationType
     */
    public function testBuildValue( SiteAccessLimitationType $limitationType )
    {
        $expected = array( 'test', 'test' => 9 );
        $value = $limitationType->buildValue( $expected );

        self::assertInstanceOf( '\eZ\Publish\API\Repository\Values\User\Limitation\SiteAccessLimitation', $value );
        self::assertInternalType( 'array', $value->limitationValues );
        self::assertEquals( $expected, $value->limitationValues );
    }

    /**
     * @return array
     */
    public function providerForTestEvaluate()
    {
        return array(
            // SiteAccess, no access
            array(
                'limitation' => new SiteAccessLimitation(),
                'object' => new SiteAccess( 'behat_site' ),
                'expected' => false
            ),
            // SiteAccess, no access
            array(
                'limitation' => new SiteAccessLimitation( array( 'limitationValues' => array( "2339567439" ) ) ),
                'object' => new SiteAccess( 'behat_site' ),
                'expected' => false
            ),
            // SiteAccess, with access
            array(
                'limitation' => new SiteAccessLimitation( array( 'limitationValues' => array( "1817462202" ) ) ),
                'object' => new SiteAccess( 'behat_site' ),
                'expected' => true
            ),
        );
    }

    /**
     * @depends testConstruct
     * @dataProvider providerForTestEvaluate
     */
    public function testEvaluate(
        SiteAccessLimitation $limitation,
        ValueObject $object,
        $expected,
        SiteAccessLimitationType $limitationType
    )
    {
        $userMock = $this->getUserMock();
        $userMock->expects( $this->never() )->method( $this->anything() );

        $value = $limitationType->evaluate(
            $limitation,
            $userMock,
            $object
        );

        self::assertInternalType( 'boolean', $value );
        self::assertEquals( $expected, $value );
    }

    /**
     * @return array
     */
    public function providerForTestEvaluateInvalidArgument()
    {
        return array(
            // invalid limitation
            array(
                'limitation' => new ObjectStateLimitation(),
                'object' => new SiteAccess()
            ),
            // invalid object
            array(
                'limitation' => new SiteAccessLimitation(),
                'object' => new ObjectStateLimitation()
            ),
        );
    }

    /**
     * @depends testConstruct
     * @dataProvider providerForTestEvaluateInvalidArgument
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function testEvaluateInvalidArgument(
        Limitation $limitation,
        ValueObject $object,
        SiteAccessLimitationType $limitationType
    )
    {
        $userMock = $this->getUserMock();
        $userMock->expects( $this->never() )->method( $this->anything() );

        $limitationType->evaluate(
            $limitation,
            $userMock,
            $object
        );
    }

    /**
     * @depends testConstruct
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotImplementedException
     *
     * @param \eZ\Publish\Core\Limitation\SiteAccessLimitationType $limitationType
     */
    public function testGetCriterion( SiteAccessLimitationType $limitationType )
    {
        $limitationType->getCriterion( new SiteAccessLimitation(), $this->getUserMock() );
    }

    /**
     * @depends testConstruct
     *
     * @param \eZ\Publish\Core\Limitation\SiteAccessLimitationType $limitationType
     */
    public function testValueSchema( SiteAccessLimitationType $limitationType )
    {
        self::markTestSkipped( "Method valueSchema() is not implemented" );
    }
}
