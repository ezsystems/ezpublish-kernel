Feature: Query controller
    In order to simplify listing items from the repository
    As a developer
    I want to run repository queries from content views

Scenario: A content view can be configured to run and render a query
    Given the following content view configuration block:
    """
        controller: ez_query:locationQueryAction
        params:
            query:
                query_type: 'LocationChildren'
                parameters:
                    parentLocationId: '@=location.id'
                assign_results_to: 'children'
    """
    And the LocationChildren QueryType defined in "src/AppBundle/QueryType/LocationChildrenQueryType.php":
    """
    <?php
    namespace AppBundle\QueryType;

    use eZ\Publish\API\Repository\Values\Content\LocationQuery;
    use eZ\Publish\API\Repository\Values\Content\Query\Criterion\ParentLocationId;
    use eZ\Publish\Core\QueryType\QueryType;

    class LocationChildrenQueryType implements QueryType
    {
        public function getQuery(array $parameters = [])
        {
            return new LocationQuery([
                'filter' => new ParentLocationId($parameters['parentLocationId']),
            ]);
        }

        public function getSupportedParameters()
        {
            return ['parentLocationId'];
        }

        public static function getName()
        {
            return 'LocationChildren';
        }
    """
    When I view a location matched by the view configuration above
    Then the LocationChildren Query is built using the folder's location id as the parentLocationId parameter
     And a Location Query is ran with the LocationChildren query
     And the Query results are assigned to the "children" twig variable
