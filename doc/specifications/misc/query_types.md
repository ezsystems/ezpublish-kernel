## QueryTypes
QueryTypes are named objects that build a Query.

To define a new QueryType, you need to create a class that implements the QueryType
interface, and register it as such in the service container.

### The QueryType interface
```php
interface QueryType
{
    /**
     * Builds and returns the Query object
     *
     * The Query can be either a Content or a Location one.
     *
     * @param array $parameters A hash of parameters that will be used to build the Query
     * @return \eZ\Publish\API\Repository\Values\Content\Query
     */
    public function getQuery(array $parameters = []);

    /**
     * Returns an array listing the parameters supported by the QueryType
     * @return array
     */
    public function getSupportedParameters();

    /**
     * Returns the QueryType name
     * @return string
     */
    public static function getName();
}
```

### Parameters
A QueryType may accept parameters, depending on the implementation. They can be used in any way, such as:
  - customizing an element's value (limit, ContentType identifier, etc)
  - conditionally adding/removing criteria from the query
  - setting the limit/offset

The implementations should use Symfony's `OptionsResolver` for parameters handling and resolution.

### QueryType example: latest content
This QueryType returns a LocationQuery that searches for the 10 last published content, order by reverse
publishing date. It accepts an optional `type` parameter, that can be set to a ContentType identifier:

```php
namespace Acme\AcmeBundle\QueryType;

use eZ\Publish\Core\QueryType\QueryType;
use eZ\Publish\API\Repository\Values\Content\Query;

class LatestContentQueryType implements QueryType
{
    public function getQuery(array $parameters = [])
    {
        $criteria[] = new Query\Criterion\Visibility(Query\Criterion\Visibility::VISIBLE);
        if (isset($parameters['type'])) {
            $criteria[] = new Query\Criterion\ContentTypeIdentifier($parameters['type']);
        }

        return new Query([
            'filter' => new Query\Criterion\LogicalAnd($criteria),
            'sortClauses' => [new Query\SortClause\DatePublished()],
            'limit' => isset($parameters['limit']) ? $parameters['limit'] : 10,
        ]);
    }

    public static function getName()
    {
        return 'AcmeBundle:LatestContent';
    }

    /**
     * Returns an array listing the parameters supported by the QueryType.
     * @return array
     */
    public function getSupportedParameters()
    {
        return ['type'];
    }
}
```

### Naming of QueryTypes
Each QueryType is named after what is returned by `getName()`. Names must be unique. A warning will be thrown during
compilation if there is a conflict, and the resulting behaviour will be unpredictable.

QueryType names should use a unique namespace, in order to avoid conflicts with other bundles. We recommend
that the name is prefixed with the bundle's name: `AcmeBundle:LatestContent`. A vendor/company's name could also
work for QueryTypes that are reusable throughout projects: `Acme:LatestContent`.

### Registering the QueryType into the service container
In addition to creating a class for a `QueryType`, you must also register the QueryType with the Service Container.
This can be done in two ways: by convention, and with a service tag.

#### By convention
Any class named `<Bundle>\Ez\QueryType\*QueryType`, that implements the QueryType interface, will be registered as a
custom QueryType. Example: `AcmeBundle\Ez\QueryType\LatestContentQueryType`.

#### Using a service tag
If the proposed convention doesn't work for you, QueryTypes can be manually tagged in the service declaration:

```yaml
acme.query.latest_content:
    class: AcmeBundle\Query\LatestContent
    tags:
        - {name: ezpublish.query_type}
```

The effect is exactly the same than registering by convention. Defining a QueryType as a service is required
if the class has custom dependencies.

##### QueryType name override

> Added in eZ Platform 1.7

You may specify an 'alias' tag attribute that will be used to register the QueryType. It allows you to use the same
class, with different arguments, as different QueryTypes:

```yaml
acme.query.latest_articles:
    class: AcmeBundle\Query\LatestContent
    arguments: ['article']
    tags:
        - {name: ezpublish.query_type, alias: latest_articles}

acme.query.latest_links:
    class: AcmeBundle\Query\LatestContent
    arguments: ['link']
    tags:
        - {name: ezpublish.query_type, alias: latest_links}
```

### The OptionsResolverBasedQueryType abstract class
An abstract class based on Symfony's `OptionsResolver` eases implementation of QueryTypes with parameters.

It provides final implementations of `getQuery()` and `getDefinedParameters()`.

A `doGetQuery()` method must be implemented instead of `getQuery()`. It is called with the parameters processed by the
OptionsResolver, meaning that the values have been validated, and default values have been set.

In addition, the `configureOptions(OptionsResolver $resolver)` method must configure the OptionsResolver.

The LatestContentQueryType can benefit from the abstract implementation:
- validate that `type` is a string, but make it optional
- validate that `limit` is an int, with a default value of 10

```php
namespace AcmeBundle\Ez\QueryType;

use eZ\Publish\API\Repository\Values\Content\Query;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OptionsBasedLatestContentQueryType extends OptionsResolverBasedQueryType implements QueryType
{
    protected function doGetQuery(array $parameters)
    {
        $criteria[] = new Query\Criterion\Visibility(Query\Criterion\Visibility::VISIBLE);
        if (isset($parameters['type'])) {
            $criteria[] = new Query\Criterion\ContentTypeIdentifier($parameters['type']);
        }

        return new Query( [
            'criterion' => new Query\Criterion\LogicalAnd($criteria),
            'sortClauses' => [new Query\SortClause\DatePublished()],
            'limit' => $parameters,
        ] );
    }

    public static function getName()
    {
        return 'AcmeBundle:LatestContent';
    }

    protected function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setAllowedTypes('type', 'string');
        $resolver->setAllowedValues('limit', 'int');
        $resolver->setDefault('limit', 10);
    }
}
```

## Using QueryTypes from PHP code
All QueryTypes are registered in a registry, the QueryType registry.

It is available from the container as `ezpublish.query_type.registry`:

```php
class MyCommand extends ContainerAwareCommand
{
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $queryType = $this->getContainer()->get('ezpublish.query_type.registry')->getQueryType('AcmeBundle:LatestContent');
        $query = $queryType->getQuery(['type' => 'article']);

        $searchResults = $this->getContainer()->get('ezpublish.api.service.search')->findContent($query);
        foreach ($searchResults->searchHits as $searchHit) {
            $output->writeln($searchHit->valueObject->contentInfo->name);
        }
    }
}
```
