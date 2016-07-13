Feature: Preview of content drafts

    As a content editor
    While I'm editing content
    I need to preview the result before publishing

    Scenario: Previewing the first version of a content item works
        Given I have "administrator" permissions
          And I create an folder draft
         When I preview this draft
         Then the output is valid

    @broken
    Scenario: Previewing the first version of a content item with a custom location controller works
        Given I have "administrator" permissions
          And I create a draft for a content type that uses a custom location controller
         When I preview this draft
         Then the output is valid

    Scenario: Previewing a draft of a content item with published version(s) works
        Given I have "administrator" permissions
          And I create a draft of an existing content item
          And I modify a field from the draft
         When I preview this draft
         Then the output is valid
          And I see a preview of this draft
