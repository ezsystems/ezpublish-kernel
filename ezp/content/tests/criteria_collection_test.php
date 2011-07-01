<?php
/**
 * File containing the ezp\content\CriteriaCollection class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 * @package ezp
 * @subpackage content_tests
 */

namespace ezp\content\tests;

/**
 * Test case for CriteriaCollection
 *
 * @package ezp
 * @subpackage content_tests
 */
use ezp\base\Repository;

class CriteriaCollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var new \ezp\content\Criteria\CriteriaCollection
     */
    protected $criteria;

    /**
     * @var ReflectionObject
     */
    protected $criteriaReflection;

    public function __construct()
    {
        parent::__construct();
        $this->setName( "CriteriaCollection class tests" );

        // setup a content CriteriaCollection
        $this->criteria = new \ezp\content\Criteria\CriteriaCollection();
        $this->criteriaReflection = new \ReflectionObject( $this->criteria );
    }

    public function testWhere()
    {
        self::markTestIncomplete( "not implemented" );
    }

    public function testLimit()
    {
        $limitValue = 10;

        $return = $this->criteria->limit( $limitValue );

        // @todo Improve the test when the in-memory storage engine is available
        self::assertEquals( $limitValue, $this->getCriteriaPropertyValue( 'limit' ) );
        self::assertEquals( $return, $this->criteria );
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testLimitWrongLimit()
    {
        $limitValue = "foobar";

        $return = $this->criteria->limit( $limitValue );

        // @todo Improve the test when the in-memory storage engine is available
        self::assertEquals( $limitValue, $this->getCriteriaPropertyValue( 'limit' ) );
        self::assertEquals( $return, $this->criteria );
    }

    public function testOffset()
    {
        $offsetValue = 10;

        $return = $this->criteria->offset( $offsetValue );

        // @todo Improve the test when the in-memory storage engine is available
        self::assertEquals( $offsetValue, $this->getCriteriaPropertyValue( 'offset' ) );
        self::assertEquals( $return, $this->criteria );
    }

    /**
     * Checks that setting a string as the offset will set the offset to 0
     */
    public function testOffsetWrongOffset()
    {
        $offsetValue = "foobar";

        $return = $this->criteria->offset( $offsetValue );

        // @todo Improve the test when the in-memory storage engine is available
        self::assertEquals( 0, $this->getCriteriaPropertyValue( 'offset' ) );
        self::assertEquals( $return, $this->criteria );
    }

    public function testType()
    {
        $result = $this->criteria->type( 'class_one', 'class_two' );

        // @todo Improve the test when the in-memory storage engine is available
        self::assertEquals( $this->criteria, $result );
        self::assertEquals( array( 'class_one', 'class_two' ), $this->getCriteriaPropertyValue( 'type' ) );

        $result = $this->criteria->type( 'class_three' );

        // @todo Improve the test when the in-memory storage engine is available
        self::assertEquals( $this->criteria, $result );
        self::assertEquals( array( 'class_one', 'class_two', 'class_three' ), $this->getCriteriaPropertyValue( 'type' ) );
    }

    /**
     * Test for the {@link ezp\content\Criteria\CriteriaCollection::field} property
     */
    public function testField()
    {
        self::assertInstanceOf( 'ezp\content\Criteria\FieldCriteria', $this->criteria->field );
    }


    /**
     * Test for the {@link ezp\content\Criteria\CriteriaCollection::meta} property
     */
    public function testMeta()
    {
        self::assertInstanceOf( 'ezp\content\Criteria\MetadataCriteria', $this->criteria->meta );
    }

    /**
     * Test for the {@link ezp\content\Criteria\CriteriaCollection::location} property
     */
    public function testLocation()
    {
        self::assertInstanceOf( 'ezp\content\Criteria\LocationCriteria', $this->criteria->location );
    }

    /**
     * Returns the value of a criteria private/protected property $propertyName
     *
     * Will of course work on public ones as well
     *
     * @param string $propertyName
     */
    private function getCriteriaPropertyValue( $propertyName )
    {
        $property = $this->criteriaReflection->getProperty( $propertyName );
        $property->setAccessible( true );
        return $property->getValue( $this->criteria );
    }
}
