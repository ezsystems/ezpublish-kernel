# Listing Content and Locations

Starting from Platform 2015.07, listing content and locations in a project is
much easier, using a couple standardized mechanisms.

Predefined, named queries can be defined in any bundle. They can then be used from twig, using a
[custom controller](#query_controller), or from PHP code using the [QueryType Registry](#fixme). The controller supports
[template overriding](#template_override), making it easy to use different templates for different calls.

## <a name="query_controller"></a> The query controller
A new controller is added : `ez_query`. It has actions for each type of Search operation:
- `ez_query:contentInfo` will run a Content Query and return ContentInfo items.
  Unless you explicitly need data from the Content (Fields, Versions), **always prefer ContentInfo**, as it performs much better.
  Most query templates should anyway iterate over the items, and render them using the viewController.
- `ez_query:content` will run a Content Query and return Content items
- `ez_query:locations` will run a Location Query and return Location items

They requires two arguments:
- `queryTypeName`, the name of the QueryType to run. Example: `AcmeBundle:LatestArticles'
- `viewType`, similar to what `viewContent` and `viewLocation` expect. Examples: `list`, `tree`, `full_list`...

They also support a `parameters` hash. It will be passed on to the QueryType when building the Query, and made available
from the query template.

```jinja
{{ render(controller(
    'ez_query:contentQuery',
    {
        'queryTypeName': 'AcmeBundle:LatestContent',
        'viewType': 'list',
        'parameters': {'type': 'article'}
    }
)) }}
```

### <a name="template_override"></a>Rendering of queries
The `ez_query` actions support template override. They support several matchers.
- `QueryType\Name`: matches the QueryType's name
- `QueryType\Parameter`: matches the value of one or several of the parameters
- `QueryType\Expression`: matches the parameters with an [Expression](http://symfony.com/en/doc/current/components/expression_language/index.html).

Assuming we want to render the `AcmeBundle:LatestContent` query using
`AcmeBundle:query/list/latest_articles.html.twig` when the `ContentType` parameter has the value 'article':

```yaml
system:
    default:
        # Or content_query to create a template override for a content query
        location_query:
            # Matches the ViewType passed to the controller
            list:
                latest_articles:
                    template: "AcmeBundle:query/list/latest_articles.html.twig"
                    match:
                        QueryType\Name: 'AcmeBundle:LatestContent'
                        QueryType\Parameters: {ContentType: "article"}
```

#### Template matchers

##### `QueryType\Name`
Matches the QueryType's name (exact match).

##### `QueryType\Parameters`
Matches the parameters against a given hash. The hash must contain the parameter's name as the key,
and the match value as the value:

```yaml
match:
    QueryType\Parameters:
        type: "article",
        category: "development"
```

Each parameter will be matched exactly. The matcher will match if ALL of the provided parameters match what is contained
in the hash. More complex cases must be covered with `QueryType\Expression`.

##### `QueryType\Expression`
Uses Symfony's [Expression Language](http://symfony.com/en/doc/current/components/expression_language/index.html)
for advanced matching.
This matcher expects a valid expression language string as the input. The expression must be
evaluated to a boolean. The parameters hash is available as `parameters`

In the example below, 'type' must be either 'article' or 'blog_post'.

```yaml
match:
    QueryType\Expression: "parameters['type'] in ['article', 'blog_post']"
```

### Query controller templates
The templates used by this controller provide you with the results from the query. The results can be iterated over, and
displayed using the object's properties or the `ez_content` controller actions.


#### Available variables

`location_list`                | array | Array of resulting Location. *Only set by the contentInfo action*
`content_list`                 | array | Array of resulting Content. *Only set by the contentInfo action*
`content_info_list`            | array | Array of resulting ContentInfo. *Only set by the contentInfo action*
`list_count`                   | int   | Number of items in the resultset, within the limit if any
`total_count`                  | int   | Total number of items in the search result
`parameters`                   | array | The `parameters` hash that was passed to the Query

## QueryType objects
To make a new QueryType available to the Query Controller, you need to create a class that implements the QueryType
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

The effect is exactly the same than registering by convention.

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
