<?php
/**
 * link: http://www.67930603.top/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: Alpha
 */

namespace app\plugins\miaosha\forms\mall;


use app\core\response\ApiCode;
use app\forms\common\goods\CommonGoods;
use app\forms\common\goods\CommonGoodsList;
use app\models\Model;
use app\plugins\miaosha\models\Goods;
use app\plugins\miaosha\models\MiaoshaGoods;
use app\plugins\miaosha\Plugin;
use yii\helpers\ArrayHelper;

class GoodsForm extends Model
{
    public $id;
    public $goods_id;
    public $page;
    public $search;
    public $status;
    public $goods_warehouse_id;
    public $choose_list;
    public $batch_ids;
    public $is_all;
    public $plugin_sign;
    public $continue_goods_count;
    public $continue_order_count;
    public $is_goods_confine;
    public $is_order_confine;
    public $freight_id;

    private $mallMembers = [];

    public function rules()
    {
        return [
            [['id', 'page', 'goods_id', 'status', 'goods_warehouse_id', 'is_all',
                'is_goods_confine', 'is_order_confine', 'continue_goods_count', 'continue_order_count',
                'freight_id'], 'integer'],
            [['page'], 'default', 'value' => 1],
            [['search', 'choose_list', 'batch_ids'], 'safe'],
            [['plugin_sign'], 'string'],
        ];
    }

