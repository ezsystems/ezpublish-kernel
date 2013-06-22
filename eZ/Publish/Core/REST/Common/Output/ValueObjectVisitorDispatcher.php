<?php
/**
 * File containing the ValueObjectVisitorDispatcher class.
 *
 * @copyright Copyright (C) 2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */
namespace eZ\Publish\Core\REST\Common\Output;

use eZ\Publish\Core\REST\Common\Output\ValueObjectVisitor;
use eZ\Publish\Core\REST\Common\Output\Visitor;
use eZ\Publish\Core\REST\Common\Output\Generator;

/**
 * Dispatches value objects to a visitor depending on the class name
 */
class ValueObjectVisitorDispatcher
{
    /**
     * @var array[string=>ValueObjectVisitor]
     */
    private $visitors;

    /**
     * @param string $visitedClassName The FQN of the visited class
     * @param ValueObjectVisitor $visitor The visitor object
     */
    public function registerVisitor( $visitedClassName, ValueObjectVisitor $visitor )
    {
        $this->visitors[$visitedClassName] = $visitor;
    }

    /**
     * @param Visitor   $visitor
     * @param Generator $generator
     * @param object    $data
     *
     * @return mixed
     * @throws Exceptions\NoVisitorFoundException
     * @throws Exceptions\InvalidTypeException
     */
    public function visit( Visitor $visitor, Generator $generator, $data )
    {
        if ( !is_object( $data ) )
        {
            throw new Exceptions\InvalidTypeException( $data );
        }
        $checkedClassNames = array();

        $className = get_class( $data );
        do
        {
            $checkedClassNames[] = $className;
            if ( isset( $this->visitors[$className] ) )
            {
                return $this->visitors[$className]->visit( $visitor, $generator, $data );
            }
        }
        while ( $className = get_parent_class( $className ) );

        throw new Exceptions\NoVisitorFoundException( $checkedClassNames );
    }
}
