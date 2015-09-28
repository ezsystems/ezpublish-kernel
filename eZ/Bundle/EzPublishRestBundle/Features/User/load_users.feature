@user
Feature: List users from the repository
    As a REST API consumer
    I need to list users from the Repository

    Scenario: The list of users can be filtered by login using a "login" query parameter
       Given I have "administrator" permissions
         And there is a user with the login "admin"
         And there isn't a user with the login "foo"
        When I create a "GET" request to "/user/users?login=admin"
         And I set header "Accept" with "UserList" object
         And I send the request
        Then response status code is 200
         And response contains only the user with the login "admin"
        When I create a "GET" request to "/user/users?login=foo"
         And I send the request
        Then response status code is 404

    Scenario: The list of users can be filtered by email using an "email" query parameter
       Given I have "administrator" permissions
         And there is a user with the email "nospam@ez.no"
         And there isn't a user with the email "foo@bar.com"
        When I create a "GET" request to "/user/users?email=nospam@ez.no"
         And I set header "Accept" with "UserList" object
         And I send the request
        Then response status code is 200
         And response contains only the user with the email "nospam@ez.no"
        When I create a "GET" request to "/user/users?email=foo@bar.com"
         And I send the request
        Then response status code is 404

    Scenario: Check if an email address is used by an existing user
       Given I have "administrator" permissions
         And there is a user with the email "nospam@ez.no"
         And there isn't a user with the email "foo@bar.com"
        When I create a "HEAD" request to "/user/users?email=nospam@ez.no"
         And I send the request
        Then response status code is 200
        When I create a "HEAD" request to "/user/users?email=foo@bar.com"
         And I send the request
        Then response status code is 404

    Scenario: Check if a login is used by an existing user
       Given I have "administrator" permissions
         And there is a user with the login "admin"
         And there isn't a user with the login "foo"
        When I create a "HEAD" request to "/user/users?login=admin"
         And I send the request
        Then response status code is 200
        When I create a "HEAD" request to "/user/users?login=foo"
         And I send the request
        Then response status code is 404

    Scenario: All users can't verify email or login usage
       Given that I can't view users
         And there is a user with the login "admin"
         And there is a user with the email "nospam@ez.no"
        When I create a "HEAD" request to "/user/users?login=admin"
         And I send the request
        Then response status code is 404
        When I create a "HEAD" request to "/user/users?email=nospam@ez.no"
         And I send the request
        Then response status code is 404