    public function getList()
    {
        $search = \Yii::$app->serializer->decode($this->search);

        $form = new CommonGoodsList();
        $form->model = 'app\plugins\miaosha\models\Goods';
        $form->sign = \Yii::$app->plugin->getCurrentPlugin()->getName();
        $form->keyword = $search['keyword'];
        $form->relations = ['goodsWarehouse.cats', 'miaoshaGoods'];
        $form->getQuery();
        $list = $form->query->groupBy('goods_warehouse_id')
            ->orderBy(['created_at' => SORT_DESC])
            ->page($pagination)
            ->all();

        $newList = [];
        /** @var Goods $item */
        foreach ($list as $item) {
            $newItem = ArrayHelper::toArray($item);
            $newItem['goodsWarehouse'] = $item->goodsWarehouse ? ArrayHelper::toArray($item->goodsWarehouse) : [];
            $newItem['miaoshaGoods'] = $item->miaoshaGoods ? ArrayHelper::toArray($item->miaoshaGoods) : [];
            try {
                $newItem['goodsWarehouse']['cats'] = $item->goodsWarehouse->cats ? ArrayHelper::toArray($item->goodsWarehouse->cats) : [];
            } catch (\Exception $exception) {
                $newItem['goodsWarehouse']['cats'] = [];
            }
            $count = MiaoshaGoods::find()->where([
                'goods_warehouse_id' => $item->goods_warehouse_id,
                'mall_id' => $item->mall_id,
                'is_delete' => 0,
            ])->count();
            $newItem['miaosha_count'] = $count;
            $newList[] = $newItem;
        }

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'list' => $newList,
                'pagination' => $pagination
            ]
        ];
    }

    public function getMiaoshaList()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $search = \Yii::$app->serializer->decode($this->search);

        $query = MiaoshaGoods::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'is_delete' => 0,
            'goods_warehouse_id' => $this->id
        ]);

        if ($search['date_start'] && $search['date_end']) {
            $query->andWhere([
                'and',
                ['>=', 'open_date', $search['date_start']],
                ['<=', 'open_date', $search['date_end']],
            ]);
        }

        $list = $query->with('goods')->orderBy(['open_date' => SORT_ASC, 'open_time' => SORT_ASC])
            ->page($pagination)->asArray()->all();

        foreach ($list as &$item) {
            $isShowStatus = 1;
            if ($item['open_date'] < date('Y-m-d')) {
                $isShowStatus = 0;
            }
            if ($item['open_date'] == date('Y-m-d') && $item['open_time'] < date('H')) {
                $isShowStatus = 0;
            }
            $item['is_show_status'] = $isShowStatus;
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

    public function getDetail()
    {

        $form = new CommonGoods();
        $res = $form->getGoodsDetail($this->id);
        $miaoshaGoods = MiaoshaGoods::find()->where(['goods_id' => $this->id])->asArray()->one();
        $res['miaoshaGoods'] = $miaoshaGoods;

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'detail' => $res,
            ]
        ];
    }

    public function destroy()
    {
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            $goods = Goods::updateAll([
                'is_delete' => 1,
            ], [
                'mall_id' => \Yii::$app->mall->id,
                'goods_warehouse_id' => $this->goods_warehouse_id,
                'sign' => (new Plugin())->getName()
            ]);

            $miaoshaGoods = MiaoshaGoods::updateAll([
                'is_delete' => 1,
            ], [
                'mall_id' => \Yii::$app->mall->id,
                'goods_warehouse_id' => $this->goods_warehouse_id
            ]);

            $transaction->commit();
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '删除成功',
            ];
        } catch (\Exception $e) {
            $transaction->rollBack();
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage()
            ];
        }
    }

    public function batchDestroy()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }


        $transaction = \Yii::$app->db->beginTransaction();
        try {

            if ($this->is_all) {
                $where = [
                    'mall_id' => \Yii::$app->mall->id,
                    'mch_id' => \Yii::$app->user->identity->mch_id,
                    'sign' => $this->plugin_sign,
                    'is_delete' => 0,
                ];
            } else {
                $where = [
                    'mall_id' => \Yii::$app->mall->id,
                    'mch_id' => \Yii::$app->user->identity->mch_id,
                    'sign' => $this->plugin_sign,
                    'goods_warehouse_id' => $this->batch_ids,
                ];
            }
            $res = Goods::updateAll(['is_delete' => 1], $where);

            if ($this->is_all) {
                $where = [
                    'mall_id' => \Yii::$app->mall->id,
                    'is_delete' => 0,
                ];
            } else {
                $where = [
                    'mall_id' => \Yii::$app->mall->id,
                    'goods_warehouse_id' => $this->batch_ids,
                ];
            }
            $res = MiaoshaGoods::updateAll(['is_delete' => 1], $where);
            $transaction->commit();

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '删除成功',
                'data' => [
                    'num' => $res
                ]
            ];
        } catch (\Exception $e) {
            $transaction->rollBack();
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage()
            ];
        }
    }

    public function miaoshaDestroy()
    {
        $beginTransaction = \Yii::$app->db->beginTransaction();
        try {
            $miaoshaGoods = MiaoshaGoods::findOne([
                'id' => $this->id,
                'mall_id' => \Yii::$app->mall->id
            ]);

            if (!$miaoshaGoods) {
                throw new \Exception('秒杀场次不存在');
            }

            $miaoshaGoods->is_delete = 1;
            $res = $miaoshaGoods->save();
            if (!$res) {
                throw new \Exception($this->getErrorMsg($miaoshaGoods));
            }

            $goods = Goods::findOne(['id' => $miaoshaGoods->goods_id]);
            if (!$goods) {
                throw new \Exception('秒杀商品不存在');
            }
            $goods->is_delete = 0;
            $res = $goods->save();
            if (!$res) {
                throw new \Exception($this->getErrorMsg($goods));
            }
            $beginTransaction->commit();

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '删除成功',
            ];
        } catch (\Exception $e) {
            $beginTransaction->rollBack();
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage()
            ];
        }
    }

    public function switchStatus()
    {
        try {
            /** @var Goods $goods */
            $goods = Goods::find()->where([
                'mall_id' => \Yii::$app->mall->id,
                'id' => $this->id,
                'sign' => \Yii::$app->plugin->getCurrentPlugin()->getName(),
            ])->one();
            if (!$goods) {
                throw new \Exception('商品不存在');
            }
            $goods->status = $goods->status ? 0 : 1;
            $res = $goods->save();
            if (!$res) {
                throw new \Exception($this->getErrorMsg($goods));
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '更新成功',
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage()
            ];
        }
    }

    public function batchMiaoshaDestroy()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $transaction = \Yii::$app->db->beginTransaction();
        try {

            if ($this->is_all) {
                $where = [
                    'mall_id' => \Yii::$app->mall->id,
                    'mch_id' => \Yii::$app->user->identity->mch_id,
                    'goods_warehouse_id' => $this->goods_warehouse_id,
                    'sign' => $this->plugin_sign,
                    'is_delete' => 0,
                ];
            } else {
                $where = [
                    'mall_id' => \Yii::$app->mall->id,
                    'mch_id' => \Yii::$app->user->identity->mch_id,
                    'sign' => $this->plugin_sign,
                    'id' => $this->batch_ids,
                ];
            }
            $res = Goods::updateAll(['is_delete' => 1], $where);

            if ($this->is_all) {
                $where = [
                    'mall_id' => \Yii::$app->mall->id,
                    'goods_warehouse_id' => $this->goods_warehouse_id,
                    'is_delete' => 0,
                ];
            } else {
                $where = [
                    'mall_id' => \Yii::$app->mall->id,
                    'goods_id' => $this->batch_ids,
                ];
            }
            $res = MiaoshaGoods::updateAll(['is_delete' => 1], $where);
            $transaction->commit();

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '删除成功',
                'data' => [
                    'num' => $res
                ]
            ];
        } catch (\Exception $e) {
            $transaction->rollBack();
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage()
            ];
        }
    }

    public function batchUpdateStatus()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        try {
            if ($this->is_all) {
                $where = [
                    'mall_id' => \Yii::$app->mall->id,
                    'mch_id' => \Yii::$app->user->identity->mch_id,
                    'goods_warehouse_id' => $this->goods_warehouse_id,
                    'sign' => $this->plugin_sign,
                    'is_delete' => 0,
                ];
            } else {
                $where = [
                    'mall_id' => \Yii::$app->mall->id,
                    'mch_id' => \Yii::$app->user->identity->mch_id,
                    'sign' => $this->plugin_sign,
                    'id' => $this->batch_ids,
                ];
            }
            $res = Goods::updateAll(['status' => $this->status ? 1 : 0], $where);

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '更新成功',
                'data' => [
                    'num' => $res
                ]
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage()
            ];
        }
    }

    public function batchUpdateFreight()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        try {
            if ($this->is_all) {
                $where = [
                    'mall_id' => \Yii::$app->mall->id,
                    'mch_id' => \Yii::$app->user->identity->mch_id,
                    'goods_warehouse_id' => $this->goods_warehouse_id,
                    'sign' => $this->plugin_sign,
                    'is_delete' => 0,
                ];
            } else {
                $where = [
                    'mall_id' => \Yii::$app->mall->id,
                    'mch_id' => \Yii::$app->user->identity->mch_id,
                    'sign' => $this->plugin_sign,
                    'id' => $this->batch_ids,
                ];
            }
            $res = Goods::updateAll(['freight_id' => $this->freight_id], $where);

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '更新成功',
                'data' => [
                    'num' => $res
                ]
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage()
            ];
        }
    }

    public function batchUpdateConfineCount()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        try {
            if ($this->is_all) {
                $where = [
                    'mall_id' => \Yii::$app->mall->id,
                    'mch_id' => \Yii::$app->user->identity->mch_id,
                    'goods_warehouse_id' => $this->goods_warehouse_id,
                    'sign' => $this->plugin_sign,
                    'is_delete' => 0,
                ];
            } else {
                $where = [
                    'mall_id' => \Yii::$app->mall->id,
                    'mch_id' => \Yii::$app->user->identity->mch_id,
                    'sign' => $this->plugin_sign,
                    'id' => $this->batch_ids,
                ];
            }
            if ($this->continue_goods_count < 0 && !$this->is_goods_confine) {
                throw new \Exception('限购商品数量不能小于0');
            }

            if ($this->continue_order_count < 0 && !$this->is_order_confine) {
                throw new \Exception('限购订单数量不能小于0');
            }

            $goodsCount = (int)$this->continue_goods_count;
            if ($this->is_goods_confine || $goodsCount < 0) {
                $goodsCount = -1;
            }

            $orderCount = (int)$this->continue_order_count;
            if ($this->is_order_confine || $orderCount < 0) {
                $orderCount = -1;
            }

            $res = Goods::updateAll(['confine_count' => $goodsCount, 'confine_order_count' => $orderCount], $where);

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '更新成功',
                'data' => [
                    'num' => $res
                ]
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage()
            ];
        }
    }
}
