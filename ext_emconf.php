<?php

$EM_CONF['formhandler'] = [
    'title' => 'Formhandler',
    'description' => 'The swiss army knife for all kinds of mail forms.',
    'category' => 'frontend',
    'version' => '13.0.0',
    'state' => 'stable',
    'author' => 'Reinhard Führicht',
    'author_email' => 'r.fuehricht@gmail.com',
    'constraints' => [
        'depends' => [
            'typo3' => '12.0.0-13.99.99'
        ],
        'conflicts' => [
        ],
    ],
    'uploadfolder' => 1,
    'createDirs' => '',
    'clearCacheOnLoad' => 1
];
