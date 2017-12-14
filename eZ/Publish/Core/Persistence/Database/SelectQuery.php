<?php

/**
 * File containing an interface for the database abstractions.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Database;

/**
 * @property-read \eZ\Publish\Core\Persistence\Database\Expression $expr
 *
 * @deprecated Since 6.13, please use Doctrine DBAL instead (@ezpublish.persistence.connection)
 *             it provides richer and more powerful DB abstraction which is also easier to use.
 */
interface SelectQuery extends Query
{
    const ASC = 'ASC';
    const DESC = 'DESC';

    /**
     * Opens the query and selects which columns you want to return with
     * the query.
     *
     * select() accepts an arbitrary number of parameters. Each parameter
     * must contain either the name of a column or an array containing
     * the names of the columns.
     * Each call to select() appends columns to the list of columns that will be
     * used in the query.
     *
     * Example:
     * <code>
     * $q->select( 'column1', 'column2' );
     * </code>
     * The same could also be written
     * <code>
     * $columns[] = 'column1';
     * $columns[] = 'column2;
     * $q->select( $columns );
     * </code>
     * or using several calls
     * <code>
     * $q->select( 'column1' )->select( 'column2' );
     * </code>
     *
     * Each of above code produce SQL clause 'SELECT column1, column2' for the query.
     *
     * @throws \eZ\Publish\Core\Persistence\Database\QueryException if called with no parameters..
     *
     * @param string|array(string) $... Either a string with a column name or an array of column names.
     *
     * @return \eZ\Publish\Core\Persistence\Database\SelectQuery returns a pointer to $this.
     */
    public function select();

    /**
     * Returns SQL to create an alias.
     *
     * This method can be used to create an alias for either a
     * table or a column.
     * Example:
     * <code>
     * // this will make the table users have the alias employees
     * // and the column user_id the alias employee_id
     * $q->select( $q->alias( 'user_id', 'employee_id' )
     *   ->from( $q->alias( 'users', 'employees' ) );
     * </code>
     *
     * @param string $name
     * @param string $alias
     *
     * @return string the query string "columnname as targetname"
     */
    public function alias($name, $alias);

    /**
     * Opens the query and uses a distinct select on the columns you want to
     * return with the query.
     *
     * selectDistinct() accepts an arbitrary number of parameters. Each
     * parameter  must contain either the name of a column or an array
     * containing the names of the columns.
     * Each call to selectDistinct() appends columns to the list of columns
     * that will be used in the query.
     *
     * Example:
     * <code>
     * $q->selectDistinct( 'column1', 'column2' );
     * </code>
     * The same could also be written
     * <code>
     * $columns[] = 'column1';
     * $columns[] = 'column2;
     * $q->selectDistinct( $columns );
     * </code>
     * or using several calls
     * <code>
     * $q->selectDistinct( 'column1' )->select( 'column2' );
     * </code>
     *
     * Each of above code produce SQL clause 'SELECT DISTINCT column1, column2'
     * for the query.
     *
     * You may call select() after calling selectDistinct() which will result
     * in the additional columns beein added. A call of selectDistinct() after
     * select() will result in an \eZ\Publish\Core\Persistence\Database\SelectQueryInvalidException.
     *
     * @throws \eZ\Publish\Core\Persistence\Database\QueryException if called with no parameters..
     *
     * @param string|array(string) $... Either a string with a column name or an array of column names.
     *
     * @return \eZ\Publish\Core\Persistence\Database\SelectQuery returns a pointer to $this.
     */
    public function selectDistinct();

    /**
     * Select which tables you want to select from.
     *
     * from() accepts an arbitrary number of parameters. Each parameter
     * must contain either the name of a table or an array containing
     * the names of tables..
     * Each call to from() appends tables to the list of tables that will be
     * used in the query.
     *
     * Example:
     * <code>
     * // the following code will produce the SQL
     * // SELECT id FROM table_name
     * $q->select( 'id' )->from( 'table_name' );
     * </code>
     *
     * @throws \eZ\Publish\Core\Persistence\Database\QueryException if called with no parameters.
     *
     * @param string|array(string) $... Either a string with a table name or an array of table names.
     *
     * @return \eZ\Publish\Core\Persistence\Database\SelectQuery a pointer to $this
     */
    public function from();

