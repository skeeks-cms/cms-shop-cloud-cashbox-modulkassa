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
        
        'log' => [
            'targets' => [
                [
                    'class'      => 'yii\log\FileTarget',
                    'levels'     => ['info', 'warning', 'error'],
                    'logVars'    => [],
                    'categories' => [
                        \skeeks\cms\shop\cloudkassa\modulkassa\controllers\ModulkassaController::class, 
                        \skeeks\cms\shop\cloudkassa\modulkassa\ModulkassaHandler::class
                    ],
                    'logFile'    => '@runtime/logs/modulkassa-info.log',
                ],

                [
                    'class'      => 'yii\log\FileTarget',
                    'levels'     => ['error'],
                    'logVars'    => [],
                    'categories' => [
                        \skeeks\cms\shop\cloudkassa\modulkassa\controllers\ModulkassaController::class, 
                        \skeeks\cms\shop\cloudkassa\modulkassa\ModulkassaHandler::class
                    ],
                    'logFile'    => '@runtime/logs/modulkassa-errors.log',
                ],
            ],
        ],
    ],
];