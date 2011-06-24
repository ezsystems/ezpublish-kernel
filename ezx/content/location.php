<?php
/**
 * Content Location (Node) domain object
 *
 * @copyright Copyright (c) 2011, eZ Systems AS
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2.0
 * @package ext
 * @subpackage content
 */

/**
 * @Entity @Table(name="ezcontentobject_tree")
 *
 * @property Location $parent
 */
namespace ezx\content;
class Location extends Abstracts\ContentModel implements \ezp\base\ObserverInterface
{
    /**
     * @var array Readable of properties on this object
     */
    protected $readableProperties = array(
        'id' => false,
        'depth' => false,
        'isHidden' => true,
        'isInvisible' => true,
    );

    /**
     * @var array Dynamic properties on this object
     */
    protected $dynamicProperties = array(
        'content' => false,
        'parent' => false,
        'children' => false,
    );

    /**
     * Setups empty children collection and attaches $content
     *
     * @param Content $content
     */
    public function __construct( Content $content )
    {
        $this->content = $content;
        $this->children = new \Doctrine\Common\Collections\ArrayCollection();
        $content->locations[] = $this;
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
    protected function getContent()
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
    protected function getParent()
    {
        if ( $this->parentLocationId <= 1 )
        {
            return;
        }
        else if ( $this->parent instanceof Proxy )
        {
            return $this->parent = $this->parent->load();
        }
        return $this->parent;
    }

    /**
     * Set parent location
     *
     * @param Location $parent
     */
    protected function setParent( Location $parent )
    {
        $this->parent = $parent;
    }

    /**
     * @OneToMany(targetEntity="Location", mappedBy="parent")
     * @var Location[]
     */
    protected $children;

    /**
     * Return collection of children Location objects
     *
     * @return Location[]
     */
    protected function getChildren()
    {
        return $this->children;
    }

    /**
     * Called when subject has been updated
     *
     * @param \ezp\base\ObservableInterface $subject
     * @param string $event
     * @return Location
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
        return '' . $this->id;
    }
}
