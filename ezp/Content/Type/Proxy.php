<?php
/**
 * File containing Proxy Content Type class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\Type;
use ezp\Base\Proxy as BaseProxy,
    ezp\Base\ModelDefinition,
    ezp\Content\Type;

/**
 * This class represents a Proxy Content Type
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
 * @property-read int[] $groupIds
 * @property Type\FieldDefinition[] $fields Appending items after it has been created has no effect, use TypeService->addFieldDefinition()
 * @property-read Type\Group[] $groups Appended items after it has been created has no effect, use TypeService->link()
 */
class Proxy extends BaseProxy implements ModelDefinition, Type
{
    public function __construct( $id, Service $service )
    {
        parent::__construct( $id, $service );
    }

    /**
     * Returns definition of the content type object, atm: permissions
     *
     * @access private
     * @return array
     */
    public static function definition()
    {
        return Concrete::definition();
    }

    /**
     * @return \ezp\Content\Type\FieldDefinition[]
     */
    public function getFields()
    {
        $this->lazyLoad();
        return $this->proxiedObject->getFields();
    }

    /**
     * @return \ezp\Content\Type\Group[]
     */
    public function getGroups()
    {
        $this->lazyLoad();
        return $this->proxiedObject->getGroups();
    }
}
