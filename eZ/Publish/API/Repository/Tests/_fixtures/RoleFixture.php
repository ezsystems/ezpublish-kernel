<?php
return array(
    array(
        1 => new \eZ\Publish\API\Repository\Tests\Stubs\Values\User\RoleStub(
            array(
                "id" => 1,
                "identifier" => "Anonymous",
            )
        ),
        2 => new \eZ\Publish\API\Repository\Tests\Stubs\Values\User\RoleStub(
            array(
                "id" => 2,
                "identifier" => "Administrator",
            )
        ),
        3 => new \eZ\Publish\API\Repository\Tests\Stubs\Values\User\RoleStub(
            array(
                "id" => 3,
                "identifier" => "Editor",
            )
        ),
        4 => new \eZ\Publish\API\Repository\Tests\Stubs\Values\User\RoleStub(
            array(
                "id" => 4,
                "identifier" => "Partner",
            )
        ),
        5 => new \eZ\Publish\API\Repository\Tests\Stubs\Values\User\RoleStub(
            array(
                "id" => 5,
                "identifier" => "Member",
            )
        ),
    ),
    array(
        "Anonymous" => 1,
        "Administrator" => 2,
        "Editor" => 3,
        "Partner" => 4,
        "Member" => 5,
    ),
    5,
    array(
        "12" => array(
                    25 => 2,
                ),
        "11" => array(
                    28 => 1,
                    34 => 5,
                ),
        "42" => array(
                    31 => 1,
                ),
        "13" => array(
                    32 => 3,
                    33 => 3,
                    38 => 5,
                ),
        "59" => array(
                    35 => 4,
                    36 => 5,
                    37 => 1,
                ),
    ),
        array(
        308 => new \eZ\Publish\API\Repository\Tests\Stubs\Values\User\PolicyStub(
            array(
                "id" => 308,
                "roleId" => 2,
                "module" => "*",
                "function" => "*",
                "limitations" => array(
                ),
            )
        ),
        319 => new \eZ\Publish\API\Repository\Tests\Stubs\Values\User\PolicyStub(
            array(
                "id" => 319,
                "roleId" => 3,
                "module" => "user",
                "function" => "login",
                "limitations" => array(
                ),
            )
        ),
        328 => new \eZ\Publish\API\Repository\Tests\Stubs\Values\User\PolicyStub(
            array(
                "id" => 328,
                "roleId" => 1,
                "module" => "content",
                "function" => "read",
                "limitations" => array(
                ),
            )
        ),
        329 => new \eZ\Publish\API\Repository\Tests\Stubs\Values\User\PolicyStub(
            array(
                "id" => 329,
                "roleId" => 1,
                "module" => "content",
                "function" => "pdf",
                "limitations" => array(
                ),
            )
        ),
        330 => new \eZ\Publish\API\Repository\Tests\Stubs\Values\User\PolicyStub(
            array(
                "id" => 330,
                "roleId" => 3,
                "module" => "ezoe",
                "function" => "*",
                "limitations" => array(
                ),
            )
        ),
        332 => new \eZ\Publish\API\Repository\Tests\Stubs\Values\User\PolicyStub(
            array(
                "id" => 332,
                "roleId" => 3,
                "module" => "ezoe",
                "function" => "*",
                "limitations" => array(
                ),
            )
        ),
        333 => new \eZ\Publish\API\Repository\Tests\Stubs\Values\User\PolicyStub(
            array(
                "id" => 333,
                "roleId" => 1,
                "module" => "rss",
                "function" => "feed",
                "limitations" => array(
                ),
            )
        ),
        334 => new \eZ\Publish\API\Repository\Tests\Stubs\Values\User\PolicyStub(
            array(
                "id" => 334,
                "roleId" => 1,
                "module" => "user",
                "function" => "login",
                "limitations" => array(
                ),
            )
        ),
        335 => new \eZ\Publish\API\Repository\Tests\Stubs\Values\User\PolicyStub(
            array(
                "id" => 335,
                "roleId" => 1,
                "module" => "user",
                "function" => "login",
                "limitations" => array(
                ),
            )
        ),
        336 => new \eZ\Publish\API\Repository\Tests\Stubs\Values\User\PolicyStub(
            array(
                "id" => 336,
                "roleId" => 1,
                "module" => "user",
                "function" => "login",
                "limitations" => array(
                ),
            )
        ),
        337 => new \eZ\Publish\API\Repository\Tests\Stubs\Values\User\PolicyStub(
            array(
                "id" => 337,
                "roleId" => 1,
                "module" => "user",
                "function" => "login",
                "limitations" => array(
                ),
            )
        ),
        338 => new \eZ\Publish\API\Repository\Tests\Stubs\Values\User\PolicyStub(
            array(
                "id" => 338,
                "roleId" => 1,
                "module" => "content",
                "function" => "read",
                "limitations" => array(
                ),
            )
        ),
        339 => new \eZ\Publish\API\Repository\Tests\Stubs\Values\User\PolicyStub(
            array(
                "id" => 339,
                "roleId" => 3,
                "module" => "content",
                "function" => "create",
                "limitations" => array(
                ),
            )
        ),
        340 => new \eZ\Publish\API\Repository\Tests\Stubs\Values\User\PolicyStub(
            array(
                "id" => 340,
                "roleId" => 3,
                "module" => "content",
                "function" => "create",
                "limitations" => array(
                ),
            )
        ),
        341 => new \eZ\Publish\API\Repository\Tests\Stubs\Values\User\PolicyStub(
            array(
                "id" => 341,
                "roleId" => 3,
                "module" => "content",
                "function" => "create",
                "limitations" => array(
                ),
            )
        ),
        342 => new \eZ\Publish\API\Repository\Tests\Stubs\Values\User\PolicyStub(
            array(
                "id" => 342,
                "roleId" => 3,
                "module" => "content",
                "function" => "create",
                "limitations" => array(
                ),
            )
        ),
        343 => new \eZ\Publish\API\Repository\Tests\Stubs\Values\User\PolicyStub(
            array(
                "id" => 343,
                "roleId" => 3,
                "module" => "content",
                "function" => "create",
                "limitations" => array(
                ),
            )
        ),
        344 => new \eZ\Publish\API\Repository\Tests\Stubs\Values\User\PolicyStub(
            array(
                "id" => 344,
                "roleId" => 3,
                "module" => "content",
                "function" => "create",
                "limitations" => array(
                ),
            )
        ),
        345 => new \eZ\Publish\API\Repository\Tests\Stubs\Values\User\PolicyStub(
            array(
                "id" => 345,
                "roleId" => 3,
                "module" => "websitetoolbar",
                "function" => "use",
                "limitations" => array(
                ),
            )
        ),
        346 => new \eZ\Publish\API\Repository\Tests\Stubs\Values\User\PolicyStub(
            array(
                "id" => 346,
                "roleId" => 3,
                "module" => "content",
                "function" => "edit",
                "limitations" => array(
                ),
            )
        ),
        347 => new \eZ\Publish\API\Repository\Tests\Stubs\Values\User\PolicyStub(
            array(
                "id" => 347,
                "roleId" => 3,
                "module" => "content",
                "function" => "read",
                "limitations" => array(
                ),
            )
        ),
        348 => new \eZ\Publish\API\Repository\Tests\Stubs\Values\User\PolicyStub(
            array(
                "id" => 348,
                "roleId" => 3,
                "module" => "notification",
                "function" => "use",
                "limitations" => array(
                ),
            )
        ),
        349 => new \eZ\Publish\API\Repository\Tests\Stubs\Values\User\PolicyStub(
            array(
                "id" => 349,
                "roleId" => 3,
                "module" => "content",
                "function" => "manage_locations",
                "limitations" => array(
                ),
            )
        ),
        350 => new \eZ\Publish\API\Repository\Tests\Stubs\Values\User\PolicyStub(
            array(
                "id" => 350,
                "roleId" => 3,
                "module" => "ezodf",
                "function" => "*",
                "limitations" => array(
                ),
            )
        ),
        351 => new \eZ\Publish\API\Repository\Tests\Stubs\Values\User\PolicyStub(
            array(
                "id" => 351,
                "roleId" => 3,
                "module" => "ezflow",
                "function" => "*",
                "limitations" => array(
                ),
            )
        ),
        352 => new \eZ\Publish\API\Repository\Tests\Stubs\Values\User\PolicyStub(
            array(
                "id" => 352,
                "roleId" => 3,
                "module" => "ezajax",
                "function" => "*",
                "limitations" => array(
                ),
            )
        ),
        353 => new \eZ\Publish\API\Repository\Tests\Stubs\Values\User\PolicyStub(
            array(
                "id" => 353,
                "roleId" => 3,
                "module" => "content",
                "function" => "diff",
                "limitations" => array(
                ),
            )
        ),
        354 => new \eZ\Publish\API\Repository\Tests\Stubs\Values\User\PolicyStub(
            array(
                "id" => 354,
                "roleId" => 3,
                "module" => "content",
                "function" => "versionread",
                "limitations" => array(
                ),
            )
        ),
        355 => new \eZ\Publish\API\Repository\Tests\Stubs\Values\User\PolicyStub(
            array(
                "id" => 355,
                "roleId" => 3,
                "module" => "content",
                "function" => "versionremove",
                "limitations" => array(
                ),
            )
        ),
        356 => new \eZ\Publish\API\Repository\Tests\Stubs\Values\User\PolicyStub(
            array(
                "id" => 356,
                "roleId" => 3,
                "module" => "content",
                "function" => "remove",
                "limitations" => array(
                ),
            )
        ),
        357 => new \eZ\Publish\API\Repository\Tests\Stubs\Values\User\PolicyStub(
            array(
                "id" => 357,
                "roleId" => 3,
                "module" => "content",
                "function" => "translate",
                "limitations" => array(
                ),
            )
        ),
        358 => new \eZ\Publish\API\Repository\Tests\Stubs\Values\User\PolicyStub(
            array(
                "id" => 358,
                "roleId" => 3,
                "module" => "rss",
                "function" => "feed",
                "limitations" => array(
                ),
            )
        ),
        359 => new \eZ\Publish\API\Repository\Tests\Stubs\Values\User\PolicyStub(
            array(
                "id" => 359,
                "roleId" => 3,
                "module" => "content",
                "function" => "bookmark",
                "limitations" => array(
                ),
            )
        ),
        360 => new \eZ\Publish\API\Repository\Tests\Stubs\Values\User\PolicyStub(
            array(
                "id" => 360,
                "roleId" => 3,
                "module" => "content",
                "function" => "pendinglist",
                "limitations" => array(
                ),
            )
        ),
        361 => new \eZ\Publish\API\Repository\Tests\Stubs\Values\User\PolicyStub(
            array(
                "id" => 361,
                "roleId" => 3,
                "module" => "content",
                "function" => "dashboard",
                "limitations" => array(
                ),
            )
        ),
        362 => new \eZ\Publish\API\Repository\Tests\Stubs\Values\User\PolicyStub(
            array(
                "id" => 362,
                "roleId" => 3,
                "module" => "content",
                "function" => "view_embed",
                "limitations" => array(
                ),
            )
        ),
        363 => new \eZ\Publish\API\Repository\Tests\Stubs\Values\User\PolicyStub(
            array(
                "id" => 363,
                "roleId" => 4,
                "module" => "content",
                "function" => "read",
                "limitations" => array(
                ),
            )
        ),
        364 => new \eZ\Publish\API\Repository\Tests\Stubs\Values\User\PolicyStub(
            array(
                "id" => 364,
                "roleId" => 4,
                "module" => "content",
                "function" => "create",
                "limitations" => array(
                ),
            )
        ),
        365 => new \eZ\Publish\API\Repository\Tests\Stubs\Values\User\PolicyStub(
            array(
                "id" => 365,
                "roleId" => 4,
                "module" => "content",
                "function" => "create",
                "limitations" => array(
                ),
            )
        ),
        366 => new \eZ\Publish\API\Repository\Tests\Stubs\Values\User\PolicyStub(
            array(
                "id" => 366,
                "roleId" => 4,
                "module" => "content",
                "function" => "create",
                "limitations" => array(
                ),
            )
        ),
        367 => new \eZ\Publish\API\Repository\Tests\Stubs\Values\User\PolicyStub(
            array(
                "id" => 367,
                "roleId" => 4,
                "module" => "content",
                "function" => "edit",
                "limitations" => array(
                ),
            )
        ),
        368 => new \eZ\Publish\API\Repository\Tests\Stubs\Values\User\PolicyStub(
            array(
                "id" => 368,
                "roleId" => 4,
                "module" => "user",
                "function" => "selfedit",
                "limitations" => array(
                ),
            )
        ),
        369 => new \eZ\Publish\API\Repository\Tests\Stubs\Values\User\PolicyStub(
            array(
                "id" => 369,
                "roleId" => 4,
                "module" => "notification",
                "function" => "use",
                "limitations" => array(
                ),
            )
        ),
        370 => new \eZ\Publish\API\Repository\Tests\Stubs\Values\User\PolicyStub(
            array(
                "id" => 370,
                "roleId" => 5,
                "module" => "content",
                "function" => "create",
                "limitations" => array(
                ),
            )
        ),
        371 => new \eZ\Publish\API\Repository\Tests\Stubs\Values\User\PolicyStub(
            array(
                "id" => 371,
                "roleId" => 5,
                "module" => "content",
                "function" => "create",
                "limitations" => array(
                ),
            )
        ),
        372 => new \eZ\Publish\API\Repository\Tests\Stubs\Values\User\PolicyStub(
            array(
                "id" => 372,
                "roleId" => 5,
                "module" => "content",
                "function" => "create",
                "limitations" => array(
                ),
            )
        ),
        373 => new \eZ\Publish\API\Repository\Tests\Stubs\Values\User\PolicyStub(
            array(
                "id" => 373,
                "roleId" => 5,
                "module" => "content",
                "function" => "edit",
                "limitations" => array(
                ),
            )
        ),
        374 => new \eZ\Publish\API\Repository\Tests\Stubs\Values\User\PolicyStub(
            array(
                "id" => 374,
                "roleId" => 5,
                "module" => "user",
                "function" => "selfedit",
                "limitations" => array(
                ),
            )
        ),
        375 => new \eZ\Publish\API\Repository\Tests\Stubs\Values\User\PolicyStub(
            array(
                "id" => 375,
                "roleId" => 5,
                "module" => "notification",
                "function" => "use",
                "limitations" => array(
                ),
            )
        ),
        376 => new \eZ\Publish\API\Repository\Tests\Stubs\Values\User\PolicyStub(
            array(
                "id" => 376,
                "roleId" => 5,
                "module" => "user",
                "function" => "password",
                "limitations" => array(
                ),
            )
        ),
        377 => new \eZ\Publish\API\Repository\Tests\Stubs\Values\User\PolicyStub(
            array(
                "id" => 377,
                "roleId" => 5,
                "module" => "ezjscore",
                "function" => "call",
                "limitations" => array(
                ),
            )
        ),
    ),
    377,
    array(
        "2" => array(
                    0 => 308,
                ),
        "3" => array(
                    0 => 319,
                    1 => 330,
                    2 => 332,
                    3 => 339,
                    4 => 340,
                    5 => 341,
                    6 => 342,
                    7 => 343,
                    8 => 344,
                    9 => 345,
                    10 => 346,
                    11 => 347,
                    12 => 348,
                    13 => 349,
                    14 => 350,
                    15 => 351,
                    16 => 352,
                    17 => 353,
                    18 => 354,
                    19 => 355,
                    20 => 356,
                    21 => 357,
                    22 => 358,
                    23 => 359,
                    24 => 360,
                    25 => 361,
                    26 => 362,
                ),
        "1" => array(
                    0 => 328,
                    1 => 329,
                    2 => 333,
                    3 => 334,
                    4 => 335,
                    5 => 336,
                    6 => 337,
                    7 => 338,
                ),
        "4" => array(
                    0 => 363,
                    1 => 364,
                    2 => 365,
                    3 => 366,
                    4 => 367,
                    5 => 368,
                    6 => 369,
                ),
        "5" => array(
                    0 => 370,
                    1 => 371,
                    2 => 372,
                    3 => 373,
                    4 => 374,
                    5 => 375,
                    6 => 376,
                    7 => 377,
                ),
    ),
    array(
        "32" => array(
                    "id" => 32,
                    "roleId" => 3,
                    "contentId" => 13,
                    "identifier" => "Subtree",
                    "value" => array(
                        0 => "/1/2/",
                    ),
                ),
        "33" => array(
                    "id" => 33,
                    "roleId" => 3,
                    "contentId" => 13,
                    "identifier" => "Subtree",
                    "value" => array(
                        0 => "/1/43/",
                    ),
                ),
    )
);
