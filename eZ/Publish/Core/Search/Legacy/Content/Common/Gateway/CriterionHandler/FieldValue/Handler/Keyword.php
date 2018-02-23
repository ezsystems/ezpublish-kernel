<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\CriterionHandler\FieldValue\Handler;

use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\Core\Persistence\Database\SelectQuery;

/**
 * FieldValue CriterionHandler handling ezkeyword External Storage for Legacy/SQL Search.
 */
class Keyword extends Collection
{
    /**
     * Generates query expression for operator and value of a Field Criterion.
     *
     * @param \eZ\Publish\Core\Persistence\Database\SelectQuery $query
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $criterion
     * @param string $column
     *
     * @return \eZ\Publish\Core\Persistence\Database\Expression
     */
    public function handle(SelectQuery $query, Criterion $criterion, $column)
    {
        $query
            ->innerJoin(
                $this->dbHandler->quoteTable('ezkeyword_attribute_link'),
                'ezcontentobject_attribute.id',
                'ezkeyword_attribute_link.objectattribute_id'
            )->innerJoin(
                $this->dbHandler->quoteTable('ezkeyword'),
                'ezkeyword.id',
                'ezkeyword_attribute_link.keyword_id'
            );

        return parent::handle($query, $criterion, 'keyword');
    }
}
