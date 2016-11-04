<?php

/**
 * File containing the RestEvents class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishRestBundle;

final class RestEvents
{
    /**
     * The REST_CSRF_TOKEN_VALIDATED event occurs after CSRF token has been validated as correct.
     */
    const REST_CSRF_TOKEN_VALIDATED = 'ezpublish.rest.csrf_token_validated';
}
