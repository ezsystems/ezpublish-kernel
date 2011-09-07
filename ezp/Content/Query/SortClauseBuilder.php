<?php
/**
 * File containing the \ezp\Content\Query\SortClauseBuilder class.
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\Query;

use BadFunctionCallException,
    ezp\Content\Query,
    ReflectionClass;

/**
 * This class provides the fluent factory interface for Content queries SortClause items
 *
 * @call \ezp\Persistence\Content\Query\SortClause\SectionIdentifier sectionIdentifier( string $sortDirection )
 */
class SortClauseBuilder
{
    /**
     * Intercepts all SortClause calls, and returns the matching SortClause object
     *
     * @return \ezp\Persistence\Content\Query\SortClause
     *
     * @throws BadFunctionCalException If the sort clause doesn't exist
     */
    public function __call( $method, $args )
    {
        $sortClauseClass = '\\ezp\\Persistence\\Content\\Query\\SortClause\\' . ucfirst( $method );
        if ( !class_exists( $sortClauseClass ) )
        {
            // @todo Add proper exception
            throw new BadFunctionCallException( "SortClause class $sortClauseClass not found" );
        }

        $reflectionClass = new ReflectionClass( $sortClauseClass );
        $argumentsCount = $reflectionClass->getConstructor()->getNumberOfParameters();

        // One argument: no extra parameters, only sort direction
        if ( $argumentsCount === 1 )
        {
            if ( count( $args ) === 1 )
            {
                $sortDirection = $args[0];
            }
            else
            {
                $sortDirection = Query::SORT_ASC;
            }
            return new $sortClauseClass( $sortDirection );
        }
        // More than one argument: extra parameters, use reflection
        else
        {
            if ( count( $args ) < $argumentsCount )
            {
                array_push( $args, Query::SORT_ASC );
            }
            return  $reflectionClass->newInstanceArgs( $args );
        }
    }
}
?>