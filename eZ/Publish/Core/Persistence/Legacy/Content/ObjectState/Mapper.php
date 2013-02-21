<?php
/**
 * File containing the ObjectState Mapper class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\Content\ObjectState;

use eZ\Publish\SPI\Persistence\Content\ObjectState;
use eZ\Publish\SPI\Persistence\Content\ObjectState\Group;
use eZ\Publish\SPI\Persistence\Content\ObjectState\InputStruct;
use eZ\Publish\SPI\Persistence\Content\Language\Handler as LanguageHandler;

/**
 * Mapper for ObjectState and object state Group objects
 */
class Mapper
{
    /**
     * Language handler
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\Language\Handler
     */
    protected $languageHandler;

    /**
     * Creates a new mapper.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Language\Handler $languageHandler
     */
    public function __construct( LanguageHandler $languageHandler )
    {
        $this->languageHandler = $languageHandler;
    }

    /**
     * Creates ObjectState object from provided $data
     *
     * @param array $data
     *
     * @return \eZ\Publish\SPI\Persistence\Content\ObjectState
     */
    public function createObjectStateFromData( array $data )
    {
        $objectState = new ObjectState();

        $objectState->id = (int)$data[0]['ezcobj_state_id'];
        $objectState->groupId = (int)$data[0]['ezcobj_state_group_id'];
        $objectState->identifier = $data[0]['ezcobj_state_identifier'];
        $objectState->priority = (int)$data[0]['ezcobj_state_priority'];
        $objectState->defaultLanguage = $this->languageHandler->load(
            $data[0]['ezcobj_state_default_language_id']
        )->languageCode;

        $objectState->languageCodes = array();
        $objectState->name = array();
        $objectState->description = array();

        foreach ( $data as $stateTranslation )
        {
            $languageCode = $this->languageHandler->load(
                $stateTranslation['ezcobj_state_language_language_id'] & ~1
            )->languageCode;

            $objectState->languageCodes[] = $languageCode;
            $objectState->name[$languageCode] = $stateTranslation['ezcobj_state_language_name'];
            $objectState->description[$languageCode] = $stateTranslation['ezcobj_state_language_description'];
        }

        return $objectState;
    }

    /**
     * Creates ObjectState array of objects from provided $data
     *
     * @param array $data
     *
     * @return \eZ\Publish\SPI\Persistence\Content\ObjectState[]
     */
    public function createObjectStateListFromData( array $data )
    {
        $objectStates = array();

        foreach ( $data as $objectStateData )
        {
            $objectStates[] = $this->createObjectStateFromData( $objectStateData );
        }

        return $objectStates;
    }

    /**
     * Creates ObjectStateGroup object from provided $data
     *
     * @param array $data
     *
     * @return \eZ\Publish\SPI\Persistence\Content\ObjectState\Group
     */
    public function createObjectStateGroupFromData( array $data )
    {
        $objectStateGroup = new Group();

        $objectStateGroup->id = (int)$data[0]['ezcobj_state_group_id'];
        $objectStateGroup->identifier = $data[0]['ezcobj_state_group_identifier'];
        $objectStateGroup->defaultLanguage = $this->languageHandler->load(
            $data[0]['ezcobj_state_group_default_language_id']
        )->languageCode;

        $objectStateGroup->languageCodes = array();
        $objectStateGroup->name = array();
        $objectStateGroup->description = array();

        foreach ( $data as $groupTranslation )
        {
            $languageCode = $this->languageHandler->load(
                $groupTranslation['ezcobj_state_group_language_real_language_id']
            )->languageCode;

            $objectStateGroup->languageCodes[] = $languageCode;
            $objectStateGroup->name[$languageCode] = $groupTranslation['ezcobj_state_group_language_name'];
            $objectStateGroup->description[$languageCode] = $groupTranslation['ezcobj_state_group_language_description'];
        }

        return $objectStateGroup;
    }

    /**
     * Creates ObjectStateGroup array of objects from provided $data
     *
     * @param array $data
     *
     * @return \eZ\Publish\SPI\Persistence\Content\ObjectState\Group[]
     */
    public function createObjectStateGroupListFromData( array $data )
    {
        $objectStateGroups = array();

        foreach ( $data as $objectStateGroupData )
        {
            $objectStateGroups[] = $this->createObjectStateGroupFromData( $objectStateGroupData );
        }

        return $objectStateGroups;
    }

    /**
     * Creates an instance of ObjectStateGroup object from provided $input struct
     *
     * @param \eZ\Publish\SPI\Persistence\Content\ObjectState\InputStruct $input
     *
     * @return \eZ\Publish\SPI\Persistence\Content\ObjectState\Group
     */
    public function createObjectStateGroupFromInputStruct( InputStruct $input )
    {
        $objectStateGroup = new Group();

        $objectStateGroup->identifier = $input->identifier;
        $objectStateGroup->defaultLanguage = $input->defaultLanguage;
        $objectStateGroup->name = $input->name;
        $objectStateGroup->description = $input->description;

        $objectStateGroup->languageCodes = array();
        foreach ( $input->name as $languageCode => $name )
        {
            $objectStateGroup->languageCodes[] = $languageCode;
        }

        return $objectStateGroup;
    }

    /**
     * Creates an instance of ObjectState object from provided $input struct
     *
     * @param \eZ\Publish\SPI\Persistence\Content\ObjectState\InputStruct $input
     *
     * @return \eZ\Publish\SPI\Persistence\Content\ObjectState
     */
    public function createObjectStateFromInputStruct( InputStruct $input )
    {
        $objectState = new ObjectState();

        $objectState->identifier = $input->identifier;
        $objectState->defaultLanguage = $input->defaultLanguage;
        $objectState->name = $input->name;
        $objectState->description = $input->description;

        $objectState->languageCodes = array();
        foreach ( $input->name as $languageCode => $name )
        {
            $objectState->languageCodes[] = $languageCode;
        }

        return $objectState;
    }
}
