<?php
return [
    ''                                                 => 'main/default/index',
    '<_a:(contact|about|agreement|error)>'             => 'main/default/<_a>',
    '<_a:(login|logout|registration)>'                 => 'user/default/<_a>',
    'emailconfirm/<emailConfirm:[\w\-]{32}>'           => 'user/default/email-confirm',
    'get-confirmation-link'                            => 'user/default/get-confirmation-link',
    '<_a:test/.*>'                                     => 'test/default/index',

    'user/<id:\d+>'                                    => 'user/default/view',

    'article/<slug:[\w\-]+>'                           => 'article/default/view',
    'article/category/<slug:[\w\-]+>'                  => 'article/default/category',
    'article/<_a:[\w\-]+>/<id:\d+>'                    => 'article/default/<_a>',

    'filestorage/<path:[\w\-]+>/<filename:[\w\-]{32}>' => '/filestorage/default/index',
    'filestorage/<filename:[\w\-]{32}>'                => '/filestorage/default/index',

    '<_m:[\w\-]+>/<_c:[\w\-]+>/<id:\d+>'               => '<_m>/<_c>/view',
    '<_m:[\w\-]+>/<_c:[\w\-]+>/<_a:[\w\-]+>/<id:\d+>'  => '<_m>/<_c>/<_a>',
    '<_m:[\w\-]+>'                                     => '<_m>/default/index',
    '<_m:[\w\-]+>/<_c:[\w\-]+>'                        => '<_m>/<_c>/index',
];