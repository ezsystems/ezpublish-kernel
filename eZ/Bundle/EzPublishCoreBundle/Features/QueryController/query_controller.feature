Feature: Query controller
    In order to simplify listing items from the repository
    As a developer
    I want to run repository queries from content views

Scenario: A content view can be configured to run and render a query
    Given a content item that matches the view configuration block below
      And the following content view configuration block:
      """
      controller: ez_query:locationQueryAction
      params:
          query:
              query_type: 'LocationChildren'
              parameters:
                  parentLocationId: 2
              assign_results_to: 'children'
      """
      And a LocationChildren QueryType defined in "src/QueryType/LocationChildrenQueryType.php":
      """
      <?php
      namespace App\QueryType;

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
      }
      """
     When I view a content matched by the view configuration above
     Then the viewed content's main location id is mapped to the parentLocationId QueryType parameter
     Then a LocationChildren Query is built from the LocationChildren QueryType
      And a Location Search is executed with the LocationChildren Query
      And the Query results are assigned to the "children" twig variable

Scenario: A content view can be configured to run and render a query and return a PagerFanta Object
    Given a content item that matches the view configuration block below
    And the following content view configuration block with paging action:
      """
      controller: ez_query:pagingQueryAction
      params:
          query:
              query_type: 'LocationChildren'
              parameters:
                  parentLocationId: 2
              assign_results_to: 'children'
      """
    And a LocationChildren QueryType defined in "src/QueryType/LocationChildrenQueryType.php":
      """
      <?php
      namespace App\QueryType;

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
      }
      """
    When I view a content matched by the view configuration above
    Then the Query results assigned to the "children" twig variable is a "Pagerfanta\Pagerfanta" object

Scenario: A content view can be configured to run and render a query return a PagerFanta Object and set limit and page name
    Given a content item that matches the view configuration block below
    And the following template defined in "templates/tests.html.twig":
      """
      <div id='currentPage'>{{ children.currentPage }}</div>
      <div id='maxPerPage'>{{ children.maxPerPage }}</div>
      """
    And the following content view configuration block with paging action and the template set above:
      """
      controller: ez_query:pagingQueryAction
      template: tests.html.twig
      params:
          query:
              query_type: 'LocationChildren'
              parameters:
                  parentLocationId: 2
              limit: 1
              assign_results_to: 'children'
      """
    And a LocationChildren QueryType defined in "src/QueryType/LocationChildrenQueryType.php":
      """
      <?php
      namespace App\QueryType;

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
      }
      """
    When I view a content matched by the view configuration above
    Then the Query results assigned to the twig variable is a Pagerfanta object and has limit "1" and selected page "1"

Scenario: A content view can be configured to run and render a query and set a specific page
    Given a content item that matches the view configuration block below
    And "3" contents are created to test paging
    And the following template defined in "templates/tests.html.twig":
      """
      <div id='currentPage'>{{ children.currentPage }}</div>
      <div id='maxPerPage'>{{ children.maxPerPage }}</div>
      """
    And the following content view configuration block with paging action and the template set above:
      """
      controller: ez_query:pagingQueryAction
      template: tests.html.twig
      params:
          query:
              query_type: 'LocationChildren'
              parameters:
                  parentLocationId: 2
              limit: 1
              page_param: p
              assign_results_to: 'children'
      """
    And a LocationChildren QueryType defined in "src/QueryType/LocationChildrenQueryType.php":
      """
      <?php
      namespace App\QueryType;

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
      }
      """
    When I view a content matched by the view configuration above on page "2" with the "p" parameter
    Then the Query results assigned to the twig variable is a Pagerfanta object and has limit "1" and selected page "2"
