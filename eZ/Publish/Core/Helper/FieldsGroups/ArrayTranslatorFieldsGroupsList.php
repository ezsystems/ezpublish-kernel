<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Helper\FieldsGroups;

use eZ\Publish\API\Repository\Values\ContentType\FieldDefinition;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * A fields groups list implementation based on settings (scalar values) injection.
 * Human-readable names are obtained using the translator, in the `ezplatform_fields_groups` domain.
 *
 * @internal meant to be instantiated by the DIC. Do not inherit from it or instantiate it manually.
 */
final class ArrayTranslatorFieldsGroupsList implements FieldsGroupsList
{
    /** @var array */
    private $groups;

    /** @var string */
    private $defaultGroup;

    /** @var \Symfony\Contracts\Translation\TranslatorInterface */
    private $translator;

    public function __construct(TranslatorInterface $translator, $defaultGroup, array $groups)
    {
        $translatedGroups = [];
        foreach ($groups as $groupIdentifier) {
            $translatedGroups[$groupIdentifier] = $translator->trans($groupIdentifier, [], 'ezplatform_fields_groups');
        }
        $this->groups = $translatedGroups;
        $this->defaultGroup = $defaultGroup;
        $this->translator = $translator;
    }

    public function getGroups()
    {
        return $this->groups;
    }

    public function getDefaultGroup()
    {
        return $this->defaultGroup;
    }

    public function getFieldGroup(FieldDefinition $fieldDefinition): string
    {
        if (empty($fieldDefinition->fieldGroup)) {
            return $this->getDefaultGroup();
        }

        return $fieldDefinition->fieldGroup;
    }
}
