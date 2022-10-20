<?php
/**
 * @link https://cms.skeeks.com/
 * @copyright Copyright (c) 2010 SkeekS
 * @license https://cms.skeeks.com/license/
 * @author Semenov Alexander <semenov@skeeks.com>
 */

namespace skeeks\cms\shop\cloudkassa\modulkassa;

use skeeks\cms\helpers\StringHelper;
use skeeks\cms\shop\cloudkassa\CloudkassaHandler;
use skeeks\cms\shop\models\ShopCheck;
use skeeks\yii2\form\fields\FieldSet;
use yii\base\Exception;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\httpclient\Client;

/**
 * @author Semenov Alexander <semenov@skeeks.com>
 */
class ModulkassaHandler extends CloudkassaHandler
{

    /**
     * @var string
     */
    public $login = '';

    /**
     * @var string
     */
    public $password = '';

    /**
     * @var string Идентификатор точки продаж
     */
    public $point_id = '';

    /**
     * @var string
     */
    public $base_api_url = "https://service.modulpos.ru/api";

    /**
     * @var string
     */
    public $base_service_api_url = "https://service.modulpos.ru/api/fn";

    /**
     * @var string
     */
    public $base_demo_service_api_url = "https://demo.modulpos.ru/api/fn";

    /**
     * TODO: эта опция не работает
     * @var bool
     */
    public $is_test = false;

    /**
     * @var int
     */
    public $request_timeout = 20;

    /**
     * @var int
     */
    public $request_maxRedirects = 2;

    /**
     * @return array
     */
    static public function descriptorConfig()
    {
        return array_merge(parent::descriptorConfig(), [
            'name' => \Yii::t('skeeks/shop/app', 'Модулькасса'),
        ]);
    }


    public function rules()
    {
        return ArrayHelper::merge(parent::rules(), [
            [['login'], 'string'],
            [['password'], 'string'],
            [['point_id'], 'string'],
            [['login', 'password', 'point_id'], 'required'],
        ]);
    }

    public function attributeLabels()
    {
        return ArrayHelper::merge(parent::attributeLabels(), [
            'login'    => "Логин от кабинета modulkassa",
            'password' => "Пароль от кабинета modulkassa",
            'point_id' => "Идентификатор точки продаж",
        ]);
    }

