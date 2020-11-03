<?php
/**
 * link: http://www.67930603.top/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: Alpha
 */

namespace app\plugins\pintuan\forms\mall;


use app\core\response\ApiCode;
use app\forms\common\goods\CommonGoodsList;
use app\models\Mall;
use app\models\Model;
use app\plugins\pintuan\forms\common\CommonGoods;
use app\plugins\pintuan\models\Goods;
use app\plugins\pintuan\models\PintuanGoods;
use app\plugins\pintuan\models\PintuanGoodsAttr;
use app\plugins\pintuan\models\PintuanGoodsGroups;
use yii\helpers\ArrayHelper;

/**
 * @property Mall $mall
 */
class GoodsForm extends Model
{
    public $mall;
    public $id;
    public $search;
    public $page;
    public $sort;
    public $batch_ids;
    public $status;
    public $is_all;
    public $plugin_sign;

    public function rules()
    {
        return [
            [['id', 'page', 'sort', 'status', 'is_all'], 'integer'],
            [['search', 'batch_ids'], 'safe'],
            [['plugin_sign'], 'string']
        ];
    }

    public function attributeLabels()
    {
        return [
            'sort' => '排序',
        ];
    }

    public function getList()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $search = json_decode($this->search, true);
        $common = new CommonGoodsList();
        $common->keyword = $search['keyword'];
        $common->page = $this->page;
        $common->sign = \Yii::$app->plugin->getCurrentPlugin()->getName();
        $common->relations = ['goodsWarehouse.cats', 'attr', 'pintuanGoods', 'groups'];
        $common->model = new Goods();

        if (array_key_exists('sort_prop', $search) && $search['sort_prop']) {
            $common->sort = 6;
            $common->sort_prop = $search['sort_prop'];
            $common->sort_type = $search['sort_type'];
        } else {
            $common->sort = 2;
        }
        if (array_key_exists('status', $search) && $search['status'] != -1) {
            if ($search['status'] == 0 || $search['status'] == 1) {
                $common->status = $search['status'];
            } else if ($search['status'] == 2) {
                $common->is_sold_out = 1;
            }
        }
        $list = $common->search();

        $newList = [];
        /* @var Goods[] $list */
        foreach ($list as $item) {
            $newItem = ArrayHelper::toArray($item);
            $newItem = array_merge($newItem, [
                'name' => $item->goodsWarehouse->name,
                'cover_pic' => $item->goodsWarehouse->cover_pic,
                'num_count' => 0,
                'cats' => $item->goodsWarehouse->cats,
                'status' => $item->status,
                'is_sell_well' => $item->pintuanGoods->is_sell_well,
                'pintuanGoods' => $item->pintuanGoods,
                'groups' => $item->groups,
                'goodsWarehouse' => $item->goodsWarehouse
            ]);
            foreach ($item->attr as $attr) {
                $newItem['num_count'] += $attr->stock;
            }
            $newList[] = $newItem;
        }

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'pagination' => $common->pagination,
                'list' => $newList
            ]
        ];
    }

    public function search()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        if (!$this->mall) {
            $this->mall = \Yii::$app->mall;
        }

        $common = \app\forms\common\goods\CommonGoods::getCommon();
        $detail = $common->getGoodsDetail($this->id);
        $commonGoods = CommonGoods::getCommon();
        $goods = $commonGoods->getPintuanGoods($this->id);

        if (!$goods) {
            throw new \Exception('商品不存在');
        }

        /* @var PintuanGoodsGroups[] $pintuanGroups */
        $pintuanGroups = PintuanGoodsGroups::find()->with(['attr.goodsAttr', 'attr.memberPrice'])
            ->where(['is_delete' => 0, 'goods_id' => $this->id])->all();
        $groupMinPrice = 0;
        $groupMaxPrice = 0;
        if (count($pintuanGroups) > 0) {
            /** @var PintuanGoodsAttr $attr */
            foreach ($pintuanGroups[0]->attr as $attr) {
                $groupMinPrice = $groupMinPrice ? min($groupMinPrice, $attr->pintuan_price) : $attr->pintuan_price;
                $groupMaxPrice = $groupMaxPrice ? max($groupMaxPrice, $attr->pintuan_price) : $attr->pintuan_price;
            }
        }
        $detail['group_min_price'] = $groupMinPrice;
        $detail['group_max_price'] = $groupMaxPrice;

        $detail['plugin'] = [
            'is_alone_buy' => $goods->is_alone_buy,
            'goods_id' => $goods->goods_id,
            'end_time' => $goods->end_time,
            'groups_restrictions' => $goods->groups_restrictions,
            'is_sell_well' => $goods->is_sell_well,
            'goods_groups_count' => count($pintuanGroups)
        ];


        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => [
                'detail' => $detail
            ]
        ];
    }

    public function switchStatus()
    {
        try {
            $goods = CommonGoods::getGoodsDetail($this->id);

            $pintuanGoods = PintuanGoods::findOne(['goods_id' => $goods->id]);
            if (!$pintuanGoods) {
                throw new \Exception('拼团商品不存在');
            }
            if ($pintuanGoods->end_time < mysql_timestamp()) {
                throw new \Exception('拼团结束时间需大于当前时间');
            }

            $goodsCount = PintuanGoodsGroups::find()->andWhere(['goods_id' => $goods->id, 'is_delete' => 0])->count();
            if (!$goodsCount && !$goods->status) {
                throw new \Exception('拼团商品至少需要添加一个拼团组,商品才可上架');
            }

            $goods->status = $goods->status ? 0 : 1;
            $res = $goods->save();

            if (!$res) {
                throw new \Exception($this->getErrorMsg($goods));
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '更新成功'
            ];
        } catch (\Exception $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage()
            ];
        }
    }

    public function switchSellWell()
    {
        try {
            $goods = CommonGoods::getCommon()->getPintuanGoods($this->id);
            $goods->is_sell_well = $goods->is_sell_well ? 0 : 1;
            $res = $goods->save();

            if (!$res) {
                throw new \Exception($this->getErrorMsg($goods));
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功'
            ];
        } catch (\Exception $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage()
            ];
        }
    }

    public function updateHotSell()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        if ($this->is_all) {
            $where = [
                'mall_id' => \Yii::$app->mall->id,
                'is_delete' => 0,
            ];
        } else {
            $where = [
                'mall_id' => \Yii::$app->mall->id,
                'goods_id' => $this->batch_ids,
            ];
        }

        $res = PintuanGoods::updateAll([
            'is_sell_well' => $this->status ? 1 : 0
        ], $where);

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '更新成功',
            'data' => [
                'num' => $res
            ]
        ];
    }

    public function batchUpdateStatus()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        if ($this->is_all) {
            $where = [
                'mall_id' => \Yii::$app->mall->id,
                'sign' => $this->plugin_sign,
                'is_delete' => 0,
            ];
        } else {
            $where = [
                'mall_id' => \Yii::$app->mall->id,
                'sign' => $this->plugin_sign,
                'id' => $this->batch_ids,
            ];
        }

        $res = Goods::updateAll([
            'status' => $this->status
        ], $where);

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '更新成功',
            'data' => [
                'num' => $res
            ]
        ];
    }
}
