<?php
/**
 * File containing Proxy Content Type class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\Type\Group;
use ezp\Base\Proxy\Model as ModelProxy,
    ezp\Content\Type\Group,
    ezp\Content\Type\Service;

/**
 * Proxy Group class ( Content Class Group )
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
class Proxy extends ModelProxy implements Group
{
    /**
     * @param mixed $id
     * @param \ezp\Content\Type\Service $service
     */
    public function __construct( $id, Service $service )
    {
        parent::__construct( $id, $service );
    }

    /**
     * @return void
     */
    protected function lazyLoad()
    {
        if ( $this->proxiedObject === null )
        {
            $this->proxiedObject = $this->service->loadGroup( $this->id );
        }
    }

    /**
     * @return \ezp\Content\Type[]
     */
    public function getTypes()
    {
        $this->lazyLoad();
        return $this->proxiedObject->getTypes();
    }
}