    /**
     * Returns the SQL for an inner join or prepares $fromString for an inner join.
     *
     * This method could be used in two forms:
     *
     * <b>innerJoin( 't2', $joinCondition )</b>
     *
     * Takes 2 string arguments and returns \eZ\Publish\Core\Persistence\Database\SelectQuery.
     *
     * The first parameter is the name of the table to join with. The table to
     * which is joined should have been previously set with the from() method.
     *
     * The second parameter should be a string containing a join condition that
     * is returned by an \eZ\Publish\Core\Persistence\Database\SelectQueryExpression.
     *
     * Example:
     * <code>
     * // the following code will produce the SQL
     * // SELECT id FROM t1 INNER JOIN t2 ON t1.id = t2.id
     * $q->select( 'id' )->from( 't1' )->innerJoin( 't2', $q->expr->eq('t1.id', 't2.id' ) );
     * </code>
     *
     * <b>innerJoin( 't2', 't1.id', 't2.id' )</b>
     *
     * Takes 3 string arguments and returns \eZ\Publish\Core\Persistence\Database\SelectQuery. This is a simplified form
     * of the 2 parameter version.  innerJoin( 't2', 't1.id', 't2.id' ) is
     * equal to innerJoin( 't2', $this->expr->eq('t1.id', 't2.id' ) );
     *
     * The first parameter is the name of the table to join with. The table to
     * which is joined should have been previously set with the from() method.
     *
     * The second parameter is the name of the column on the table set
     * previously with the from() method and the third parameter the name of
     * the column to join with on the table that was specified in the first
     * parameter.
     *
     * Example:
     * <code>
     * // the following code will produce the SQL
     * // SELECT id FROM t1 INNER JOIN t2 ON t1.id = t2.id
     * $q->select( 'id' )->from( 't1' )->innerJoin( 't2', 't1.id', 't2.id' );
     * </code>
     *
     * @throws \eZ\Publish\Core\Persistence\Database\QueryException if called with inconsistent parameters or if
     *         invoked without preceding call to from().
     *
     * @param string $table2,... The table to join with, followed by either the
     *                           two join columns, or a join condition.
     *
     * @return \eZ\Publish\Core\Persistence\Database\SelectQuery
     */
    public function innerJoin();

    /**
     * Returns the SQL for a left join or prepares $fromString for a left join.
     *
     * This method could be used in two forms:
     *
     * <b>leftJoin( 't2', $joinCondition )</b>
     *
     * Takes 2 string arguments and returns \eZ\Publish\Core\Persistence\Database\SelectQuery.
     *
     * The first parameter is the name of the table to join with. The table to
     * which is joined should have been previously set with the from() method.
     *
     * The second parameter should be a string containing a join condition that
     * is returned by an \eZ\Publish\Core\Persistence\Database\SelectQueryExpression.
     *
     * Example:
     * <code>
     * // the following code will produce the SQL
     * // SELECT id FROM t1 LEFT JOIN t2 ON t1.id = t2.id
     * $q->select( 'id' )->from( 't1' )->leftJoin( 't2', $q->expr->eq('t1.id', 't2.id' ) );
     * </code>
     *
     * <b>leftJoin( 't2', 't1.id', 't2.id' )</b>
     *
     * Takes 3 string arguments and returns \eZ\Publish\Core\Persistence\Database\SelectQuery. This is a simplified form
     * of the 2 parameter version.  leftJoin( 't2', 't1.id', 't2.id' ) is
     * equal to leftJoin( 't2', $this->expr->eq('t1.id', 't2.id' ) );
     *
     * The first parameter is the name of the table to join with. The table to
     * which is joined should have been previously set with the from() method.
     *
     * The second parameter is the name of the column on the table set
     * previously with the from() method and the third parameter the name of
     * the column to join with on the table that was specified in the first
     * parameter.
     *
     * Example:
     * <code>
     * // the following code will produce the SQL
     * // SELECT id FROM t1 LEFT JOIN t2 ON t1.id = t2.id
     * $q->select( 'id' )->from( 't1' )->leftJoin( 't2', 't1.id', 't2.id' );
     * </code>
     *
     * @throws \eZ\Publish\Core\Persistence\Database\QueryException if called with inconsistent parameters or if
     *         invoked without preceding call to from().
     *
     * @param string $table2,... The table to join with, followed by either the
     *                           two join columns, or a join condition.
     *
     * @return \eZ\Publish\Core\Persistence\Database\SelectQuery
     */
    public function leftJoin();

