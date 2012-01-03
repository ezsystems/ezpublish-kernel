<?php
/**
 * File containing Content Type interface
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content;
use ezp\Base\ModelDefinition;

/**
 * Content Type interface
 *
 * @property-read mixed $id
 * @property-read int $status
 * @property string[] $name
 * @property string[] $description
 * @property string $identifier
 * @property mixed $created
 * @property mixed $creatorId
 * @property mixed $modified
 * @property mixed $modifierId
 * @property-read string $remoteId
 * @property string $urlAliasSchema
 * @property string $nameSchema
 * @property bool $isContainer
 * @property int $initialLanguageId
 * @property bool $defaultAlwaysAvailable
 * @property int $sortField Valid values are found at {@link \ezp\Content\Location::SORT_FIELD_*}
 * @property int $sortOrder Valid values are {@link \ezp\Content\Location::SORT_ORDER_*}
 * @property-read int[] $groupIds
 * @property Type\FieldDefinition[] $fields Appending items after it has been created has no effect, use TypeService->addFieldDefinition()
 * @property-read Type\Group[] $groups Appended items after it has been created has no effect, use TypeService->link()
 */
interface Type extends ModelDefinition
{
    /**
     * @return \ezp\Content\Type\FieldDefinition[]
     */
    public function getFields();

    /**
     * @return \ezp\Content\Type\Group[]
     */
    public function getGroups();
}
