<?php
/**
 * link: http://www.67930603.top/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: Alpha
 */

namespace app\forms\api\order;


use app\core\response\ApiCode;
use app\forms\api\goods\MallGoods;
use app\forms\common\template\TemplateList;
use app\models\Express;
use app\models\Model;
use app\models\OrderDetail;
use app\models\OrderRefund;
use app\plugins\advance\models\AdvanceOrder;
use yii\helpers\ArrayHelper;

class OrderRefundForm extends Model
{
    public $id;// 订单详情ID

    public function rules()
    {
        return [
            [['id'], 'required'],
            [['id'], 'integer'],
        ];
    }

    public function getDetail()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        try {
            $detail = OrderDetail::find()->andWhere([
                'id' => $this->id
            ])->with('goods.goodsWarehouse', 'order')->asArray()->one();

            if (!$detail) {
                throw new \Exception('订单不存在');
            }

            $orderDetail = new OrderDetail();
            $goodsAttrInfo = $orderDetail->decodeGoodsInfo($detail['goods_info']);

            $goodsInfo['name'] = $detail['goods']['goodsWarehouse']['name'];
            $goodsInfo['num'] = $detail['num'];
            $goodsInfo['total_original_price'] = $detail['total_original_price'];
            $goodsInfo['member_discount_price'] = $detail['member_discount_price'];
            $goodsInfo['attr_list'] = $goodsAttrInfo['attr_list'];
            $goodsInfo['pic_url'] = $goodsAttrInfo['goods_attr']['pic_url'] ?:
                $detail['goods']['goodsWarehouse']['cover_pic'];
            $detail['goods_info'] = $goodsInfo;
            $detail['refund_price'] = $detail['total_price'] < $detail['order']['total_pay_price'] ?
                $detail['total_price'] : $detail['order']['total_pay_price'];
            //预售尾款订单，售后可退金额为尾款+定金
            if ($detail['goods']['sign'] == 'advance') {
                //判断是否存在插件，是否有插件权限
                $bool = false;
                $permission_arr = \Yii::$app->branch->childPermission(\Yii::$app->mall->user->adminInfo);//取商城所属账户权限
                if (!is_array($permission_arr) && $permission_arr) {
                    $bool = true;
                } else {
                    foreach ($permission_arr as $value) {
                        if ($value == 'advance') {
                            $bool = true;
                            break;
                        }
                    }
                }
                if (\Yii::$app->plugin->getInstalledPlugin('advance') && $bool) {
                    $plugin = \Yii::$app->plugin->getPlugin('advance');
                    /* @var AdvanceOrder $advance_info */
                    $advance_info = $plugin->getAdvance($detail['order']['id'], $detail['order']['order_no']);
                    if (!empty($advance_info)) {
                        $detail['refund_price'] += bcmul($advance_info->goods_num, $advance_info->deposit);
                    }
                }
            }
            $detail['template_message_list'] = $this->getTemplateMessage();

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '请求成功',
                'data' => [
                    'detail' => $detail
                ]
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
                'error' => [
                    'line' => $e->getLine()
                ]
            ];
        }
    }

    private function getTemplateMessage()
    {
        $arr = ['order_refund_tpl'];
        $list = TemplateList::getInstance()->getTemplate(\Yii::$app->appPlatform, $arr);
        return $list;
    }

    /**
     * 售后订单详情
     * @return array
     * @throws \Exception
     */
    public function getOrderRefundDetail()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        /** @var OrderRefund $orderRefund */
        $orderRefund = OrderRefund::find()->alias('o')->where([
            'o.mall_id' => \Yii::$app->mall->id,
            'o.id' => $this->id,
            'o.user_id' => \Yii::$app->user->id,
            'o.is_delete' => 0,
        ])
            ->with('detail.goods.goodsWarehouse', 'order', 'refundAddress')
            ->one();

        if (!$orderRefund) {
            throw new \Exception('订单不存在');
        }

        $newOrderRefund = ArrayHelper::toArray($orderRefund);
        $newOrderRefund['status_text'] = $orderRefund->statusText($orderRefund);
        $newItem = ArrayHelper::toArray($orderRefund->detail);
        $goodsInfo = MallGoods::getGoodsData($orderRefund->detail);
        $newItem['goods_info'] = $goodsInfo;
        $newOrderRefund['detail'][] = $newItem;

        if ($orderRefund->refundAddress) {
            $newOrderRefund['refundAddress'] = ArrayHelper::toArray($orderRefund->refundAddress);
            try {
                $orderRefund->refundAddress->address = \Yii::$app->serializer->decode($orderRefund->refundAddress->address);
            } catch (\Exception $exception) {
                $orderRefund->refundAddress->address = [];
            }
            $refundAddress = '';
            foreach ($orderRefund->refundAddress->address as $item) {
                $refundAddress .= $item;
            }
            $newOrderRefund['refundAddress']['address'] = $refundAddress . $orderRefund->refundAddress->address_detail;
        }

        try {
            $newOrderRefund['pic_list'] = json_decode($newOrderRefund['pic_list']);
        } catch (\Exception $exception) {
            $newOrderRefund['pic_list'] = [];
        }

        $newOrderRefund = array_merge($newOrderRefund, $orderRefund->checkAfterRefund($orderRefund));
        $newOrderRefund['template_message_list'] = $this->getTemplateMessage();
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'detail' => $newOrderRefund,
                'express_list' => Express::getExpressList()
            ]
        ];
    }
}
