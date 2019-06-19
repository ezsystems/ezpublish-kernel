<?php

/**
 * File containing the ObjectState Mapper class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Legacy\Content\ObjectState;

use eZ\Publish\SPI\Persistence\Content\ObjectState;
use eZ\Publish\SPI\Persistence\Content\ObjectState\Group;
use eZ\Publish\SPI\Persistence\Content\ObjectState\InputStruct;
use eZ\Publish\SPI\Persistence\Content\Language\Handler as LanguageHandler;

/**
 * Mapper for ObjectState and object state Group objects.
 */
class Mapper
{
    /**
     * Language handler.
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\Language\Handler
     */
    protected $languageHandler;

    /**
     * Creates a new mapper.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Language\Handler $languageHandler
     */
    public function __construct(LanguageHandler $languageHandler)
    {
        $this->languageHandler = $languageHandler;
    }

    /**
     * Creates ObjectState object from provided $data.
     *
     * @param array $data
     *
     * @return \eZ\Publish\SPI\Persistence\Content\ObjectState
     */
    public function createObjectStateFromData(array $data)
    {
        $objectState = new ObjectState();

        $languageIds = [(int)$data[0]['ezcobj_state_default_language_id']];
        foreach ($data as $stateTranslation) {
            $languageIds[] = (int)$stateTranslation['ezcobj_state_language_language_id'] & ~1;
        }
        $languages = $this->languageHandler->loadList($languageIds);

        $objectState->id = (int)$data[0]['ezcobj_state_id'];
        $objectState->groupId = (int)$data[0]['ezcobj_state_group_id'];
        $objectState->identifier = $data[0]['ezcobj_state_identifier'];
        $objectState->priority = (int)$data[0]['ezcobj_state_priority'];
        $objectState->defaultLanguage = $languages[(int)$data[0]['ezcobj_state_default_language_id']]->languageCode;

        $objectState->languageCodes = [];
        $objectState->name = [];
        $objectState->description = [];

        foreach ($data as $stateTranslation) {
            $languageCode = $languages[$stateTranslation['ezcobj_state_language_language_id'] & ~1]->languageCode;
            $objectState->languageCodes[] = $languageCode;
            $objectState->name[$languageCode] = $stateTranslation['ezcobj_state_language_name'];
            $objectState->description[$languageCode] = $stateTranslation['ezcobj_state_language_description'];
        }

        return $objectState;
    }

    /**
     * Creates ObjectState array of objects from provided $data.
     *
     * @param array $data
     *
     * @return \eZ\Publish\SPI\Persistence\Content\ObjectState[]
     */
    public function createObjectStateListFromData(array $data)
    {
        $objectStates = [];

        foreach ($data as $objectStateData) {
            $objectStates[] = $this->createObjectStateFromData($objectStateData);
        }

        return $objectStates;
    }

    /**
     * Creates ObjectStateGroup object from provided $data.
     *
     * @param array $data
     *
     * @return \eZ\Publish\SPI\Persistence\Content\ObjectState\Group
     */
    public function createObjectStateGroupFromData(array $data)
    {
        $objectStateGroup = new Group();

        $languageIds = [(int)$data[0]['ezcobj_state_group_default_language_id']];
        foreach ($data as $groupTranslation) {
            $languageIds[] = (int)$groupTranslation['ezcobj_state_group_language_real_language_id'];
        }
        $languages = $this->languageHandler->loadList($languageIds);

        $objectStateGroup->id = (int)$data[0]['ezcobj_state_group_id'];
        $objectStateGroup->identifier = $data[0]['ezcobj_state_group_identifier'];
        $objectStateGroup->defaultLanguage = $languages[
            (int)$data[0]['ezcobj_state_group_default_language_id']
        ]->languageCode;

        $objectStateGroup->languageCodes = [];
        $objectStateGroup->name = [];
        $objectStateGroup->description = [];

        foreach ($data as $groupTranslation) {
            $languageCode = $languages[(int)$groupTranslation['ezcobj_state_group_language_real_language_id']]->languageCode;
            $objectStateGroup->languageCodes[] = $languageCode;
            $objectStateGroup->name[$languageCode] = $groupTranslation['ezcobj_state_group_language_name'];
            $objectStateGroup->description[$languageCode] = $groupTranslation['ezcobj_state_group_language_description'];
        }

        return $objectStateGroup;
    }

    /**
     * Creates ObjectStateGroup array of objects from provided $data.
     *
     * @param array $data
     *
     * @return \eZ\Publish\SPI\Persistence\Content\ObjectState\Group[]
     */
    public function createObjectStateGroupListFromData(array $data)
    {
        $objectStateGroups = [];

        foreach ($data as $objectStateGroupData) {
            $objectStateGroups[] = $this->createObjectStateGroupFromData($objectStateGroupData);
        }

        return $objectStateGroups;
    }

    /**
     * Creates an instance of ObjectStateGroup object from provided $input struct.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\ObjectState\InputStruct $input
     *
     * @return \eZ\Publish\SPI\Persistence\Content\ObjectState\Group
     */
    public function createObjectStateGroupFromInputStruct(InputStruct $input)
    {
        $objectStateGroup = new Group();

        $objectStateGroup->identifier = $input->identifier;
        $objectStateGroup->defaultLanguage = $input->defaultLanguage;
        $objectStateGroup->name = $input->name;
        $objectStateGroup->description = $input->description;

        $objectStateGroup->languageCodes = [];
        foreach ($input->name as $languageCode => $name) {
            $objectStateGroup->languageCodes[] = $languageCode;
        }

        return $objectStateGroup;
    }

    /**
     * Creates an instance of ObjectState object from provided $input struct.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\ObjectState\InputStruct $input
     *
     * @return \eZ\Publish\SPI\Persistence\Content\ObjectState
     */
    public function createObjectStateFromInputStruct(InputStruct $input)
    {
        $objectState = new ObjectState();

        $objectState->identifier = $input->identifier;
        $objectState->defaultLanguage = $input->defaultLanguage;
        $objectState->name = $input->name;
        $objectState->description = $input->description;

        $objectState->languageCodes = [];
        foreach ($input->name as $languageCode => $name) {
            $objectState->languageCodes[] = $languageCode;
        }

        return $objectState;
    }
}
