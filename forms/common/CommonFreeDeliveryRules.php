<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/13
 * Time: 10:39
 */

namespace app\forms\common;


use app\models\FreeDeliveryRules;
use Yii;

class CommonFreeDeliveryRules
{
    public static function deleteItem($id = null)
    {
        $model = FreeDeliveryRules::findOne([
            'id' => $id,
            'mall_id' => Yii::$app->mall->id,
            'mch_id' => Yii::$app->user->identity->mch_id,
            'is_delete' => 0
        ]);
        if (!$model) {
            return [
                'code' => 1,
                'msg' => '没有可删除的选项'
            ];
        } else {
            $model->is_delete = 1;
            if ($model->save()) {
                return [
                    'code' => 0,
                    'msg' => '删除成功'
                ];
            } else {
                return [
                    'code' => 1,
                    'msg' => $model->errors[0]
                ];
            }
        }
    }
}