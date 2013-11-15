<?php

namespace eZ\Publish\Core\Persistence\Database;

use PDO;

interface Query
{
    /**
     * Create a subselect used with the current query.
     *
     * @return \eZ\Publish\Core\Persistence\Database\SelectQuery
     */
    public function subSelect();
    public function prepare();
    public function bindValue( $value, $placeHolder = null, $type = PDO::PARAM_STR );
    public function bindParam( &$param, $placeHolder = null, $type = PDO::PARAM_STR );
}
