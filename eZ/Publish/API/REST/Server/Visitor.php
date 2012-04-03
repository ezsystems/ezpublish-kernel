<?php
/**
 * File containing the BaseTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\REST\Server;

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

    public function __construct( Generator $generator, array $visitors )
    {
        $this->generator = $generator;
        foreach ( $visitors as $class => $visitor )
        {
            $this->addVisitor( $class, $visitor );
        }
    }

    public function addVisitor( $class, ValueObjectVisitor $visitor )
    {
        if ( $class[0] === '\\' )
        {
            $class = substr( $class, 1 );
        }

        $this->visitors[$class] = $visitor;
    }

    /**
     * Visit struct returned by controllers
     *
     * @param mixed $data
     * @return string
     */
    public function visit( $data )
    {
        $this->generator->startDocument( $data );
        $this->visitValueObject( $data );
        return $this->generator->endDocument( $data );
    }

    /**
     * Visit struct returned by controllers
     *
     * @param mixed $data
     * @return string
     */
    public function visitValueObject( $data )
    {
        $classname = get_class( $data );
        do {
            if ( isset( $this->visitors[$classname] ) )
            {
                return $this->visitors[$classname]->visit( $this, $data );
            }
        } while ( $classname = get_parent_class( $classname ) );

        throw new \RuntimeException( '"No freaking visitor found!"' );
    }
}

