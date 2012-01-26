##Public API implementation repo for NetGen and eZ


#How to get started
* Clone this repo
* Clone ezp-next next to it
* Symlink ezp/Persistance and ezp/Io into the ezp/ folder of this project (and remember to not add these folders using git to this repo.. tip: always use "git add -u" if you need to add to staging index)
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
* The need for symlinks to ezp-next (eventualy they should be merged)
* (Might be that the layout of the files will change later)

