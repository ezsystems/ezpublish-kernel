<?php
/**
 * Content Location (Node) model object
 *
 * @copyright Copyright (c) 2011, eZ Systems AS
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2.0
 * @package ext
 * @subpackage doctrine
 */

/**
 * @Entity @Table(name="ezcontentobject_tree")
 */
namespace ezx\doctrine\content;
class Location extends Abstract_ContentModel implements \ezx\doctrine\Interface_Observer
{
    protected static $definition = array(
        'id' => array( 'type' => self::TYPE_INT, 'readonly' => true, 'internal' => true ),
        'depth' => array( 'type' => self::TYPE_INT, 'readonly' => true, 'internal' => true ),
        'isHidden' => array( 'type' => self::TYPE_INT ),
        'isInvisible' => array( 'type' => self::TYPE_INT ),
        'content' => array( 'type' => self::TYPE_OBJECT, 'internal' => true ),
        'parent' => array( 'type' => self::TYPE_OBJECT, 'internal' => true ),
        'children' => array( 'type' => self::TYPE_ARRAY, 'internal' => true ),
    );

    public function __construct()
    {
        $this->children = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * @Id @Column(type="integer", name="node_id") @GeneratedValue
     * @var int
     */
    protected $id;

    /**
     * @Column(type="integer")
     * @var int
     */
    protected $depth;

    /**
     * @Column(type="integer", name="is_hidden")
     * @var int
     */
    protected $isHidden;

    /**
     * @Column(type="integer", name="is_invisible")
     * @var int
     */
    protected $isInvisible;

    /**
     * @Column(type="integer", name="main_node_id")
     * @var int
     */
    protected $mainLocationId;

    /**
     * @Column(type="integer", name="parent_node_id")
     * @var int
     */
    protected $parentLocationId;

    /**
     * @ManyToOne(targetEntity="Content", inversedBy="locations", fetch="EAGER")
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
     * @ManyToOne(targetEntity="Location", inversedBy="children")
     * @JoinColumn(name="parent_node_id", referencedColumnName="node_id")
     * @var Location
     */
    protected $parent;

    /**
     * Return parent Location object
     *
     * @return Location
     */
    public function getParent()
    {
        if ( $this->parent_location_id > 1 )
        {
            return $this->parent;
        }
    }

    /**
     * @OneToMany(targetEntity="Location", mappedBy="parent")
     * @var \Doctrine\Common\Collections\ArrayCollection(Location)
     */
    protected $children;

    /**
     * Return collection of children Location objects
     *
     * @return \Doctrine\Common\Collections\ArrayCollection(Location)
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * Called when subject has been updated
     *
     * @param \ezx\doctrine\Interface_Observable $subject
     * @param string|null $event
     * @return Location
     */
    public function update( \ezx\doctrine\Interface_Observable $subject , $event  = null )
    {
        if ( $subject instanceof Content )
        {
            $this->notify( $event );
            return $this;
        }
        return parent::update( $subject, $event );
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return '' . $this->id;
    }
}
