<?php
/**
 * @link https://cms.skeeks.com/
 * @copyright Copyright (c) 2010 SkeekS
 * @license https://cms.skeeks.com/license/
 * @author Semenov Alexander <semenov@skeeks.com>
 */

namespace skeeks\cms\shop\cloudkassa\modulkassa;

use skeeks\cms\shop\cloudkassa\CloudkassaHandler;
use skeeks\yii2\form\fields\BoolField;
use skeeks\yii2\form\fields\FieldSet;
use yii\helpers\ArrayHelper;

/**
 * @author Semenov Alexander <semenov@skeeks.com>
 */
class ModulkassaHandler extends CloudkassaHandler
{

    /**
     * @var string Какой город отображается по умолчанию
     */
    public $defaultCity = '';

    /**
     * @var string Из какого города будет идти доставка
     */
    public $cityFrom = 'Москва';

    /**
     * @var string Можно выбрать страну, для которой отображать список ПВЗ
     */
    public $country = 'Россия';
    /**
     * @var string Рассчитывать цену по выбранному ПВЗ?
     */
    public $isCalculatePrice = 0;
    /**
     * @var string Рассчитывать цену по выбранному ПВЗ?
     */
    public $isRequiredSelectPoint = 1;

    /**
     * @var string
     */
    public $checkoutModelClass = CdekCheckoutModel::class;
    public $checkoutWidgetClass = CdekCheckoutWidget::class;

    /**
     * @return array
     */
    static public function descriptorConfig()
    {
        return array_merge(parent::descriptorConfig(), [
            'name' => \Yii::t('skeeks/shop/app', 'СДЭК'),
        ]);
    }


    public function rules()
    {
        return ArrayHelper::merge(parent::rules(), [
            [['defaultCity'], 'string'],
            [['cityFrom'], 'string'],
            [['country'], 'string'],
            [['isCalculatePrice'], 'integer'],
            [['isRequiredSelectPoint'], 'integer'],
        ]);
    }

    public function attributeLabels()
    {
        return ArrayHelper::merge(parent::attributeLabels(), [
            'defaultCity'           => "Какой город отображается по умолчанию",
            'cityFrom'              => "Из какого города будет идти доставка",
            'country'               => "Можно выбрать страну, для которой отображать список ПВЗ",
            'isCalculatePrice'      => "Рассчитывать цену по выбранному ПВЗ?",
            'isRequiredSelectPoint' => "Для оформления заказа ПВЗ должен быть выбран обязательно?",

            /*'api_key'     => "Ключ api",

            'custom_city' => "Город",
            'weight' => "Вес заказа",

            'height' => "Высота коробки заказа",
            'width'  => "Ширина коробки заказа",
            'depth'  => "Глубина коробки заказа",*/
        ]);
    }

    public function attributeHints()
    {
        return ArrayHelper::merge(parent::attributeHints(), [
            'defaultCity'           => "Есди город не указан, то будет определен автоматически по координатам пользователя.",
            'isCalculatePrice'      => "Если выбрано нет, то цена за доставку не будет рассчитываться.",
            'isRequiredSelectPoint' => "Если выбрано да - то без выбранного ПВЗ заказ оформить не получится. Если выбрано нет - то заказ можно оформить без выбора ПВЗ",
        ]);
    }


    /**
     * @return array
     */
    public function getConfigFormFields()
    {
        return [
            'main' => [
                'class'  => FieldSet::class,
                'name'   => 'Основные',
                'fields' => [
                    'defaultCity',
                    'cityFrom',
                    'country',
                    'isCalculatePrice'      => [
                        'class' => BoolField::class,
                    ],
                    'isRequiredSelectPoint' => [
                        'class' => BoolField::class,
                    ],
                ],
            ],
            /*'default' => [
                'class'  => FieldSet::class,
                'name'   => 'Данные по умолчанию',
                'fields' => [
                    'custom_city',

                    'weight' => [
                        'class' => WidgetField::class,
                        'widgetClass' => SmartWeightInputWidget::class
                    ],

                    'height' => [
                        'class' => NumberField::class,
                        'append' => 'см.'
                    ],

                    'width' => [
                        'class' => NumberField::class,
                        'append' => 'см.'
                    ],

                    'depth' => [
                        'class' => NumberField::class,
                        'append' => 'см.'
                    ]
                ],
            ],*/
        ];
    }
}