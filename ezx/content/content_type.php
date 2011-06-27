<?php
/**
 * Content Type (content class) domain object
 *
 * @copyright Copyright (c) 2011, eZ Systems AS
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2.0
 * @package ext
 * @subpackage content
 */

/**
 * @Entity @Table(name="ezcontentclass")
 *
 * @property-read int $id
 * @property-read int $version
 * @property-read string $identifier
 * @property-read ContentTypeField[] $fields
 * @property-read ContentTypeGroup[] $groups
 */
namespace ezx\content;
class ContentType extends \ezp\base\AbstractModel
{
    /**
     * @var array Readable of properties on this object
     */
    protected $readableProperties = array(
        'id' => false,
        'version' => false,
        'identifier' => true,
    );

    /**
     * @var array Dynamic properties on this object
     */
    protected $dynamicProperties = array(
        'contentObjects' => false,
        'groups' => true,
        'fields' => true,
    );

    public function __construct()
    {
        $this->groups = new \Doctrine\Common\Collections\ArrayCollection();
        $this->fields = new \Doctrine\Common\Collections\ArrayCollection();
        $this->contentObjects = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * @Id @Column(type="integer") @GeneratedValue
     * @var int
     */
    protected $id;

    /**
     * @Column(type="integer")
     * @var int
     */
    protected $version;

    /**
     * @Column(length=50)
     * @var string
     */
    protected $identifier;

    /**
     * @OneToMany(targetEntity="ContentTypeField", mappedBy="contentType", fetch="EAGER")
     * @var ContentTypeField[]
     */
    protected $fields;

    /**
     * Return collection of all fields assigned to object (all versions and languages)
     *
     * @return ContentTypeField[]
     */
    protected function getFields()
    {
        return $this->fields;
    }

    /**
     * @OneToMany(targetEntity="Content", mappedBy="contentType")
     * @var Content[]
     */
    protected $contentObjects;

    /**
     * Return collection of all content objects of this content type
     *
     * @return Content[]
     */
    protected function getContentObjects()
    {
        return $this->contentObjects;
    }

    /**
     * @ManyToMany(targetEntity="ContentTypeGroup", mappedBy="contentTypes")
     * @var ContentTypeGroup[]
     */
    protected $groups;

    /**
     * Return collection of ContentTypeGroup
     *
     * @return ContentTypeGroup[]
     */
    protected function getGroups()
    {
        return $this->groups;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->id . ' ('  . $this->identifier . ')';
    }
}
