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
     * @var \ezp\Content
     */
    protected $content;

    /**
     * @param mixed $id
     * @param int $versionNo
     * @param \ezp\Content\Service $service
     */
    public function __construct( Content $content, $versionNo, Service $service )
    {
        $this->versionNo = $versionNo;
        $this->content = $content;
        parent::__construct( $content->id, $service );
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
            $versions = $this->content->getVersions();
            $this->proxiedObject = $versions[ $this->versionNo ];
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
