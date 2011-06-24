<?php
/**
 * Content Type group (content class group) domain object
 *
 * @copyright Copyright (c) 2011, eZ Systems AS
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2.0
 * @package ext
 * @subpackage content
 */

/**
 * @Entity @Table(name="ezcontentclassgroup")
 */
namespace ezx\content;
class ContentTypeGroup extends Abstracts\ContentModel
{
    /**
     * @var array Readable of properties on this object
     */
    protected $readableProperties = array(
        'id' => false,
        'version' => false,
        //'identifier' => true,
    );

    /**
     * @var array Dynamic properties on this object
     */
    protected $dynamicProperties = array(
        'contentTypes' => false,
    );

    public function __construct()
    {
        $this->contentTypes = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * @Id @Column(type="integer") @GeneratedValue
     * @var int
     */
    protected $id;

    /**
     * @Column(length=50)
     * @var string
     */
    protected $name;

    /**
     * @ManyToMany(targetEntity="ContentType", mappedBy="groups")
     * @var ContentType[]
     */
    protected $contentTypes;

    /**
     * Return collection of all content objects of this content type
     *
     * @return ContentType[]
     */
    public function getContentTypes()
    {
        return $this->contentTypes;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->id;
    }
}
