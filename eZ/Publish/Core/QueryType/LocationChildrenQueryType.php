<?php
/**
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace BD\SandboxBundle\QueryType;

use BD\SandboxBundle\QueryType\Sorter\LocationOptionsSorter;
use eZ\Publish\Core\QueryType\OptionsResolverBasedQueryType;
use eZ\Publish\Core\QueryType\QueryType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;

class LocationChildrenQueryType extends OptionsResolverBasedQueryType implements QueryType
{
    private $languages;

    private $excludedContentTypes;

    /**
     * @var Sorter\LocationOptionsSorter
     */
    private $sorter;

    public function __construct(LocationOptionsSorter $sorter, array $languages, array $excludedContentTypes)
    {
        $this->languages = $languages;
        $this->excludedContentTypes = $excludedContentTypes;
        $this->sorter = $sorter;
    }

    protected function configureOptions(OptionsResolver $optionsResolver)
    {
        $optionsResolver->setRequired('location');
        $optionsResolver->setDefault('types', []);
        $optionsResolver->setAllowedValues('location', function ($value) { return $value instanceof Location; });
        $optionsResolver->setAllowedTypes('types', 'array');
    }

    protected function doGetQuery(array $parameters)
    {
        $query = new \eZ\Publish\API\Repository\Values\Content\LocationQuery();

        $criteria = [
            new Criterion\Visibility(Criterion\Visibility::VISIBLE),
            new Criterion\ParentLocationId($parameters['location']->id),
            new Criterion\LanguageCode($this->languages),
        ];

        if (isset($parameters['types'])) {
            $criteria[] = new Criterion\ContentTypeIdentifier($parameters['types']);
        }

        if (!empty($this->excludedContentTypes)) {
            $criteria[] = new Criterion\LogicalNot(
                new Criterion\ContentTypeIdentifier($this->excludedContentTypes)
            );
        }

        $query->filter = $criteria;

        $this->sorter->sortFromLocation($query, $parameters['location']);

        return $query;
    }

    /**
     * Returns the QueryType name.
     * @return string
     */
    public static function getName()
    {
        return 'LocationChildren';
    }
}
