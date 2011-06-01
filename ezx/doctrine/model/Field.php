<?php
/**
 * Abstract Content Field (content attribute) model object
 *
 * @copyright Copyright (c) 2011, eZ Systems AS
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2.0
 * @package ext
 * @subpackage doctrine
 */

/**
 * @Entity @Table(name="ezcontentobject_attribute")
 */
namespace ezx\doctrine\model;
class Field extends Abstract_Field implements Interface_Observer
{
    /**
     * @Id @Column(type="integer")
     * @var int
     */
    protected $id = 0;

    /**
     * @Id @Column(type="integer")
     * @var int
     */
    protected $version = 0;

    /**
     * @Column(type="integer", name="contentobject_id")
     * @var int
     */
    protected $contentId = 0;

    /**
     * @Column(type="string")
     * @var string
     */
    protected $data_text = '';

    /**
     * @Column(type="integer")
     * @var int
     */
    protected $data_int = 0;

    /**
     * @Column(type="float")
     * @var float
     */
    protected $data_float = 0.0;

    /**
     * @Column(length=20, name="language_code")
     * @var string
     */
    protected $languageCode = '';

    /**
     * @Column(length=50, name="data_type_string")
     * @var string
     */
    protected $fieldTypeString = '';

    /**
     * @ManyToOne(targetEntity="Content", inversedBy="fields")
     * @JoinColumn(name="contentobject_id", referencedColumnName="id")
     * @var Content
     */
    protected $content;

    /**
     * Return content object
     *
     * @return Content
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @ManyToOne(targetEntity="ContentTypeField", inversedBy="contentFields", fetch="EAGER")
     * @JoinColumn(name="contentclassattribute_id", referencedColumnName="id")
     * @var ContentType
     */
    protected $contentTypeField;

    /**
     * Return content type object
     *
     * @return ContentType
     */
    public function getContentTypeField()
    {
        return $this->contentTypeField;
    }

    /**
     * Called when subject has been updated
     *
     * @param Interface_Observable $subject
     * @param string|null $event
     * @return Interface_Observer
     */
    public function update( Interface_Observable $subject , $event  = null )
    {
        if ( $subject instanceof Content )
            $this->notify( $event );
        else
            parent::update( $subject, $event );
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return  $this->id . ' ' . $this->version . ' ' . $this->languageCode . ' ' . $this->fieldTypeString;
    }
}
