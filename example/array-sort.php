<?php
use \DeathSatan\ArrayHelpers\Arr;
require_once dirname(__DIR__).DIRECTORY_SEPARATOR.'vendor'.DIRECTORY_SEPARATOR.'autoload.php';

$data = [
    [
        'id'=>1,
        'age'=>12,
        'created_at'=>2003
    ],
    [
        'id'=>3,
        'age'=>11,
        'created_at'=>2001
    ],
    [
        'id'=>2,
        'age'=>13,
        'created_at'=>2002
    ]
];


    $res = Arr::sortByField(
        $data,
        [
            'id'=>SORT_ASC,
            'age'=>SORT_DESC
        ]
    );

    var_dump($res);