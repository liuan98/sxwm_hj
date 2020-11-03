<?php
/**
 * link: http://www.67930603.top/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: Alpha
 */

namespace app\plugins\pintuan\forms\mall;


use app\core\response\ApiCode;
use app\models\GoodsWarehouse;
use app\models\Model;
use app\plugins\pintuan\models\Goods;
use app\plugins\pintuan\models\Order;
use app\plugins\pintuan\models\PintuanOrderRelation;
use app\plugins\pintuan\models\PintuanOrders;

class PinTuanOrderGroupsForm extends Model
{
    public $id;
    public $keyword;
    public $page;
    public $status;

    public function rules()
    {
        return [
            [['id', 'status'], 'integer'],
            [['keyword'], 'string'],
            [['page'], 'default', 'value' => 1],
        ];
    }

    public function getList()
    {
        // status != 0 未支付的拉团订单不显示
        $query = PintuanOrders::find()->where([
            'mall_id' => \Yii::$app->mall->id,
        ])->andWhere(['!=', 'status', 0]);

        if ($this->status) {
            $query->andWhere(['status' => $this->status]);
        }

        if ($this->keyword) {
            $goodsWarehouseIds = GoodsWarehouse::find()->where(['like', 'name', $this->keyword])->select('id');
            $goodsIds = Goods::find()->where(['goods_warehouse_id' => $goodsWarehouseIds])->select('id');

            $orderIds = Order::find()->where(['mall_id' => \Yii::$app->mall->id, 'is_delete' => 0, 'sign' => \Yii::$app->plugin->currentPlugin->getName()])
                ->andWhere(['like', 'order_no', $this->keyword])->select('id');
            $pintuanOrderId = PintuanOrderRelation::find()->where(['is_delete' => 0, 'order_id' => $orderIds])->select('pintuan_order_id');

            $query->andWhere([
                'or',
                ['goods_id' => $goodsIds],
                ['id' => $pintuanOrderId]
            ]);
        }

        $list = $query->with(['orderRelation.order.user.userInfo', 'orderRelation.order.detail', 'goods.goodsWarehouse'])
            ->page($pagination, 10, $this->page)
            ->orderBy(['created_at' => SORT_DESC])
            ->asArray()
            ->all();

        foreach ($list as $key => $item) {
            $robotNum = 0;
            $newItemList = [];
            /** @var PintuanOrderRelation $orItem */
            foreach ($item['orderRelation'] as $orItem) {
                if ($orItem['robot_id'] > 0) {
                    $robotNum++;
                }
                if (($orItem['order']['is_pay'] == 1 || $orItem['order']['pay_type'] == 2) || $orItem['robot_id'] > 0) {
                    if ($orItem['is_parent']) {
                        $list[$key]['order'] = $orItem['order'];
                        foreach ($orItem['order']['detail'] as $oKey => $oDetail) {
                            $list[$key]['goods_info'] = \Yii::$app->serializer->decode($oDetail['goods_info']);
                        }
                    }
                    // 排除因超出拼团总人数而取消的订单
                    if ($orItem['cancel_status'] == 0) {
                        $newItemList[] = $orItem;
                    }
                }
            }
            $list[$key]['orderRelation'] = $newItemList;
            $list[$key]['robot_num'] = $robotNum;
            $list[$key]['order_count'] = count($newItemList);
        }

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'list' => $list,
                'pagination' => $pagination
            ]
        ];
    }

    public function detail()
    {
        $orderIds = PintuanOrderRelation::find()->where([
            'pintuan_order_id' => $this->id,
            'cancel_status' => 0,
        ])->select('order_id');
        $orderIds = Order::find()->where([
            'id' => $orderIds,
        ])
            ->andWhere([
                'or',
                ['is_pay' => 1],
                ['pay_type' => 2],
            ])
            ->select('id');

        $query = Order::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'id' => $orderIds,
        ])->andWhere([
            'or',
            ['is_pay' => 1],
            ['pay_type' => 2]
        ]);

        if ($this->keyword) {
            $query->andWhere(['like', 'order_no', $this->keyword]);
        }
        $list = $query->with(['user', 'detail.goods.goodsWarehouse', 'orderRelation'])
            ->page($pagination)
            ->asArray()
            ->all();

        /** @var PintuanOrders $pintuanOrder */
        $pintuanOrder = PintuanOrders::find()->where(['id' => $this->id])->one();
        $robotNum = 0;
        foreach ($pintuanOrder->orderRelation as $orItem) {
            if ($orItem->robot_id > 0) {
                $robotNum++;
            }
        }
        foreach ($list as $key => $item) {
            $item['detail'][0]['goods_info'] = \Yii::$app->serializer->decode($item['detail'][0]['goods_info']);
            $list[$key]['goods'] = $item['detail'][0];
            unset($list[$key]['detail']);
            $list[$key]['pintuanOrder'] = $pintuanOrder;
        }


        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'list' => $list,
                'pagination' => $pagination,
                'robotNum' => $robotNum
            ]
        ];
    }
}
