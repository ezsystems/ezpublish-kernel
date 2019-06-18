<?php

/**
 * File containing the LocationMatcherFactoryTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\Matcher\Tests;

class LocationMatcherFactoryTest extends ContentBasedMatcherFactoryTest
{
    protected $matcherFactoryClass = 'eZ\\Publish\\Core\\MVC\\Symfony\\Matcher\\LocationMatcherFactory';

    /**
     * Returns a valid ValueObject (supported by current MatcherFactory), that will match the test rules.
     * i.e. Should return eZ\Publish\API\Repository\Values\Content\Location for LocationMatcherFactory.
     *
     * @return \eZ\Publish\API\Repository\Values\ValueObject
     */
    protected function getMatchableValueObject()
    {
        return $this->getContentView(['id' => 789], ['id' => 456]);
    }

    /**
     * Returns a valid ValueObject (supported by current MatcherFactory), that won't match the test rules.
     * i.e. Should return eZ\Publish\API\Repository\Values\Content\Location for LocationMatcherFactory.
     *
     * @return \eZ\Publish\API\Repository\Values\ValueObject
     */
    protected function getNonMatchableValueObject()
    {
        return $this->getContentView(['id' => 123456789], ['id' => 987654321]);
    }

    protected function getMatcherClass()
    {
        return 'Id\\Location';
    }
}
