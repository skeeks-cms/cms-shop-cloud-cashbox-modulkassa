<?php
return [
    'components' => [
        'shop' => [
            'cloudkassaHandlers'             => [
                'modulkassa' => [
                    'class' => \skeeks\cms\shop\cloudkassa\modulkassa\ModulkassaHandler::class
                ]
            ]
        ],
    ],
];