    public function attributeHints()
    {
        return ArrayHelper::merge(parent::attributeHints(), [
            'point_id' => 'Идентификатор можно получить в личном кабинете выбрав нужную точку продаж 3769574b-d082-4fe2-8cf0-dee21f9a7e0f',
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
                    'login',
                    'password',
                    'point_id',
                ],
            ],
        ];
    }


    public function sendApiRequest($api_method, $request_method = "GET", $data = [], $base_api_url = null)
    {

        if (!$base_api_url) {
            $base_api_url = $this->base_api_url;
        }

        \Yii::info("sendApiRequest:{$base_api_url}:{$api_method}:{$request_method}:".print_r($data, true), static::class);

        $client = new Client([
            'requestConfig' => [
                'format' => Client::FORMAT_JSON,
            ],
        ]);

        $request = $client->createRequest()
            ->setMethod($request_method)
            ->setUrl($base_api_url."/".$api_method)
            ->addHeaders(['Authorization' => 'Basic '.base64_encode($this->login.":".$this->password)])
            ->setOptions([
                'timeout'      => $this->request_timeout,
                'maxRedirects' => $this->request_maxRedirects,
            ]);

        if ($data) {
            $request->setData($data);
        }

        $response = $request->send();

        if (!$response->isOk) {
            $errormsg = "Error request:".$response->content;
            \Yii::error($errormsg, static::class);
            throw new Exception($errormsg);
        }

        \Yii::info("Success: ".$response->content, static::class);
        return (array)$response->data;
    }

    /**
     * @param $api_method
     * @param $request_method
     * @param $data
     * @param $base_api_url
     * @return array
     * @throws Exception
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\httpclient\Exception
     */
    public function sendServiceApiRequest($api_method, $request_method = "GET", $data = [], $base_api_url = null)
    {


        if (!$base_api_url) {
            $base_api_url = $this->is_test ? $this->base_demo_service_api_url : $this->base_service_api_url;
        }

        \Yii::info("sendServiceApiRequest:{$base_api_url}:{$api_method}:{$request_method}:".print_r($data, true), static::class);

        $client = new Client([
            'requestConfig' => [
                'format' => Client::FORMAT_JSON,
            ],
        ]);

        $fn_username = ArrayHelper::getValue($this->getFnData(), "userName");
        $fn_password = ArrayHelper::getValue($this->getFnData(), "password");
        //print_r($fn_username);
        //print_r("--");
        //print_r($fn_password);
        //print_r(base64_encode($fn_username.":".$fn_password));
        //die;

        /* print_r(Json::encode($data));
         die;*/

        $request = $client->createRequest()
            ->setMethod($request_method)
            ->setUrl($base_api_url."/".$api_method)
            ->addHeaders(['Authorization' => 'Basic '.base64_encode($fn_username.":".$fn_password)])
            ->setOptions([
                'timeout'      => $this->request_timeout,
                'maxRedirects' => $this->request_maxRedirects,
            ]);

        if ($data) {
            $request->setData($data);
        }

        $response = $request->send();

        if (!$response->isOk) {
            $errormsg = "Error request:".$response->content;
            \Yii::error($errormsg, static::class);
            throw new Exception($errormsg);
        }

        \Yii::info("Success: ".$response->content, static::class);

        return (array)$response->data;
    }

    /**
     * Получение информации по всем торговым точкам
     *
     * @return array
     * @throws Exception
     */
    /*public function getRetailPoints()
    {
        return $this->sendApiRequest("v1/retail-points");
    }*/

    /**
     * Получение информации по конкретной торговой точке
     *
     * @param string $retailPointId вида 3769574b-d082-4fe2-8cf0-deee1f9a7e0f
     * @return array
     * @throws Exception
     */

    public function getRetailPoint()
    {
        return $this->sendApiRequest("v1/retail-point/".$this->point_id);
    }



    /**
     * Получение всех смен торговой точки
     *
     * @return array
     * @throws Exception
     */
    /*public function getShifts()
    {
        return $this->sendApiRequest("v1/retail-point/" . $this->point_id . '/shift');
    }*/

    /**
     * Получение документа смены
     *
     * @param string $retailPointId
     * @param string $shiftDocId
     * @return array
     * @throws Exception
     */
    /*public function getShift(string $shiftDocId)
    {
        return $this->sendApiRequest("v1/retail-point/" . $this->point_id . '/shift/' . $shiftDocId);
    }*/
    /**
     * Получение документа смены
     *
     * @param string $retailPointId
     * @param string $shiftDocId
     * @return array
     * @throws Exception
     */
    /*public function createShift($data = [])
    {
        return $this->sendApiRequest("v1/retail-point/" . $this->point_id . '/shift', "POST", $data);
    }*/

    /**
     * Данные для авторизации автофискализации данных
     * @return array|mixed
     * @throws Exception
     */
    public function getFnData()
    {
        $key = "fn_data_".md5($this->login.":".$this->password . ":" . $this->point_id);

        if (!$data = \Yii::$app->cache->get($key)) {
            $data = $this->_associate();
            \Yii::$app->cache->set($key, $data);
        }

        return $data;
    }

    /**
     * @param string $retailPointId
     * @param string $shiftDocId
     * @return array
     * @throws Exception
     */
    protected function _associate($data = [])
    {
        return $this->sendApiRequest("v1/associate/".$this->point_id, "POST", $data, $this->base_service_api_url);
    }
    /**
     *
     * Пример ответа:
     * {
     * "status": "READY",
     * "dateTime": "2019-09-17T03:31:56+00:00"
     * }
     *
     * status Может принимать значения:
     * ● ready - соединение с фискальным накопителемустановлено,
     * состояние позволяет фискализировать чеки
     * ● associated - клиент успешно связан с розничной точкой, но касса
     * еще ни разу не вышла на связь и не сообщила свое состояние
     * ● failed - Проблемы получения статуса фискального накопителя.
     * Этот статус не препятствует добавлению документов для
     * фискализации. Все документы будут добавлены в очередь на
     * сервере и дождутся момента, когда касса будет в состоянии их
     * фискализировать
     * statusDateTime string Строка с датой и временем в ISO форм
     *
     * @return array
     * @throws Exception
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\httpclient\Exception
     */
    public function status()
    {
        return $this->sendServiceApiRequest("v1/status");
    }
    /**
     * @return array
     * @throws Exception
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\httpclient\Exception
     */
    public function createApiDoc($data)
    {
        return $this->sendServiceApiRequest("v2/doc", "POST", $data);
    }

    /**
     * @param string $doc_id
     * @return array
     * @throws Exception
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\httpclient\Exception
     */
    public function getApiStatusDoc(string $doc_id)
    {
        return $this->sendServiceApiRequest("v1/doc/{$doc_id}/status", "GET");
    }


    /**
     * Обновить статус чека
     *
     * @param ShopCheck $shopCheck
     * @return $this
     */
    public function updateStatus(ShopCheck $shopCheck)
    {
        if ($shopCheck->provider_uid) {
            $result = $this->getApiStatusDoc($shopCheck->provider_uid);
            $status = ArrayHelper::getValue($result, 'status');

            /**
             * Имеет следующие значения:
             * ● QUEUED - документ принят в очередь на обработку
             * ● PENDING - документ получен кассой для печати
             * ● PRINTED - фискализирован успешно
             * ● WAIT_FOR_CALLBACK - фискализирован успешно, но не был
             * получен ответ 200 ОК от запроса на адрес, указанный в
             * параметре responseURL
             * ● COMPLETED - результат фискализации отправлен (еслибыло
             * заполнено поле responseURL) в сервис-источник.
             * ● FAILED - ошибка при фискализации
             */
            if (in_array($status, [
                "PRINTED",
                "WAIT_FOR_CALLBACK",
                "COMPLETED",
            ])) {
                $shopCheck->status = ShopCheck::STATUS_APPROVED;
                $shopCheck->provider_response_data = Json::encode($result);
                $shopCheck->qr = ArrayHelper::getValue($result, 'fiscalInfo.qr');;

                $shopCheck->fiscal_check_number = (string) ArrayHelper::getValue($result, 'fiscalInfo.checkNumber');
                $shopCheck->fiscal_date = ArrayHelper::getValue($result, 'fiscalInfo.date');
                if ($shopCheck->fiscal_date) {
                    $shopCheck->fiscal_date_at = \Yii::$app->formatter->asTimestamp(strtotime($shopCheck->fiscal_date));
                }
                $shopCheck->fiscal_ecr_registration_umber = (string) ArrayHelper::getValue($result, 'fiscalInfo.ecrRegistrationNumber');
                $shopCheck->fiscal_fn_doc_mark = (string) ArrayHelper::getValue($result, 'fiscalInfo.fnDocMark');
                $shopCheck->fiscal_kkt_number = (string) ArrayHelper::getValue($result, 'fiscalInfo.kktNumber');
                $shopCheck->fiscal_fn_doc_number = (string) ArrayHelper::getValue($result, 'fiscalInfo.fnDocNumber');
                $shopCheck->fiscal_fn_number = (string) ArrayHelper::getValue($result, 'fiscalInfo.fnNumber');
                $shopCheck->fiscal_shift_number = (string) ArrayHelper::getValue($result, 'fiscalInfo.shiftNumber');
                
                if (!$shopCheck->save()) {
                    $errorMessage = "Ошибка обновления чека: " . print_r($shopCheck->errors, true);
                    \Yii::error($errorMessage, static::class);
                    throw new Exception($errorMessage);
                }
            } elseif (in_array($status, [
                "FAILED",
            ])) {
                $shopCheck->status = ShopCheck::STATUS_ERROR;
                $shopCheck->provider_response_data = Json::encode($result);;

                $shopCheck->error_message = ArrayHelper::getValue($result, 'fiscalInfo.message');
                
                if (!$shopCheck->save()) {
                    $errorMessage = "Ошибка обновления чека: " . print_r($shopCheck->errors, true);
                    \Yii::error($errorMessage, static::class);
                    throw new Exception($errorMessage);
                }
            }
        }

        return $this;
    }

    /**
     * @param ShopCheck $shopCheck
     * @return $this
     * @throws Exception
     * @throws \yii\base\InvalidConfigException
     */
    public function createFiscalCheck(ShopCheck $shopCheck)
    {
        $items = [];

        \Yii::info("createFiscalCheck".print_r($shopCheck->id, true), static::class);

        $uuid = vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex(random_bytes(16)), 4));

        $shopOrder = $shopCheck->shopOrder;

        $pointData = $this->getRetailPoint();

        $shopCheck->seller_address = (string) ArrayHelper::getValue($pointData, "address");
        $shopCheck->seller_name = (string) ArrayHelper::getValue($pointData, "name");
        $shopCheck->seller_inn = (string) ArrayHelper::getValue($pointData, "inn");
        $shopCheck->kkm_payments_address = (string) ArrayHelper::getValue($pointData, "kkmInfo.paymentsAddress");

        $shopCheck->status = ShopCheck::STATUS_WAIT;
        $shopCheck->provider_uid = $uuid;
        //TODO inventPositions //Если что преобразовать
        //TODO moneyPositions //Если что преобразовать
        $data = [
            "docNum"           => $shopOrder->id,
            "id"               => $shopCheck->provider_uid,
            "docType"          => StringHelper::strtoupper($shopCheck->doc_type),
            "checkoutDateTime" => \Yii::$app->formatter->asDatetime(time(), "php:c"),
            "email"            => $shopCheck->email,
            "printReceipt"     => (bool) $shopCheck->is_print,
            "responseURL"      => Url::to(['/modulkassa/modulkassa/backend', "check_id" => $shopCheck->id], true),
            //"textToPrint"      => "Продажа 111",
            "cashierName"      => $shopCheck->cashier_name,
            "cashierPosition"  => $shopCheck->cashier_position,
            "taxMode"          => $shopCheck->tax_mode ? $shopCheck->tax_mode : null, //"SIMPLIFIED", //В настройки
            "inventPositions"  => $shopCheck->inventPositions,
            "moneyPositions"   => $shopCheck->moneyPositions,
        ];

        if (!$shopCheck->save()) {
            $message = "Ошибка создания чека: ".print_r($shopCheck->errors, true);
            \Yii::error($message, static::class);
            throw new Exception($message);
        }

        $shopCheck->provider_request_data = Json::encode($data);

        $result = $this->createApiDoc($data);

        if ($result && is_array($result)) {
            $apiStatus = ArrayHelper::getValue($result, "status");
            $shopCheck->provider_response_data = Json::encode($result);
        }

        if (!$shopCheck->save(false)) {
            //throw new Exception("Ошибка создания чека: " . print_r($shopCheck->errors, true));
        }

        return $this;
    }


    /**
     * @return bool
     * @throws Exception
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\httpclient\Exception
     */
    public function isReady()
    {
        $status = $this->status();
        $statusSring = ArrayHelper::getValue($status, "status");
        if (StringHelper::strtolower($statusSring) == 'ready') {
            return true;
        }

        return false;
    }

    /**
     * @return array
     */
    public function getInfoData()
    {
        $this->getRetailPoint();
        return [];
    }
}