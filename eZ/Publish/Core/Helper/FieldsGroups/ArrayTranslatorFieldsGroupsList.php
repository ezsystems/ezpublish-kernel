<?php
/**
 * This file is part of the ezpublish-kernel package.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Helper\FieldsGroups;

use Symfony\Component\Translation\TranslatorInterface;

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

    /** @var \Symfony\Component\Translation\TranslatorInterface */
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
}
