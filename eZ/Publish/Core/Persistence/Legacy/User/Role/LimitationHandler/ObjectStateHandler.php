<?php

/**
 * File containing the abstract Limitation handler.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Legacy\User\Role\LimitationHandler;

use eZ\Publish\API\Repository\Values\User\Limitation;
use eZ\Publish\Core\Persistence\Legacy\User\Role\LimitationHandler;
use eZ\Publish\SPI\Persistence\User\Policy;

/**
 * Limitation Handler.
 *
 * Takes care of Converting a Policy limitation from Legacy value to spi value accepted by API.
 */
class ObjectStateHandler extends LimitationHandler
{
    const STATE_GROUP = 'StateGroup_';

    /**
     * Translate API STATE limitation to Legacy StateGroup_<identifier> limitations.
     *
     * @param Policy $policy
     */
    public function toLegacy(Policy $policy)
    {
        if ($policy->limitations !== '*' && isset($policy->limitations[Limitation::STATE])) {
            if ($policy->limitations[Limitation::STATE] === '*') {
                $map = array_fill_keys(array_keys($this->getGroupMap()), '*');
            } else {
                $map = $this->getGroupMap($policy->limitations[Limitation::STATE]);
            }
            $policy->limitations += $map;
            unset($policy->limitations[Limitation::STATE]);
        }
    }

    /**
     * Translate Legacy StateGroup_<identifier> limitations to API STATE limitation.
     *
     * @param Policy $policy
     */
    public function toSPI(Policy $policy)
    {
        if ($policy->limitations === '*' || empty($policy->limitations)) {
            return;
        }

        // First iterate to prepare for the range of possible conditions below
        $hasStateGroup = false;
        $allWildCard = true;
        $someWildCard = false;
        $map = [];
        foreach ($policy->limitations as $identifier => $limitationsValues) {
            if (strncmp($identifier, self::STATE_GROUP, 11) === 0) {
                $hasStateGroup = true;
                if ($limitationsValues !== '*') {
                    $allWildCard = false;
                } else {
                    $someWildCard = true;
                }

                $map[$identifier] = $limitationsValues;
                unset($policy->limitations[$identifier]);
            }
        }

        if (!$hasStateGroup) {
            return;
        }

        if ($allWildCard) {
            $policy->limitations[Limitation::STATE] = '*';

            return;
        }

        if ($someWildCard) {
            $fullMap = $this->getGroupMap();
            foreach ($map as $identifier => $limitationsValues) {
                if ($limitationsValues === '*') {
                    $map[$identifier] = $fullMap[$identifier];
                }
            }
        }

        $policy->limitations[Limitation::STATE] = [];
        foreach ($map as $limitationValues) {
            $policy->limitations[Limitation::STATE] = array_merge(
                $policy->limitations[Limitation::STATE],
                $limitationValues
            );
        }
    }

    /**
     * Query for groups identifiers and id's.
     */
    protected function getGroupMap(array $limitIds = null)
    {
        $query = $this->dbHandler->createSelectQuery();
        $query->select(
            $this->dbHandler->quoteColumn('identifier', 'ezcobj_state_group'),
            $this->dbHandler->quoteColumn('id', 'ezcobj_state')
        )->from(
            $this->dbHandler->quoteTable('ezcobj_state')
        )->innerJoin(
            $this->dbHandler->quoteTable('ezcobj_state_group'),
            $query->expr->eq(
                $this->dbHandler->quoteColumn('group_id', 'ezcobj_state'),
                $this->dbHandler->quoteColumn('id', 'ezcobj_state_group')
            )
        );

        if ($limitIds !== null) {
            $query->where(
                $query->expr->in(
                    $this->dbHandler->quoteColumn('id', 'ezcobj_state'),
                    array_map('intval', $limitIds)
                )
            );
        }

        $statement = $query->prepare();
        $statement->execute();

        $map = [];
        $groupValues = $statement->fetchAll(\PDO::FETCH_ASSOC);
        foreach ($groupValues as $groupValue) {
            $map[self::STATE_GROUP . $groupValue['identifier']][] = (int)$groupValue['id'];
        }

        return $map;
    }
}
