##Public API implementation repo for NetGen and eZ

#Dependencies
* pear: PHPUnit & eZ Components
* PHP 5 Module: php5_sqlit
* Database: sqlite3 if not installed by above stage


#How to get started
* Clone this repo
* Clone ezp-next next to it
* clone research next to it
* Symlink ezp-next: ezp/Persistance and ezp/Io into the ezp/ folder of this project
* Symlink reasearch: publicapi/ezp/PublicAPI into ezp/ folder of this project
* Copy config.php-DEVELOPMENT to config.php
* Execute "$ php index.php" (cli) to verify that it manages to output some text
* Execute "$ phpunit" to see current status of missing tests / implementations


#Directory Layout
ezp\Base (Common code needed by all bellow)

ezp\PublicAPI (Public API Interface)
ezp\Publish\PublicAPI (Public API Implementation)

ezp\Persistence (Persistence Interface)
ezp\Persistence\Storage\Legacy (Legacy Storage Engine)
ezp\Persistence\Storage\InMemory ("In-Memory" Storage Engine(for unit tests))

ezp\Io (Io Interface)
ezp\Io\Storage\Legacy (Legacy Storage Engine)
ezp\Io\Storage\InMemory ("In-Memory" Storage Engine(for unit tests))


#Things that are temporary
* This repository
* The need for symlinks to ezp-next and research (eventualy they should be merged)
* (Might be that the layout of the files will change later)


#How to add Service unit tests from ezp-next (using both InMemory and Legacy Storage Handlers)
* cp ../ezp-next/ezp/Content/Tests/Service/<serviceTest> ezp/Publish/PublicAPI/Tests/Service/<Service>Base.php
* Make it abstract and addopt to inherit from ezp/Publish/PublicAPI/Tests/Service/Base.php
* Copy existing SectionTest.php in both InMemory/ and Legacy/ folder to <Service>Test.php and addapt it to extend class from above. 
* Addapt tests to new Interface

#Things to remember when writing unit tests
* Check return class name type
* Add tests where properties are not set, wrong type or invalid values
* Always have test to trigger all exception types
* If some of the cases above is undefined (not documented), report it.

#Things to remember when implementing Public API
* You CAN skip duplicating validation already done by Persistence API (like NotFoundException)
  if exception is exactly the same.
  But There still need to be full set of unit tests against Public API which will trigger failures
  if Persistence API changes
* Validate as much input as possible, Public API is the layer where all input validation should be done
  Persistence API usually only deals with storage specific validations.

#How to repport issues
* Login to jira.ez.no
* Select "eZ Publish Next" aka "EZPNEXT" Project
* Check if issue already exist, using JQL in advance search interface you can write something like
  "component = Persistence" or one of these components to drill down the search: LegacyStorageEngine or PublicAPI
  But, also do a search w/o component as it might be w/o or with wrong component.
* Create issue (usually Bug, but if you see room for improvement, use Improvement)
* Mark it with on of the Components mentioned above.
* Notify André so he can make sure it goes into InputQ

#How to work on issues in JIRA
In periods where NetGen is working 100% on Public API project, it will be beneficial to
include active devs in the daily eZ Engineering Status meetings and also promote them to
devs in JIRA so they can be assigned to taks (to be able to se status on the Kanban board)
If so:
* Kanban board is available under "Agile" -> "RapidBoard"
  (you might have to select among existing RapidBoards the first time)
* Pick either a Story (a feature), Improvement or Bug issue type and drag it to development column
* When done set it to review state and add comment with link to commit / pull request for review
* (if reviewer finds issue, it will be re assigned to you or a separate bug will be created for it)

