<?php
/**
 * Content Type (content class) model object
 *
 * @copyright Copyright (c) 2011, eZ Systems AS
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2.0
 * @package ext
 * @subpackage doctrine
 */

/**
 * @Entity @Table(name="ezcontentclass")
 *
 * @property-read int $id
 * @property-read int $version
 * @property-read string $identifier
 * @property-read array(ContentTypeField) $fields
 * @property-read array(ContentTypeGroup) $groups
 */
namespace ezx\doctrine\content;
class ContentType extends Abstract_ContentModel
{
    protected static $definition = array(
        'id' => array( 'type' => self::TYPE_INT, 'internal' => true ),
        'version' => array( 'type' => self::TYPE_INT, 'internal' => true ),
        'identifier' => array( 'type' => self::TYPE_STRING ),
        'fields' => array( 'type' => self::TYPE_ARRAY ),
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
     * @OneToMany(targetEntity="ContentTypeField", mappedBy="contentType")
     * @var \Doctrine\Common\Collections\ArrayCollection(ContentTypeField)
     */
    protected $fields;

    /**
     * Return collection of all fields assigned to object (all versions and languages)
     *
     * @return \Doctrine\Common\Collections\ArrayCollection(ContentTypeField)
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * @OneToMany(targetEntity="Content", mappedBy="contentType")
     * @var \Doctrine\Common\Collections\ArrayCollection(Content)
     */
    protected $contentObjects;

    /**
     * Return collection of all content objects of this content type
     *
     * @return \Doctrine\Common\Collections\ArrayCollection(Content)
     */
    public function getContentObjects()
    {
        return $this->contentObjects;
    }

    /**
     * @ManyToMany(targetEntity="ContentTypeGroup", mappedBy="contentTypes")
     * @var \Doctrine\Common\Collections\ArrayCollection(ContentTypeGroup)
     */
    protected $groups;

    /**
     * Return collection of ContentTypeGroup
     *
     * @return \Doctrine\Common\Collections\ArrayCollection(ContentTypeGroup)
     */
    public function getGroups()
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
