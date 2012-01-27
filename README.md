##Public API implementation repo for NetGen and eZ


#How to get started
* Clone this repo
* Clone ezp-next next to it
* clone research next to it
* Symlink ezp-next: ezp/Persistance and ezp/Io into the ezp/ folder of this project
* Symlink reasearch: publicapi/ezp/PublicAPI into ezp/ folder of this project
* Somehow (temporary) symlink or copy the "PublicAPI" interface folder to ezp\ folder	
* Copy config.php-DEVELOPMENT to config.php
* Execute index.php (httpd or cli) verify that it at least manages to get to *echo $serviceContainer->get( 'repository' )->...* line (currently line 46) in index.php


#Directory Layout
ezp\Base (Common code needed by all bellow)

ezp\PublicAPI (Public API Interface)
ezp\Publish\PublicAPI (Public API Implementation)

ezp\Persistence (Persistence Interface)
ezp\Persistence\Storage\Legacy (Legacy Storage Engine)
ezp\Persistence\Storage\InMemory ("In-Memory" Storage Engine(for unit tests))

ezp\Io (Io Interface)
ezp\Io\Storage\Legacy (Legacy Storage Engine)
ezp\Io\Storage\InMemory ("In-Memory" Storage Engine(for unit tests):)


#Things that are temporary
* This repository
* The need for symlinks to ezp-next and research (eventualy they should be merged)
* (Might be that the layout of the files will change later)


#How to add Service unit tests from ezp-next (using both InMemory and Legacy Storage Handlers)
* cp ../ezp-next/ezp/Content/Tests/Service/<serviceTest> ezp/Publish/PublicAPI/Tests/Service/<serviceTest>
* cd ../ezp-next && git checkout legacyServiceTesting
* cp ../ezp-next/ezp/Content/Tests/Service/Legacy/<serviceTest> ezp/Publish/PublicAPI/Tests/Service/Legacy/<serviceTest>
* git checkout master && cd ../publicapi



