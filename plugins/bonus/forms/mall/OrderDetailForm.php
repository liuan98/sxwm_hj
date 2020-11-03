<?php
/**
 * link: http://www.67930603.top/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\plugins\bonus\forms\mall;

use app\core\response\ApiCode;
use app\models\Order;
use app\models\Model;
use app\models\OrderRefund;
use app\plugins\bonus\models\BonusCaptain;
use app\plugins\bonus\models\BonusOrderLog;
use app\plugins\mch\models\Mch;

class OrderDetailForm extends Model
{
    public $order_id;

    public function rules()
    {
        return [
            [['order_id'], 'required'],
        ];
    }


    public function search()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $order = Order::find()->alias('o')->where([
            'o.mall_id' => \Yii::$app->mall->id,
            'o.id' => $this->order_id,
            'o.is_delete' => 0,
        ])->select(['o.*', 'bo.bonus_price', 'bc.name as captain_name', 'bc.mobile as captain_mobile', 'bo.status as bonus_status', 'bo.remark as bonus_remark'])
            ->leftJoin(['bo' => BonusOrderLog::tableName()], 'bo.order_id = o.id')
            ->leftJoin(['bc' => BonusCaptain::tableName()], 'bc.user_id = bo.to_user_id')
            ->with('user')
            ->with('detail.goods.goodsWarehouse')
            ->with('refund')
            ->with('clerk')
            ->with('store')
            ->with('detailExpress.expressRelation.orderDetail.expressRelation')
            ->asArray()
            ->one();

        if (!$order) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '订单不存在',
            ];
        }
        $order['refund_info'] = [];
        if ($order['refund']) {
            $order['refund_info'] = $order['refund'][0];
            $order['refund'] = (new OrderRefund())->statusText_business($order['refund'][0]);
        }

        foreach ($order['detail'] as $key => $item) {
            $order['detail'][$key]['goods']['pic_url'] = json_decode($item['goods']['goodsWarehouse']['pic_url'], true);
            $order['detail'][$key]['goods']['cover_pic'] = $item['goods']['goodsWarehouse']['cover_pic'];
            $order['detail'][$key]['attr_list'] = json_decode($item['goods_info'], true)['attr_list'];
            $order['detail'][$key]['goods_info'] = json_decode($item['goods_info'], true);
        }

        $order['order_form'] = json_decode($order['order_form'], true);

        //倒计时秒
        $order['auto_cancel'] = strtotime($order['auto_cancel_time']) - time();
        $order['auto_confirm'] = strtotime($order['auto_confirm_time']) - time();
        $order['auto_sales'] = strtotime($order['auto_sales_time']) - time();
        $mch = [];
        // 多商户
        if ($order['mch_id'] > 0) {
            $mch = Mch::findOne(['mall_id' => \Yii::$app->mall->id, 'id' => $order['mch_id']]);
        }
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => [
                'order' => $order,
                'mch' => $mch,
            ]
        ];
    }
}
