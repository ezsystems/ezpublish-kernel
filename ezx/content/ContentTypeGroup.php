<?php
/**
 * Content Type group (content class group) model object
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
class ContentTypeGroup extends Abstract_ContentModel
{
    /**
     * Definition of properties on this class
     *
     * {@inheritdoc}
     *
     * @see \ezx\base\Abstract_Model::$definition
     * @var array
     */
    protected static $definition = array(
        'id' => array(
            'type' => self::TYPE_INT,
            'internal' => true
        ),
        'version' => array(
            'type' => self::TYPE_INT,
            'internal' => true
        ),
        //'identifier' => array( 'type' => self::TYPE_STRING ),
        'contentTypes' => array(
            'type' => self::TYPE_ARRAY,
            'dynamic' => true,
        ),
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
     * @var \Doctrine\Common\Collections\ArrayCollection(ContentType)
     */
    protected $contentTypes;

    /**
     * Return collection of all content objects of this content type
     *
     * @return \Doctrine\Common\Collections\ArrayCollection(ContentType)
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
