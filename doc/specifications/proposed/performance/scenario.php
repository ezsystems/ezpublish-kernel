<?php

namespace eZ\Publish\Profiler;

use eZ\Publish\API\Repository\Values\Content\Query;

$defaultLanguage = 'eng-GB';

$folderType = new ContentType(
    'folder',
    array(
        'title' => new Field\TextLine(),
    ),
    array($defaultLanguage)
);

$articleType = new ContentType(
    'article',
    array(
        'title' => new Field\TextLine(),
        'body' => new Field\RichText( new DataProvider\RichText() ),
        'author' => new Field\Author( new DataProvider\User( 'editor' ) ),
        // …
    ),
    array($defaultLanguage, 'ger-DE', 'fra-FR'), // Languages of content
    8 // Average number of versions
);

$commentType = new ContentType(
    'comment',
    array(
        'text' => new Field\TextBlock(),
        'author' => new Field\Author( new DataProvider\Aggregate( array(
            new DataProvider\AnonymousUser(),
            new DataProvider\User( 'user' )
        ) ) ),
        // …
    ),
    array($defaultLanguage),
    2 // Average number of versions
);

$createTask = new Task(
    new Actor\Create(
        1, $folderType,
        new Actor\Create(
            12, $folderType,
            new Actor\Create(
                12, $folderType,
                new Actor\Create(
                    12, $folderType,
                    new Actor\Create(
                        12, $folderType,
                        new Actor\Create(
                            50, $articleType,
                            new Actor\Create(
                                5, $commentType
                            ),
                            $articles = new Storage\LimitedRandomized()
                        )
                    )
                )
            ),
            $folders = new Storage\LimitedRandomized()
        )
    )
);

$viewTask = new Task(
    new Actor\SubtreeView(
        $articles
    )
);

$simpleSearchTask = new Task(
    new Actor\Search(
        "Field Search",
        new Query( array(
            'query' => new Query\Criterion\Field(
                'title',
                Query\Criterion\Operator::EQ,
                'test'
            ),
        ) )
    )
);

$sortedSearchTask = new Task(
    new Actor\Search(
        "Field Sort Search",
        new Query( array(
            'sortClauses' => array( new Query\SortClause\Field(
                'profiler-article',
                'title',
                Query::SORT_ASC,
                $defaultLanguage
            ) ),
        ) )
    )
);

$removeTask = new Task(
    new Actor\SubtreeRemove(
        $folders
    )
);

// Current executor – provided by the caller
//*
$executor->run(
    array(
        new Constraint\Ratio( $createTask, 1 ),
    ),
    new Aborter\Count(100)
); // */

/*
$executor->run(
    array(
        new Constraint\Ratio( $createTask, 1/10 ),
        new Constraint\Ratio( $viewTask, 1 ),
        new Constraint\Ratio( $simpleSearchTask, 1/3 ),
        new Constraint\Ratio( $sortedSearchTask, 1/5 ),
    ),
    new Aborter\Count(500)
); // */

//*
$executor->run(
    array(
        new Constraint\Ratio( $removeTask, 1 ),
    ),
    new Aborter\Count(50)
); // */