    /**
     * Returns the SQL for a right join or prepares $fromString for a right join.
     *
     * This method could be used in two forms:
     *
     * <b>rightJoin( 't2', $joinCondition )</b>
     *
     * Takes 2 string arguments and returns \eZ\Publish\Core\Persistence\Database\SelectQuery.
     *
     * The first parameter is the name of the table to join with. The table to
     * which is joined should have been previously set with the from() method.
     *
     * The second parameter should be a string containing a join condition that
     * is returned by an \eZ\Publish\Core\Persistence\Database\SelectQueryExpression.
     *
     * Example:
     * <code>
     * // the following code will produce the SQL
     * // SELECT id FROM t1 LEFT JOIN t2 ON t1.id = t2.id
     * $q->select( 'id' )->from( 't1' )->rightJoin( 't2', $q->expr->eq('t1.id', 't2.id' ) );
     * </code>
     *
     * <b>rightJoin( 't2', 't1.id', 't2.id' )</b>
     *
     * Takes 3 string arguments and returns \eZ\Publish\Core\Persistence\Database\SelectQuery. This is a simplified form
     * of the 2 parameter version.  rightJoin( 't2', 't1.id', 't2.id' ) is
     * equal to rightJoin( 't2', $this->expr->eq('t1.id', 't2.id' ) );
     *
     * The first parameter is the name of the table to join with. The table to
     * which is joined should have been previously set with the from() method.
     *
     * The second parameter is the name of the column on the table set
     * previously with the from() method and the third parameter the name of
     * the column to join with on the table that was specified in the first
     * parameter.
     *
     * Example:
     * <code>
     * // the following code will produce the SQL
     * // SELECT id FROM t1 LEFT JOIN t2 ON t1.id = t2.id
     * $q->select( 'id' )->from( 't1' )->rightJoin( 't2', 't1.id', 't2.id' );
     * </code>
     *
     * @throws \eZ\Publish\Core\Persistence\Database\QueryException if called with inconsistent parameters or if
     *         invoked without preceding call to from().
     *
     * @param string $table2,... The table to join with, followed by either the
     *                           two join columns, or a join condition.
     *
     * @return \eZ\Publish\Core\Persistence\Database\SelectQuery
     */
    public function rightJoin();

    /**
     * Adds a where clause with logical expressions to the query.
     *
     * where() accepts an arbitrary number of parameters. Each parameter
     * must contain a logical expression or an array with logical expressions.
     * If you specify multiple logical expression they are connected using
     * a logical and.
     *
     * Multiple calls to where() will join the expressions using a logical and.
     *
     * Example:
     * <code>
     * $q->select( '*' )->from( 'table' )->where( $q->expr->eq( 'id', 1 ) );
     * </code>
     *
     * @throws \eZ\Publish\Core\Persistence\Database\QueryException if called with no parameters.
     *
     * @param string|array(string) $... Either a string with a logical expression name
     * or an array with logical expressions.
     *
     * @return \eZ\Publish\Core\Persistence\Database\SelectQuery
     */
    public function where();

    /**
     * Returns SQL that limits the result set.
     *
     * $limit controls the maximum number of rows that will be returned.
     * $offset controls which row that will be the first in the result
     * set from the total amount of matching rows.
     *
     * Example:
     * <code>
     * $q->select( '*' )->from( 'table' )
     *                  ->limit( 10, 0 );
     * </code>
     *
     * LIMIT is not part of SQL92. It is implemented here anyway since all
     * databases support it one way or the other and because it is
     * essential.
     *
     * @param string $limit integer expression
     * @param string $offset integer expression
     *
     * @return \eZ\Publish\Core\Persistence\Database\SelectQuery
     */
    public function limit($limit, $offset = '');

    /**
     * Returns SQL that orders the result set by a given column.
     *
     * You can call orderBy multiple times. Each call will add a
     * column to order by.
     *
     * Example:
     * <code>
     * $q->select( '*' )->from( 'table' )
     *                  ->orderBy( 'id' );
     * </code>
     *
     * @param string $column a column name in the result set
     * @param string $type if the column should be sorted ascending or descending.
     *        you can specify this using \eZ\Publish\Core\Persistence\Database\SelectQuerySelect::ASC or \eZ\Publish\Core\Persistence\Database\SelectQuerySelect::DESC
     *
     * @return \eZ\Publish\Core\Persistence\Database\SelectQuery a pointer to $this
     */
    public function orderBy($column, $type = self::ASC);

    /**
     * Returns SQL that groups the result set by a given column.
     *
     * You can call groupBy multiple times. Each call will add a
     * column to group by.
     *
     * Example:
     * <code>
     * $q->select( '*' )->from( 'table' )
     *                  ->groupBy( 'id' );
     * </code>
     *
     * @throws \eZ\Publish\Core\Persistence\Database\QueryException if called with no parameters.
     *
     * @param string $column a column name in the result set
     *
     * @return \eZ\Publish\Core\Persistence\Database\SelectQuery a pointer to $this
     */
    public function groupBy();

    /**
     * Returns SQL that set having by a given expression.
     *
     * You can call having multiple times. Each call will add an
     * expression with a logical and.
     *
     * Example:
     * <code>
     * $q->select( '*' )->from( 'table' )->groupBy( 'id' )
     *                  ->having( $q->expr->eq('id',1) );
     * </code>
     *
     * @throws \eZ\Publish\Core\Persistence\Database\QueryException
     *         if called with no parameters.
     *
     * @param string|array(string) $... Either a string with a logical expression name
     *                             or an array with logical expressions.
     *
     * @return \eZ\Publish\Core\Persistence\Database\SelectQuery a pointer to $this
     */
    public function having();
}
