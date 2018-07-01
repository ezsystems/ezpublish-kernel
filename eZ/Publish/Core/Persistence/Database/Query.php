<?php

/**
 * File containing an interface for the database abstractions.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Database;

use PDO;

/**
 * @property-read \eZ\Publish\Core\Persistence\Database\Expression $expr
 *
 * @deprecated Since 6.13, please use Doctrine DBAL instead (@ezpublish.persistence.connection)
 *             it provides richer and more powerful DB abstraction which is also easier to use.
 */
interface Query
{
    /**
     * Create a subselect used with the current query.
     *
     * @return \eZ\Publish\Core\Persistence\Database\SelectQuery
     */
    public function subSelect();

    /**
     * @return \PDOStatement
     */
    public function prepare();

    /**
     * Binds the value $value to the specified variable name $placeHolder.
     *
     * This method provides a shortcut for PDOStatement::bindValue
     * when using prepared statements.
     *
     * The parameter $value specifies the value that you want to bind. If
     * $placeholder is not provided bindValue() will automatically create a
     * placeholder for you. An automatic placeholder will be of the name
     * 'placeholder1', 'placeholder2' etc.
     *
     * For more information see {@link http://php.net/pdostatement-bindparam}
     *
     * Example:
     * <code>
     * $value = 2;
     * $q->eq( 'id', $q->bindValue( $value ) );
     * $stmt = $q->prepare(); // the value 2 is bound to the query.
     * $value = 4;
     * $stmt->execute(); // executed with 'id = 2'
     * </code>
     *
     * @param mixed $value
     * @param string $placeHolder the name to bind with. The string must start with a colon ':'.
     *
     * @return string the placeholder name used.
     */
    public function bindValue($value, $placeHolder = null, $type = PDO::PARAM_STR);

    /**
     * Binds the parameter $param to the specified variable name $placeHolder..
     *
     * This method provides a shortcut for PDOStatement::bindParam
     * when using prepared statements.
     *
     * The parameter $param specifies the variable that you want to bind. If
     * $placeholder is not provided bind() will automatically create a
     * placeholder for you. An automatic placeholder will be of the name
     * 'placeholder1', 'placeholder2' etc.
     *
     * For more information see {@link http://php.net/pdostatement-bindparam}
     *
     * Example:
     * <code>
     * $value = 2;
     * $q->eq( 'id', $q->bindParam( $value ) );
     * $stmt = $q->prepare(); // the parameter $value is bound to the query.
     * $value = 4;
     * $stmt->execute(); // executed with 'id = 4'
     * </code>
     *
     * @param &mixed $param
     * @param string $placeHolder the name to bind with. The string must start with a colon ':'.
     *
     * @return string the placeholder name used.
     */
    public function bindParam(&$param, $placeHolder = null, $type = PDO::PARAM_STR);

    /**
     * Return the SQL string for this query.
     *
     * @return string
     */
    public function getQuery();

    /**
     * Return the SQL string for this query.
     *
     * @return string
     */
    public function __toString();
}
