Feature: Preview of content drafts

    As a content editor
    While I'm editing content
    I need to preview the result before publishing

    Scenario: Previewing the first version of a content works
        Given I have "administrator" permissions
          And I create an article draft
         When I preview this draft
         Then the output is valid

    @broken
    Scenario: Previewing the first version of a content with a custom location controller works
        Given I have "administrator" permissions
          And I create a draft for a content type that uses a custom location controller
         When I preview this draft
         Then the output is valid
