<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Formhandler',
    'description' => 'The swiss army knife for all kinds of mail forms.',
    'category' => 'frontend',
    'version' => '14.2.2',
    'state' => 'stable',
    'author' => 'Reinhard Führicht',
    'author_email' => 'r.fuehricht@gmail.com',
    'constraints' => [
        'depends' => [
            'typo3' => '12.4.0-14.3.99'
        ],
        'conflicts' => [
        ],
    ],
    'uploadfolder' => 1,
    'createDirs' => '',
    'clearCacheOnLoad' => 1
];
