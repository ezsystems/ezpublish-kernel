<?php
/**
 * File containing the ezp\Content\Section\Concrete class.
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\Section;
use ezp\Base\Model,
    ezp\Base\Repository,
    ezp\Base\Exception\Logic,
    ezp\Content,
    ezp\Content\Section,
    ezp\Persistence\Content\Section as SectionValue;

/**
 * This class represents a Concrete Section object
 *
 * @property-read integer $id
 *                The ID, automatically assigned by the persistence layer
 * @property string $identifier
 *                Unique identifier for the section.
 * @property string $name
 *                Human readable name of the section (preferably short for gui's)
 */
class Concrete extends Model implements Section
{
    /**
     * @inherit-doc
     * @var array
     */
    protected $readWriteProperties = array(
        'id' => false,
        'identifier' => true,
        'name' => true,
    );

    /**
     * Constructor, setups all internal objects.
     */
    public function __construct()
    {
        $this->properties = new SectionValue();
    }

    /**
     * Returns definition of the section object, atm: permissions
     *
     * @access private
     * @return array
     */
    public static function definition()
    {
        return array(
            'module' => 'section',
            'functions' => array(
                'assign' => array(
                    'Class' => array(
                        'compare' => function( Section $newSection, array $limitationsValues, Repository $repository, Content $content )
                        {
                            return in_array( $content->typeId, $limitationsValues );
                        },
                    ),
                    'Section' => array(
                        'compare' => function( Section $newSection, array $limitationsValues, Repository $repository, Content $content )
                        {
                            return in_array( $content->sectionId, $limitationsValues );
                        },
                    ),
                    'Owner' => array(
                        'options' => function( Repository $repository )
                        {
                            return array( '1' => 'Self' );
                        },
                        'compare' => function( Section $newSection, array $limitationsValues, Repository $repository, Content $content )
                        {
                            if ( $limitationsValues !== array( '1' ) )
                                throw new Logic(
                                    'Policy module:section function:assign limitation:Owner',
                                    'expected value: array( 0 => \'1\' ), got : ' . var_export( $limitationsValues, true )
                                );
                            return $content->ownerId === $repository->getUser()->id;
                        },
                    ),
                    'NewSection' => array(
                        'compare' => function( Section $newSection, array $limitationsValues )
                        {
                            return in_array( $newSection->id, $limitationsValues );
                        },
                    ),
                ),
                'edit' => array(),
                'view' => array(),
            ),
        );
    }
}
