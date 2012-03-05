<?php
/**
 * File contains: ezp\Content\Tests\RelationTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\Tests;
use ezp\Content\Concrete as ConcreteContent,
    ezp\Content\Type\Concrete as ConcreteType,
    ezp\Content\Relation,
    ezp\Base\ServiceContainer,
    ezp\Base\Configuration,
    eZ\Publish\SPI\Persistence\Content\Relation as RelationValue,
    eZ\Publish\SPI\Persistence\Content\Type as TypeValue,
    ezp\User\Proxy as ProxyUser,
    PHPUnit_Framework_TestCase;

/**
 * Test case for Relation class
 */
class RelationTest extends PHPUnit_Framework_TestCase
{

    /**
     * @var \ezp\Content
     */
    protected $content;

    public function setUp()
    {
        parent::setUp();

        $sc = new ServiceContainer(
            Configuration::getInstance('service')->getAll(),
            array(
                '@persistence_handler' => new \eZ\Publish\SPI\Persistence\Storage\InMemory\Handler(),
                '@io_handler' => new \ezp\Io\Storage\InMemory(),
            )
        );
        $sc->getRepository()->getContentService();

        // setup a content type & content object of use by tests, fields are not needed for relation
        $vo = new TypeValue(
            array(
                'identifier' => 'article',
                'id' => 1,
                'status' => TypeValue::STATUS_DEFINED
            )
        );
        $contentType = new ConcreteType();
        $contentType->setState(
            array( 'properties' => $vo )
        );

        $this->content = new ConcreteContent( $contentType, new ProxyUser( 10, $sc->getRepository()->getUserService() ) );
        $this->content->setState(
            array(
                "properties" => new RelationValue(
                    array(
                        "id" => 42,
                    )
                )
            )
        );
    }

    /**
     * @covers \ezp\Content\Relation::__construct
     */
    public function testConstruct()
    {
        $relation = new Relation( Relation::COMMON, $this->content );
        $this->assertEquals( 42, $relation->destinationContentId );
        $this->assertEquals( Relation::COMMON, $relation->type );
    }

    /**
     * @expectedException \ezp\Base\Exception\InvalidArgumentValue
     * @covers \ezp\Content\Relation::__construct
     */
    public function testConstructWrongType1()
    {
        $relation = new Relation( "common", $this->content );
    }

    /**
     * @expectedException \ezp\Base\Exception\InvalidArgumentValue
     * @covers \ezp\Content\Relation::__construct
     */
    public function testConstructWrongType2()
    {
        $relation = new Relation( ~0, $this->content );
    }
}
