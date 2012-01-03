<?php
/**
 * Content Type group (content class group) domain object
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\Type;
use ezp\Base\Model,
    ezp\Base\ModelDefinition,
    ezp\Base\Collection\Type as TypeCollection,
    ezp\Persistence\Content\Type\Group as GroupValue;

/**
 * Group class ( Content Class Group )
 *
 *
 * @property-read int $id
 * @property string[] $name
 * @property string[] $description
 * @property string $identifier
 * @property mixed $created
 * @property mixed $creatorId
 * @property mixed $modified
 * @property mixed $modifierId
 * @property-read \ezp\Content\Type[] $types Appended items will not be stored, use TypeService->link()
 */
interface Group extends ModelDefinition
{
    /**
     * @return \ezp\Content\Type[]
     */
    public function getTypes();
}
