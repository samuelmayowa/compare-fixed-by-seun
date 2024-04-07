<?php

return [
    '/php-tech/public/search' => [ //this should be /search when host
        [
            'type'      => 'GET',
            'handler'   => 'FormController@index',   
        ],       
    ],
    '/php-tech/public/submit-form' => [ //this should be /submit-form wheen hosted
        [
            'type'      => 'POST',
            'handler'   => 'FormController@submit',
        ],
    ],
    '/php-tech/public/' => [ //this should be / when hosted
        [
            'type'      => 'GET',
            'handler'   => 'FormController@home',
        ],
    ],
    '/php_tech/public/get-more' => [ //this should be / when hosted
        [
            'type'      => 'GET',
            'handler'   => 'FormController@more',
        ],
    ]
];