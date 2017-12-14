<?php

/**
 * File containing an interface for the Doctrine database abstractions.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Doctrine;

use PDO;

/**
 * Class SubselectDoctrineQuery.
 *
 * @deprecated Since 6.13, please use Doctrine DBAL instead (@ezpublish.persistence.connection)
 *             it provides richer and more powerful DB abstraction which is also easier to use.
 */
class SubselectDoctrineQuery extends SelectDoctrineQuery
{
    /**
     * Holds the outer query.
     *
     * @var \eZ\Publish\Core\Persistence\Doctrine\SelectDoctrineQuery
     */
    protected $outerQuery = null;

    /**
     * Constructs a new ezcQuerySubSelect object.
     *
     * The subSelect() method of the ezcQuery object creates an object of this
     * class, and passes itself as $outer parameter to this constructor.
     *
     * @param \eZ\Publish\Core\Persistence\Doctrine\AbstractDoctrineQuery $outer
     */
    public function __construct(AbstractDoctrineQuery $outer)
    {
        $this->outerQuery = $outer;

        parent::__construct($outer->connection);
    }

    /**
     * Binds the parameter $param to the specified variable name $placeHolder.
     *
     * This method uses ezcQuery::bindParam() from the ezcQuery class in which
     * the subSelect was called. Info about bound parameters are stored in
     * the parent ezcQuery object that is stored in the $outer property.
     *
     * The parameter $param specifies the variable that you want to bind. If
     * $placeholder is not provided bind() will automatically create a
     * placeholder for you. An automatic placeholder will be of the name
     * 'placeholder1', 'placeholder2' etc.
     *
     * Example:
     * <code>
     * <?php
     * $value = 2;
     * $subSelect = $q->subSelect();
     * $subSelect->select('*')
     *            ->from( 'table2' )
     *            ->where( $subSelect->expr->in(
     *                  'id', $subSelect->bindParam( $value )
     *                   )
     *              );
     *
     * $q->select( '*' )
     *   ->from( 'table' )
     *   ->where ( $q->expr->eq( 'id', $subSelect ) );
     *
     * $stmt = $q->prepare(); // the parameter $value is bound to the query.
     * $value = 4;
     * $stmt->execute(); // subselect executed with 'id = 4'
     * ?>
     * </code>
     *
     * @see ezcQuery::bindParam()
     *
     * @param &mixed $param
     * @param string $placeHolder the name to bind with. The string must start with a colon ':'.
     *
     * @return string the placeholder name used.
     */
    public function bindParam(&$param, $placeHolder = null, $type = PDO::PARAM_STR)
    {
        return $this->outerQuery->bindParam($param, $placeHolder, $type);
    }

    /**
     * Binds the value $value to the specified variable name $placeHolder.
     *
     * This method uses ezcQuery::bindParam() from the ezcQuery class in which
     * the subSelect was called. Info about bound parameters are stored in
     * the parent ezcQuery object that is stored in the $outer property.
     *
     * The parameter $value specifies the value that you want to bind. If
     * $placeholder is not provided bindValue() will automatically create a
     * placeholder for you. An automatic placeholder will be of the name
     * 'placeholder1', 'placeholder2' etc.
     *
     * Example:
     * <code>
     * <?php
     * $value = 2;
     * $subSelect = $q->subSelect();
     * $subSelect->select( name )
     *          ->from( 'table2' )
     *          ->where(  $subSelect->expr->in(
     *                'id', $subSelect->bindValue( $value )
     *                 )
     *            );
     *
     * $q->select( '*' )
     *   ->from( 'table1' )
     *   ->where ( $q->expr->eq( 'name', $subSelect ) );
     *
     * $stmt = $q->prepare(); // the $value is bound to the query.
     * $value = 4;
     * $stmt->execute(); // subselect executed with 'id = 2'
     * ?>
     * </code>
     *
     * @see ezcQuery::bindValue()
     *
     * @param mixed $value
     * @param string $placeHolder the name to bind with. The string must start with a colon ':'.
     *
     * @return string the placeholder name used.
     */
    public function bindValue($value, $placeHolder = null, $type = PDO::PARAM_STR)
    {
        return $this->outerQuery->bindValue($value, $placeHolder, $type);
    }

    /**
     * Returns the SQL string for the subselect.
     *
     * Example:
     * <code>
     * <?php
     * $subSelect = $q->subSelect();
     * $subSelect->select( name )->from( 'table2' );
     * $q->select( '*' )
     *   ->from( 'table1' )
     *   ->where ( $q->expr->eq( 'name', $subSelect ) );
     * $stmt = $q->prepare();
     * $stmt->execute();
     * ?>
     * </code>
     *
     * @return string
     */
    public function getQuery()
    {
        return '( ' . parent::getQuery() . ' )';
    }

    /**
     * Returns ezcQuerySubSelect of deeper level.
     *
     * Used for making subselects inside subselects.
     *
     * Example:
     * <code>
     * <?php
     * $value = 2;
     * $subSelect = $q->subSelect();
     * $subSelect->select( name )
     *           ->from( 'table2' )
     *           ->where( $subSelect->expr->in(
     *                 'id', $subSelect->bindValue( $value )
     *                  )
     *             );
     *
     * $q->select( '*' )
     *   ->from( 'table1' )
     *   ->where ( $q->expr->eq( 'name', $subSelect ) );
     *
     * $stmt = $q->prepare(); // the $value is bound to the query.
     * $value = 4;
     * $stmt->execute(); // subselect executed with 'id = 2'
     * ?>
     * </code>
     *
     * @return \eZ\Publish\Core\Persistence\Database\SelectQuery
     */
    public function subSelect()
    {
        return new self($this->outerQuery);
    }
}
