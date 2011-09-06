<?php
/**
 * File containing ModelDefinition interface
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Base;

/**
 * Interface for model definition
 *
 */
interface ModelDefinition
{
    /**
     * Returns definition of the model object, eg: permissions(, properties)...
     *
     * Return value example:
     * array(
     *     'module' => 'content',
     *     'functions' => array(
     *         'read' => array(
     *             'Class' => array(
     *                 'options' => function( Repository $repository )
     *                 {
     *                     return $repository->getContentTypeService()->loadAll( $idNamePair = true );
     *                 },
     *                 'compare' => function( Content $model, array $limitationsValues[, Repository $repository[, Model $model2 = null]] )
     *                 {
     *                     return in_array( $model->typeId, $limitationsValues );
     *                 },
     *                 'query' => function( Criterion $criterion, array $limitationsValues[, Repository $repository] )
     *                 {
     *                     if ( !$criterion instanceof Criterion\LogicalAnd )
     *                         $criterion = new Criterion\LogicalAnd( $criterion );
     *
     *                     foreach ( $limitationsValues as $classId )
     *                     {
     *                         $criterion->append( new Criterion\ContentTypeId( $classId ) );
     *                     }
     *                 },
     *             )
     *         )
     *     )
     * );
     *
     * Where 'read' is function and 'Class' is limitation identifier.
     *
     * Bellow that are three possible keys: 'options', a callback returning a list of options
     * or a list of options, where key is scalar value identifying limitation and value is human readable name.
     * The second key is 'compare', a callback comparing instance of an object against limitation values.
     * The third possible key is 'query', a callback that modifies criterion object to include filtering rules
     * for the limitation.
     *
     * @access private
     * @return array
     */
    public function definition();
}
?>
