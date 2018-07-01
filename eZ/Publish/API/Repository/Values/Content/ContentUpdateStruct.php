<?php

/**
 * File containing the eZ\Publish\API\Repository\Values\Content\ContentUpdateStruct class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\API\Repository\Values\Content;

/**
 * This class is used for updating the fields of a content object draft.
 *
 * @property \eZ\Publish\API\Repository\Values\Content\Field[] $fields
 */
abstract class ContentUpdateStruct extends ContentStruct
{
    /**
     * The language code of the version. In 4.x this code will be used as the language code of the translation
     * (which is shown in the admin interface).
     * It is also used as default language for added fields.
     *
     * @var string
     */
    public $initialLanguageCode;

    /**
     * Creator user ID.
     *
     * Creator of the version, in the search API this is referred to as the modifier of the published content.
     *
     * WARNING: Standard permission rules applies, only the user set here will be able to change the draft after being
     *          set as modifier, and only if (s)he has access to edit the draft in the first place.
     *
     * @since 5.4
     *
     * @var mixed Optional creator of version, current user will be used if null
     */
    public $creatorId;
}
