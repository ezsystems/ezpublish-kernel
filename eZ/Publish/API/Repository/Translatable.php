<?php

/**
 * File containing the eZ\Publish\API\Repository\ContentService class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\API\Repository;

/**
 * Interface implemented by everything which should be translatable. This
 * should for example be implemented by any exception, which might bubble up to
 * a user, or validation errors.
 */
interface Translatable
{
    /**
     * Returns a translatable Message.
     *
     * @return \eZ\Publish\API\Repository\Values\Translation
     */
    public function getTranslatableMessage();
}
