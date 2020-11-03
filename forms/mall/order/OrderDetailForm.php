<?php
/**
 * link: http://www.67930603.top/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\forms\mall\order;

use app\core\response\ApiCode;
use app\forms\common\mch\MchSettingForm;
use app\models\Order;
use app\models\Model;
use app\models\OrderRefund;
use app\models\User;
use app\plugins\mch\models\Mch;

class OrderDetailForm extends Model
{
    public $order_id;

    // 前端操作 显示设置
    public $is_send_show;
    public $is_cancel_show;
    public $is_clerk_show;
    public $is_confirm_show;

    public function rules()
    {
        return [
            [['order_id'], 'required'],
            [['is_confirm_show', 'is_send_show', 'is_cancel_show', 'is_clerk_show'], 'default', 'value' => 1],
        ];
    }


    public function search()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $order = Order::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'id' => $this->order_id,
            'is_delete' => 0,
        ])
            ->with('user', 'shareOrder.user', 'refund', 'clerk', 'orderClerk', 'store')
            ->with('detail.goods.goodsWarehouse', 'detail.expressRelation')
            ->with('detailExpress.expressRelation.orderDetail.expressRelation')
            ->with('detailExpress.expressSingle')
            ->with('refund')
            ->with('clerk')
            ->with('orderClerk')
            ->with('store')
            ->with('paymentOrder.paymentOrderUnion')
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

        $existsFormIds = [];
        foreach ($order['detail'] as $key => $item) {
            $order['detail'][$key]['goods']['pic_url'] = json_decode($item['goods']['goodsWarehouse']['pic_url'], true);
            $order['detail'][$key]['goods']['cover_pic'] = $item['goods']['goodsWarehouse']['cover_pic'];
            $order['detail'][$key]['attr_list'] = json_decode($item['goods_info'], true)['attr_list'];
            $order['detail'][$key]['goods_info'] = json_decode($item['goods_info']);
            $order['detail'][$key]['form_data'] = $item['form_data'] ? \Yii::$app->serializer->decode($item['form_data']) : null;
            $sameForm = false;
            if ($order['detail'][$key]['form_id']) {
                if (in_array($order['detail'][$key]['form_id'], $existsFormIds)) {
                    $sameForm = true;
                } else {
                    $existsFormIds[] = $order['detail'][$key]['form_id'];
                }
            }
            $order['detail'][$key]['same_form'] = $sameForm;
        }

        foreach ($order['detailExpress'] as &$detailExpress) {
            foreach ($detailExpress['expressRelation'] as &$expressRelation) {
                $expressRelation['orderDetail']['goods_info'] = \Yii::$app->serializer->decode($expressRelation['orderDetail']['goods_info']);
            }
            unset($expressRelation);
        }
        unset($detailExpress);

        $order['order_form'] = json_decode($order['order_form'], true);

        //倒计时秒
        $order['auto_cancel'] = $order['is_send'] == 0 ? strtotime($order['auto_cancel_time']) - time() : 0;
        $order['auto_confirm'] = $order['is_confirm'] == 0 ? strtotime($order['auto_confirm_time']) - time() : 0;
        $order['auto_sales'] = ($order['is_confirm'] == 1 && $order['is_sale'] == 0) ? strtotime($order['auto_sales_time']) - time() : 0;
        $order['city'] = json_decode($order['city_info'], true);
        $mch = [];
        // 多商户
        if ($order['mch_id'] > 0) {
            $mch = Mch::findOne(['mall_id' => \Yii::$app->mall->id, 'id' => $order['mch_id']]);
        }

        $order = $this->getShareOrderData($order);

        $plugins = \Yii::$app->role->getPluginList();
        $order['plugin_data'] = [];
        foreach ($plugins as $key => $plugin) {
            if (($key == $order['sign'] || $key == 'vip_card') && method_exists($plugin, 'getOrderInfo')) {
                $order['plugin_data'] = array_merge($order['plugin_data'], $plugin->getOrderInfo($order['id']));
            }
        }

        // 控制订单操作 是否显示(例如拼团)
        $order['is_send_show'] = $this->is_send_show;
        $order['is_cancel_show'] = $this->is_cancel_show;
        $order['is_clerk_show'] = $this->is_clerk_show;
        $order['is_confirm_show'] = $this->is_confirm_show;

        $order['action_status'] = (new Order())->getOrderActionStatus($order);


        if (\Yii::$app->user->identity->mch_id > 0) {
            $mchSettingForm = new MchSettingForm();
            $mchSetting = $mchSettingForm->search();
            $order['is_confirm_show'] = $mchSetting['is_confirm_order'] ? 1 : 0;
        }

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => [
                'order' => $order,
                'mch' => $mch,
            ]
        ];
    }

    // 获取分销订单信息
    private function getShareOrderData($order)
    {
        $firstPrice = 0;
        $secondPrice = 0;
        $thirdPrice = 0;
        foreach ($order['shareOrder'] as $index => $item) {
            $firstPrice += $item['first_price'];
            $secondPrice += $item['second_price'];
            $thirdPrice += $item['third_price'];
        }

        $parentId = [];
        foreach ($order['shareOrder'] as $item) {
            if (!in_array($item['first_parent_id'], $parentId)) {
                $parentId[] = $item['first_parent_id'];
            }
            if (!in_array($item['second_parent_id'], $parentId)) {
                $parentId[] = $item['second_parent_id'];
            }
            if (!in_array($item['third_parent_id'], $parentId)) {
                $parentId[] = $item['third_parent_id'];
            }
        }
        $newShareOrder = [];
        /* @var User[] $parent */
        $parent = User::find()->where(['id' => $parentId])->with('share')->all();
        foreach ($order['shareOrder'] as $index => $item) {
            $first = null;
            $second = null;
            $third = null;
            foreach ($parent as $value) {
                if ($value->id == $item['first_parent_id']) {
                    $first = $value;
                }
                if ($value->id == $item['second_parent_id']) {
                    $second = $value;
                }
                if ($value->id == $item['third_parent_id']) {
                    $third = $value;
                }
            }
            $item['first_parent'] = [
                'nickname' => $first->nickname,
                'name' => $first->share ? $first->share->name : '',
                'mobile' => $first->share ? $first->share->mobile : '',
            ];
            $item['second_parent'] = $second ? [
                'nickname' => $second->nickname,
                'name' => $second->share ? $second->share->name : '',
                'mobile' => $second->share ? $second->share->mobile : '',
            ] : null;
            $item['third_parent'] = $third ? [
                'nickname' => $third->nickname,
                'name' => $third->share ? $third->share->name : '',
                'mobile' => $third->share ? $third->share->mobile : '',
            ] : null;

            $newShareItem = $item;
            $newShareItem['is_zigou'] = $item['user_id'] == $item['first_parent_id'] ? 1 : 0;
            $newShareItem['first_parent'] = $item['first_parent'];
            $newShareItem['second_parent'] = $item['second_parent'];
            $newShareItem['third_parent'] = $item['third_parent'];
            $newShareOrder = $newShareItem;
        }

        $newShareOrder['first_price'] = $firstPrice;
        $newShareOrder['second_price'] = $secondPrice;
        $newShareOrder['third_price'] = $thirdPrice;

        $order['shareOrder'] = [$newShareOrder];

        return $order;
    }
}
