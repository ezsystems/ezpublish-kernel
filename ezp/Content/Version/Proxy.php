<?php
/**
 * File containing the ezp\Content\Version\Proxy class.
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\Version;
use ezp\Base\Proxy\Model as ModelProxy,
    ezp\Content,
    ezp\Content\Version,
    ezp\Content\Version\Concrete,
    ezp\Content\Service;

/**
 * This class represents a Proxy Content Version
 *
 *
 * @property-read int $id
 * @property-read int $versionNo
 * @property-read mixed $contentId
 * @property-read int $status One of the STATUS_* constants
 * @property-read \ezp\Content $content
 * @property mixed $initialLanguageId
 * @property int $creatorId
 * @property int $created
 * @property int $modified
 * @property-read ContentField[] $fields An hash structure of fields
 */
class Proxy extends ModelProxy implements Version
{
    /**
     * @var int
     */
    protected $versionNo;

    /**
     * @param mixed $contentId
     * @param int $versionNo
     * @param \ezp\Content\Service $service
     */
    public function __construct( $contentId, $versionNo, Service $service )
    {
        $this->versionNo = $versionNo;
        parent::__construct( $contentId, $service );
    }

    /**
     * Overload to get version by Content object as there is no api to load version object atm
     *
     * @return void
     */
    protected function lazyLoad()
    {
        if ( $this->proxiedObject === null )
        {
            $this->proxiedObject = $this->service->loadVersion( $this->id, $this->versionNo );
            $this->moveObservers();
        }
    }

    /**
     * Returns definition of the content object, atm: permissions
     *
     * @access private
     * @return array
     */
    public static function definition()
    {
        return Concrete::definition();
    }

    /**
     * Provides read access to a $property
     *
     * @param string $property
     * @return mixed
     */
    public function __get( $property )
    {
        if ( $property === "contentId" )
            return $this->id;
        if ( $property === "versionNo" )
            return $this->versionNo;

        $this->lazyLoad();
        return $this->proxiedObject->$property;
    }

    /**
     * Return Type object
     *
     * @return \ezp\Content
     */
    public function getContent()
    {
        $this->lazyLoad();
        return $this->proxiedObject->getContent();
    }

    /**
     * Get fields of current version
     *
     * @return \ezp\Content\Field[]
     */
    public function getFields()
    {
        $this->lazyLoad();
        return $this->proxiedObject->getFields();
    }
}
