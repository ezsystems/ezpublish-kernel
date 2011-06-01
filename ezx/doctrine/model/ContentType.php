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
 */
namespace ezx\doctrine\model;
class ContentType extends Abstract_Model
{
    public function __construct()
    {
        $this->groups = new SerializableCollection();
        $this->fields = new SerializableCollection();
        $this->contentObjects = new SerializableCollection();
    }

    /**
     * @Id @Column(type="integer") @GeneratedValue
     * @var int
     */
    public $id;

    /**
     * @Column(type="integer")
     * @var int
     */
    public $version;

    /**
     * @Column(length=50)
     * @var string
     */
    public $identifier;

    /**
     * @OneToMany(targetEntity="ContentTypeField", mappedBy="contentType")
     * @var SerializableCollection(ContentTypeField)
     */
    protected $fields;

    /**
     * Return collection of all fields assigned to object (all versions and languages)
     *
     * @return SerializableCollection(ContentTypeField)
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * @OneToMany(targetEntity="Content", mappedBy="contentType")
     * @var SerializableCollection(Content)
     */
    protected $contentObjects = array() ;

    /**
     * Return collection of all content objects of this content type
     *
     * @return SerializableCollection(Content)
     */
    public function getContentObjects()
    {
        return $this->contentObjects;
    }

    /**
     * @ManyToMany(targetEntity="ContentTypeGroup", mappedBy="contentTypes")
     * @var SerializableCollection(ContentTypeGroup)
     */
    //private $groups;

    /**
     * Return collection of ContentTypeGroup
     *
     * @return SerializableCollection(ContentTypeGroup)
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
