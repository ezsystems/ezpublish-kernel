<?php
/**
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\QueryType;

class NamedQueryTypeRegistry implements QueryTypeRegistry
{
    private $registry = array();

    public function getQueryType( $identifier )
    {
        $this->registry[$identifier];
    }

    public function registerQueryTypes( array $queryTypes )
    {
        $this->registry = array_merge( $queryTypes, $this->registry );
    }
}
