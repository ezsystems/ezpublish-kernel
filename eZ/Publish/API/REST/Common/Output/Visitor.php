<?php
/**
 * File containing the Visitor class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\REST\Common\Output;
use eZ\Publish\API\REST\Common\Message;

/**
 * Visitor for view models
 */
class Visitor
{
    /**
     * Visitors for value objects
     *
     * Structure:
     * <code>
     *  array(
     *      <class> => <ValueObjectVisitor>,
     *      …
     *  )
     *
     * @var array
     */
    protected $visitors = array();

    /**
     * Generator
     *
     * @var Generator
     */
    protected $generator;

    /**
     * HTTP Response Headers
     *
     * @var array
     */
    protected $headers = array();

    /**
     * Construct from Generator and an array of concrete view model visitors
     *
     * @param Generator $generator
     * @param array $visitors
     * @return void
     */
    public function __construct( Generator $generator, array $visitors )
    {
        $this->generator = $generator;
        foreach ( $visitors as $class => $visitor )
        {
            $this->addVisitor( $class, $visitor );
        }
    }

    /**
     * Add a new visitor for the given class
     *
     * @param string $class
     * @param ValueObjectVisitor $visitor
     * @return void
     */
    public function addVisitor( $class, ValueObjectVisitor $visitor )
    {
        if ( $class[0] === '\\' )
        {
            $class = substr( $class, 1 );
        }

        $this->visitors[$class] = $visitor;
    }

    /**
     * Set HTTP response header
     *
     * Does not allow overwriting of response headers. The first definition of
     * a header will be used.
     *
     * @param string $name
     * @param string $value
     * @return void
     */
    public function setHeader( $name, $value )
    {
        if ( !isset( $this->headers[$name] ) )
        {
            $this->headers[$name] = $value;
        }
    }

    /**
     * Visit struct returned by controllers
     *
     * @param mixed $data
     * @return string
     */
    public function visit( $data )
    {

        $this->generator->reset();
        $this->generator->startDocument( $data );
        $this->visitValueObject( $data );

        $result = new Message(
            $this->headers,
            $this->generator->endDocument( $data )
        );

        $this->headers = array();

        return $result;
    }

    /**
     * Visit struct returned by controllers
     *
     * Should be called from sub-vistors to visit nested objects.
     *
     * @param mixed $data
     * @return void
     */
    public function visitValueObject( $data )
    {
        if ( !is_object( $data ) )
        {
            throw new Exceptions\InvalidTypeException( $data );
        }
        $checkedClassNames = array();

        $classname = get_class( $data );
        do {
            $checkedClassNames[] = $classname;
            if ( isset( $this->visitors[$classname] ) )
            {
                return $this->visitors[$classname]->visit( $this, $this->generator, $data );
            }
        } while ( $classname = get_parent_class( $classname ) );

        throw new Exceptions\NoVisitorFoundException( $checkedClassNames );
    }

    /**
     * Generates a media type for $type based on the used generator.
     *
     * @param string $type
     * @return string
     * @see \eZ\Publish\API\REST\Common\Generator::getMediaType()
     */
    public function getMediaType( $type )
    {
        return $this->generator->getMediaType( $type );
    }
}

