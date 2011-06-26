<?php
/**
 * Content (content object) domain object
 *
 * @copyright Copyright (c) 2011, eZ Systems AS
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2.0
 * @package ext
 * @subpackage content
 */

/**
 * @Entity @Table(name="ezcontentobject_version")
 * @HasLifecycleCallbacks
 *
 * @property-read int $id
 * @property-read int $version
 * @property int $userId
 * @property int $creatorId
 * @property-read ContentField[] $fields An hash structure of fields
 */
namespace ezx\content;
class ContentVersion extends Abstracts\ContentModel implements \ezp\base\ObserverInterface
{
    /**
     * @var array Readable of properties on this object
     */
    protected $readableProperties = array(
        'id' => false,
        'version' => false,
        'userId' => true,
        'creatorId' => true,
        'created' => true,
        'modified' => true,
        'initialLanguageId' => true,
        'languageMask' => true,
        'contentObjectId' => false,
        'fields' => false,
    );

    /**
     * @var array Dynamic properties on this object
     */
    protected $dynamicProperties = array(
        'fieldMap' => true,
        'content' => false,
    );

    /**
     * Create content version based on content and content type fields objects
     *
     * @param Content $content
     */
    public function __construct( Content $content )
    {
        $this->content = $content;
        $this->fields = new \Doctrine\Common\Collections\ArrayCollection();
        foreach ( $content->contentType->fields as $contentTypeField )
        {
            $this->fields[] = new ContentField( $this, $contentTypeField );
        }
        $this->postLoad();
    }

    /**
     * Setup locations and fields as observers of content object
     *
     * @PostLoad
     * @internal Only for use by create function
     * @return Content
     */
    protected function postLoad()
    {
        foreach( $this->getFields() as $field )
        {
            $this->attach( $field, 'store' );
        }
        return $this;
    }

    /**
     * @Id @Column(type="integer") @GeneratedValue
     * @var int
     */
    protected $id = 0;

    /**
     * @Column(type="integer")
     * @var int
     */
    protected $version = 0;

    /**
     * @Column(type="integer", name="user_id")
     * @var int
     */
    protected $userId = 0;

    /**
     * @Column(type="integer", name="creator_id")
     * @var int
     */
    protected $creatorId = 0;

    /**
     * @Column(type="integer")
     * @var int
     */
    protected $created = 0;

    /**
     * @Column(type="integer")
     * @var int
     */
    protected $modified = 0;

    /**
     * @Column(type="integer")
     * @var int
     */
    protected $status = 0;

    /**
     * @Column(type="integer", name="initial_language_id")
     * @var int
     */
    protected $initialLanguageId = 0;

    /**
     * @Column(type="integer", name="language_mask")
     * @var int
     */
    protected $languageMask = 0;

    /**
     * @Column(type="integer", name="contentobject_id")
     * @var int
     */
    protected $contentObjectId = 0;

    /**
     * @OneToMany(targetEntity="ContentField", mappedBy="contentVersion", fetch="EAGER")
     * @var ContentField[]
     */
    protected $fields;

    /**
     * Return collection of all fields assigned to object (all versions and languages)
     *
     * @return ContentField[]
     */
    protected function getFields()
    {
        return $this->fields;
    }

    /**
     * @ManyToOne(targetEntity="Content", inversedBy="contentVersions")
     * @JoinColumn(name="contentobject_id", referencedColumnName="id")
     * @var Content
     */
    protected $content;

    /**
     * Return content object
     *
     * @return Content
     */
    protected function getContent()
    {
        return $this->content;
    }

    /**
     * @param ContentField[]
     */
    protected $fieldMap;

    /**
     * Return collection of all fields assigned to object (all versions and languages)
     *
     * @return ContentField[]
     */
    protected function getFieldMap()
    {
        if ( $this->fieldMap !== null )
            return $this->fieldMap;
        return $this->fieldMap = new FieldMap( $this );
    }

    /**
     * Called when subject has been updated
     *
     * @param \ezp\base\ObservableInterface $subject
     * @param string $event
     * @return ContentVersion
     */
    public function update( \ezp\base\ObservableInterface $subject, $event = 'update' )
    {
        if ( $subject instanceof Content )
        {
            return $this->notify( $event );
        }
        return $this;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->id . ' ('  . $this->version . ')';
    }
}
