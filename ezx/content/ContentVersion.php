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
class ContentVersion extends Abstracts\ContentModel implements \ezx\base\Interfaces\Observer
{
    /**
     * Definition of properties on this class
     *
     * {@inheritdoc}
     *
     * @see \ezx\base\Abstracts\DomainObject::$definition
     * @var array
     */
    protected static $definition = array(
        'id' => array(
            'type' => self::TYPE_INT,
            'internal' => true,
        ),
        'version' => array(
            'type' => self::TYPE_INT,
            'internal' => true,
        ),
        'userId' => array(
            'type' => self::TYPE_INT,
        ),
        'creatorId' => array(
            'type' => self::TYPE_INT,
        ),
        'created' => array(
            'type' => self::TYPE_INT,
        ),
        'modified' => array(
            'type' => self::TYPE_INT,
        ),
        'status' => array(
            'type' => self::TYPE_INT,
        ),
        'initial_language_id' => array(
            'type' => self::TYPE_INT,
        ),
        'language_mask' => array(
            'type' => self::TYPE_INT,
        ),
        'contentobject_id' => array(
            'type' => self::TYPE_INT,
        ),
        'fields' => array(
            'type' => self::TYPE_ARRAY,
            'member' => true,
            'dynamic' => true,
        ),
        'content' => array(
            'type' => self::TYPE_OBJECT,
            'dynamic' => true,
        ),
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
        foreach ( $content->getContentType()->getFields() as $contentTypeField )
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
     * @Column(type="integer")
     * @var int
     */
    protected $initial_language_id = 0;

    /**
     * @Column(type="integer")
     * @var int
     */
    protected $language_mask = 0;

    /**
     * @Column(type="integer")
     * @var int
     */
    protected $contentobject_id = 0;

    /**
     * @OneToMany(targetEntity="ContentField", mappedBy="contentVersion", fetch="EAGER")
     * @var FieldMap(ContentField)
     */
    private $fields;

    /**
     * Return collection of all fields assigned to object (all versions and languages)
     *
     * @return \Doctrine\Common\Collections\ArrayCollection(ContentField)
     */
    public function getFields()
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
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Magic object that steps in when fields are accessed
     */
    protected $fieldMap;

    /**
     * Get value
     *
     * @throws \InvalidArgumentException
     * @param string $name
     * @param string $value
     * @return mixed Return $value
     */
    public function __get( $name )
    {
        if ( $name === 'fields' )
        {
            if ( $this->fieldMap !== null )
                return $this->fieldMap;
            return $this->fieldMap = new FieldMap( $this );
        }
        return parent::__get( $name );
    }

    /**
     * Called when subject has been updated
     *
     * @param \ezx\base\Interfaces\Observable $subject
     * @param string $event
     * @return ContentField
     */
    public function update( \ezx\base\Interfaces\Observable $subject, $event = 'update' )
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
