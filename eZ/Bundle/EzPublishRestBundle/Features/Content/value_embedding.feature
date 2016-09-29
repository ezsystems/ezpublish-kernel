Feature: Value objects referenced in responses can be expanded on demand by means of an HTTP header.

    Scenario:
        Given any REST request I have permissions for
          And a reference to a value object 'path.to.value_object' in the request's response
         When I add to the request the 'X-eZ-Embed-Value: path.to.value_object'
         Then the referenced value object is embedded in the response
