<?php
/**
 * Abstract Content Type Field (content class attribute) model object
 *
 * @copyright Copyright (c) 2011, eZ Systems AS
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2.0
 * @package ext
 * @subpackage doctrine
 */

/**
 * @Entity @Table(name=" ezcontentclass_attribute")
 */
namespace ezx\doctrine\model;
class ContentTypeField extends Abstract_Field
{
    public function __construct()
    {
        $this->contentFields = new SerializableCollection();
    }

    /**
     * @Id @Column(type="integer")
     * @var int
     */
    protected $id;

    /**
     * @Id @Column(type="integer")
     * @var int
     */
    protected $version;

    /**
     * @Column(type="integer", name="contentclass_id")
     * @var int
     */
    protected $contentTypeID;

    /**
     * @Column(length=50)
     * @var string
     */
    public $identifier;

    /**
     * @Column(length=50)
     * @var string
     */
    protected $data_text1;

    /**
     * @Column(length=50)
     * @var string
     */
    protected $data_text2;

    /**
     * @Column(length=50)
     * @var string
     */
    protected $data_text3;

    /**
     * @Column(length=50)
     * @var string
     */
    protected $data_text4;

    /**
     * @Column(length=50)
     * @var string
     */
    protected $data_text5;

    /**
     * @Column(length=50, name="data_type_string")
     * @var string
     */
    protected $fieldTypeString;


    /**
     * @Column(type="integer")
     * @var int
     */
    public $placement;

    /**
     * @ManyToOne(targetEntity="ContentType", inversedBy="fields")
     * @JoinColumn(name="contentclass_id", referencedColumnName="id")
     * @var ContentType
     */
    protected $contentType;

    /**
     * Return content type object
     *
     * @return ContentType
     */
    final public function getContentType()
    {
        return $this->contentType;
    }

    /**
     * @OneToMany(targetEntity="Field", mappedBy="contentTypeField")
     * @var SerializableCollection(Field)
     */
    protected $contentFields;

    /**
     * Return collection of all fields assigned to object (all versions and languages)
     *
     * @return SerializableCollection(Field)
     */
    public function getContentFields()
    {
        return $this->contentFields;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return  $this->id . ' ' . $this->version . ' (' . $this->identifier . ')';
    }
}