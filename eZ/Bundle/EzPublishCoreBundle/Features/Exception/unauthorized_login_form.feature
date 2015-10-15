Feature: Handling of Unauthorized repository exceptions
    Scenario: When a Repository UnauthorizedException is throw, anonymous users are shown the login screen
        Given that I am not logged in
         When a repository UnauthorizedException is thrown during an HTTP request
         Then the login form is shown

    Scenario: When a Repository UnauthorizedException is throw, authenticated users are shown the exception
        Given that I am logged in
         When a repository UnauthorizedException is thrown during an HTTP request
         Then an eZ\Publish\Core\Base\Exceptions\UnauthorizedException is displayed
          And an Symfony\Component\Security\Core\Exception\AccessDeniedException is displayed
