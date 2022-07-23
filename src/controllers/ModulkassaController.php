<?php

namespace skeeks\cms\shop\cloudkassa\modulkassa\controllers;

use skeeks\cms\shop\models\ShopCheck;
use yii\base\Exception;
use yii\web\NotFoundHttpException;
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 */
class ModulkassaController extends \yii\web\Controller
{
    public function actionBackend()
    {
        \Yii::info(__METHOD__, static::class);
        \Yii::info(print_r(\Yii::$app->request->get(), true), static::class);

        $check_id = \Yii::$app->request->get("check_id");
        $status = \Yii::$app->request->get("status");
        $qr = \Yii::$app->request->get("qr");

        /**
         * @var $shopCheck ShopCheck
         */
        $shopCheck = ShopCheck::find()->cmsSite()->andWhere(['id' => $check_id])->one();
        if (!$shopCheck) {
            \Yii::error("Чек не найден в базе сайта!", static::class);
            throw new NotFoundHttpException();
        }

        //Все прошло успешно
        if ($status == 'SUCCESS') {
            $shopCheck->status = ShopCheck::STATUS_APPROVED;
            $shopCheck->qr = $qr;
            $shopCheck->update(false, ['status', 'qr']);
        }
        
        if ($shopCheck->shopCashebox && $shopCheck->shopCashebox->shopCloudkassa) {
            //Обновить статус продажи
            $shopCheck->shopCashebox->shopCloudkassa->handler->updateStatus($shopCheck);
        }

        return "OK";
        
    }
}