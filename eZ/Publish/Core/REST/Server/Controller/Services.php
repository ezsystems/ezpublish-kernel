<?php

/**
 * File containing the Services controller class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Controller;

use eZ\Publish\Core\REST\Server\Controller as RestController;
use eZ\Publish\Core\REST\Server\Values;

/**
 * Services controller.
 */
class Services extends RestController
{
    /** @var array */
    protected $countriesInfo;

    public function __construct(array $countriesInfo)
    {
        $this->countriesInfo = $countriesInfo;
    }

    /**
     * Loads Country List.
     */
    public function loadCountryList()
    {
        return new Values\CountryList($this->countriesInfo);
    }
}
