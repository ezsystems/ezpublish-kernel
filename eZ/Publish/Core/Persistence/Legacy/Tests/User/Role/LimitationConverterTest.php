<?php
/**
 * File contains: eZ\Publish\Core\Persistence\Legacy\Tests\User\Role\LimitationConverter class
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\Tests\User\Role;

use eZ\Publish\API\Repository\Values\User\Limitation;
use eZ\Publish\Core\Persistence\Legacy\Tests\TestCase;
use eZ\Publish\Core\Persistence\Legacy\User\Role\LimitationConverter;
use eZ\Publish\Core\Persistence\Legacy\User\Role\LimitationHandler\ObjectStateHandler as ObjectStateLimitationHandler;
use eZ\Publish\SPI\Persistence\User\Policy;

/**
 * Test case for LimitationConverter
 */
class LimitationConverterTest extends TestCase
{
    protected function getLimitationConverter()
    {
        $dbHandler = $this->getDatabaseHandler();
        return new LimitationConverter( array( new ObjectStateLimitationHandler( $dbHandler ) ) );
    }

    /**
     * Test Object State from SPI value (supported by API) to legacy value (database)
     */
    public function testObjectStateToLegacy()
    {
        $this->insertDatabaseFixture( __DIR__ . '/../../../../../Repository/DomainLogic/Tests/Service/Integration/Legacy/_fixtures/clean_ezdemo_47_dump.php' );

        $converter = $this->getLimitationConverter();

        $policy = new Policy();
        $policy->module = "content";
        $policy->function = "read";

        // #1 Test wildcard
        $policy->limitations = array(
            Limitation::STATE => '*'
        );
        $converter->toLegacy( $policy );
        $this->assertEquals(
            array(
                ObjectStateLimitationHandler::STATE_GROUP . 'ez_lock' => '*'
            ),
            $policy->limitations,
            'Expected State limitation to be transformed into StateGroup_ limitations'
        );

        // #2 Test valid state values
        $policy->limitations = array(
            Limitation::STATE => array( 1, 2 )
        );
        $converter->toLegacy( $policy );
        $this->assertEquals(
            array(
                ObjectStateLimitationHandler::STATE_GROUP . 'ez_lock' => array( 1, 2 )
            ),
            $policy->limitations,
            'Expected State limitation to be transformed into StateGroup_ limitations'
        );

        // #3 Test invalid state values (the invalid values are just ignored as validation is done on higher level)
        $policy->limitations = array(
            Limitation::STATE => array( 1, 2, 3, 4 )
        );
        $converter->toLegacy( $policy );
        $this->assertEquals(
            array(
                ObjectStateLimitationHandler::STATE_GROUP . 'ez_lock' => array( 1, 2 )
            ),
            $policy->limitations,
            'Expected State limitation to be transformed into StateGroup_ limitations'
        );
    }

    /**
     * Test Object State from legacy value (database) to SPI value (supported by API)
     */
    public function testObjectStateToSPI()
    {
        $this->insertDatabaseFixture( __DIR__ . '/../../../../../Repository/DomainLogic/Tests/Service/Integration/Legacy/_fixtures/clean_ezdemo_47_dump.php' );

        $converter = $this->getLimitationConverter();

        $policy = new Policy();
        $policy->module = "content";
        $policy->function = "read";

        // #1 Test wildcard
        $policy->limitations = array(
            ObjectStateLimitationHandler::STATE_GROUP . 'ez_lock' => '*'
        );
        $converter->toSPI( $policy );
        $this->assertEquals(
            array(
                Limitation::STATE => '*'
            ),
            $policy->limitations,
            'Expected State limitation to be transformed into StateGroup_ limitations'
        );

        // #2 Test valid state values
        $policy->limitations = array(
            ObjectStateLimitationHandler::STATE_GROUP . 'ez_lock' => array( 1, 2 )
        );
        $converter->toSPI( $policy );
        $this->assertEquals(
            array(
                Limitation::STATE => array( 1, 2 )
            ),
            $policy->limitations,
            'Expected State limitation to be transformed into StateGroup_ limitations'
        );

        // #3 Test invalid state values (as the values supposedly comes from database they are carried over)
        $policy->limitations = array(
            ObjectStateLimitationHandler::STATE_GROUP . 'ez_lock' => array( 1, 2, 3, 4 )
        );
        $converter->toSPI( $policy );
        $this->assertEquals(
            array(
                Limitation::STATE => array( 1, 2, 3, 4 )
            ),
            $policy->limitations,
            'Expected State limitation to be transformed into StateGroup_ limitations'
        );

        // #4 Test invalid state values with mix of wildcard (wildcard values is loaded from db, rest kept as is)
        $policy->limitations = array(
            ObjectStateLimitationHandler::STATE_GROUP . 'ez_lock' => '*',
            ObjectStateLimitationHandler::STATE_GROUP . 'invalid' => array( 5 )
        );
        $converter->toSPI( $policy );

        $this->assertArrayHasKey( Limitation::STATE, $policy->limitations );

        // Don't expect backend to return sorted result, so lets sort values before testing
        sort( $policy->limitations[Limitation::STATE], SORT_NUMERIC );

        $this->assertEquals(
            array( 1, 2, 5 ),
            $policy->limitations[Limitation::STATE],
            'Expected State limitation to be transformed into StateGroup_ limitations'
        );
    }
}
