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
namespace ezx\doctrine\model;
class Location extends Abstract_Model
{
    public function __construct()
    {
        $this->children = new SerializableCollection();
    }

    /**
     * @Id @Column(type="integer", name="node_id") @GeneratedValue
     * @var int
     */
    protected $id;

    /**
     * @Column(type="integer", name="contentobject_id")
     * @var int
     */
    protected $contentId;

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
     * @var SerializableCollection(Location)
     */
    protected $children;

    /**
     * Return collection of children Location objects
     *
     * @return SerializableCollection(Location)
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return '' . $this->id;
    }
}
