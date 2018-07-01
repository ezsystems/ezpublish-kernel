Feature: users can be manipulated using the Content API

    Background:
        Given I have "administrator" permissions

    @broken
    Scenario: Creating and publishing a user with the Content API works
         When I create a "POST" request to "/content/objects"
          And I set header "content-type" with "ContentCreate" object
          And I set the Content to a User RestContentCreateStruct
          And I send the request
         Then response status code is "201"
          And it contains a Content of ContentType "user"
          And the Content has the "published" status
          And a User with the same id exists
         When I send a publish request for this content
         Then response status code is "204"

    Scenario: Removing a User Content with the Content API works
        Given there is a User Content
         When I create a delete request for this content
          And I send the request
         Then response status code is "204"
          And the User this Content referred to is deleted

    @broken
    Scenario: Editing a User Content with the Content API works
        Given there is a User Content
         When I create a draft of this content
          And I create an edit request for this draft
          And I set the email field to a new value
          And I send the request
         Then response status code is "200"
          And it contains a Version of ContentType "user"
          And the Content has the "published" status
          And the User's email was updated to the new value
