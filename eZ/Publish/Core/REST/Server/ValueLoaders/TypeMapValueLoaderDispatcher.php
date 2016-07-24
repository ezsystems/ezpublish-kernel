<?php
/**
 * This file is part of the ezplatform package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\ValueLoaders;

class TypeMapValueLoaderDispatcher
{
    /**
     * @var \eZ\Publish\Core\REST\Server\ValueLoaders\ValueLoaderInterface[]
     */
    private $typeLoadersMap;

    public function __construct($typeLoadersMap)
    {
        $this->typeLoadersMap = $typeLoadersMap;
    }

    public function load($type, $loadParameters)
    {
        return $this->typeLoadersMap[$type]->load($loadParameters);
    }
}
