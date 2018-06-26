<?php

return [
    'ezuser' => [
        0 => [
            'contentobject_id' => '14',
            'email' => 'foo@example.com',
            'login' => 'admin',
            'password_hash' => 'publish',
            'password_hash_type' => '5',
        ],
    ],
    'eznotification' => [
        0 => [
            'id' => 1,
            'owner_id' => 14,
            'is_pending' => 1,
            'type' => 'Workflow:Review',
            'created' => 1529995052,
            'data' => null,
        ],
        1 => [
            'id' => '2',
            'owner_id' => '14',
            'is_pending' => 0,
            'type' => 'Workflow:Approve',
            'created' => '1529998652',
            'data' => null,
        ],
        2 => [
            'id' => '3',
            'owner_id' => '14',
            'is_pending' => 0,
            'type' => 'Workflow:Reject',
            'created' => '1530002252',
            'data' => null,
        ],
        3 => [
            'id' => '4',
            'owner_id' => '14',
            'is_pending' => 1,
            'type' => 'Workflow:Review',
            'created' => '1530005852',
            'data' => null,
        ],
        4 => [
            'id' => '5',
            'owner_id' => '14',
            'is_pending' => 1,
            'type' => 'Workflow:Review',
            'created' => '1530009452',
            'data' => null,
        ],
        5 => [
            'id' => '6',
            'owner_id' => '10',
            'is_pending' => 0,
            'type' => 'Workflow:Review',
            'created' => '1530009452',
            'data' => null,
        ],
    ],
];
