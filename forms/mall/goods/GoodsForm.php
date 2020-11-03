<?php
/**
 * link: http://www.67930603.top/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: Alpha
 */

namespace app\forms\mall\goods;

use app\core\response\ApiCode;
use app\forms\common\goods\CommonGoods;
use app\forms\common\goods\CommonGoodsList;
use app\forms\common\goods\GoodsBase;
use app\models\Goods;
use app\models\GoodsCatRelation;
use app\models\GoodsCats;
use app\models\GoodsWarehouse;
use app\models\MallGoods;
use yii\db\Query;
use yii\helpers\ArrayHelper;

class GoodsForm extends GoodsBase
{
    public function getList()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        try {
            $search = \Yii::$app->serializer->decode($this->search);
        } catch (\Exception $exception) {
            $search = [];
        }
        $query = Goods::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'is_delete' => 0,
            'sign' => \Yii::$app->user->identity->mch_id > 0 ? 'mch' : '',
            'mch_id' => \Yii::$app->user->identity->mch_id,
        ]);

        // 商品名称搜索
        if (isset($search['keyword']) && $search['keyword']) {
            $keyword = trim($search['keyword']);
            $goodsIds = GoodsWarehouse::find()->andWhere(['is_delete' => 0])
                ->keyword($keyword, ['LIKE', 'name', $keyword])->select('id');
            $query->andWhere([
                'or',
                ['like', 'id', $search['keyword']],
                ['goods_warehouse_id' => $goodsIds]
            ]);
        }
        // 商品排序
        if (isset($search['sort_prop']) && $search['sort_prop'] && isset($search['sort_type'])) {
            $sortType = $search['sort_type'] ? SORT_ASC : SORT_DESC;
            $query->orderBy([$search['sort_prop'] => $sortType]);
        } else {
            $query->orderBy('created_at DESC');
        }
        if (isset($search['status']) && (int)$search['status'] !== -1) {
            if ((int)$search['status'] === 0 || (int)$search['status'] === 1) {
                // 上下架状态
                $query->andWhere(['status' => $search['status']]);
            } elseif ((int)$search['status'] === 2) {
                // 售罄
                $query->andWhere(['goods_stock' => 0]);
            }
        }

        // 分类搜索
        if (isset($search['cats']) && $search['cats']) {
            $query = $this->addCatWhere($search['cats'], $query);
        }
        if (\Yii::$app->user->identity->mch_id > 0) {
            $query->with('mchGoods');
        }

        // 日期搜索
        if (isset($search['date_start']) && $search['date_start'] && isset($search['date_end']) && $search['date_end']) {
            $query->andWhere(['>=', 'created_at', $search['date_start']]);
            $query->andWhere(['<=', 'created_at', $search['date_end']]);
        }

        if ($this->cat_id) {
            $goodsCatRelation = GoodsCatRelation::find()->where(['cat_id' => $this->cat_id, 'is_delete' => 0])->all();
            if (!$goodsCatRelation) {
                $goodsWarehouseIds = '0';
                /** @var GoodsCatRelation $item */
                foreach ($goodsCatRelation as $item) {
                    $goodsWarehouseIds .= ',' . $item->goods_warehouse_id;
                }
                $query->andWhere('goods_warehouse_id in (' . $goodsWarehouseIds . ')');
            }

        }

        $list = $query->with('goodsWarehouse.cats', 'goodsWarehouse.mchCats', 'attr', 'mallGoods')
            ->page($pagination)->all();

        $newList = [];
        /** @var Goods $item */
        foreach ($list as $item) {
            $newItem = ArrayHelper::toArray($item);
            $newItem['goodsWarehouse'] = isset($item->goodsWarehouse) ? ArrayHelper::toArray($item->goodsWarehouse) : [];
            $newItem['mchGoods'] = isset($item->mchGoods) ? ArrayHelper::toArray($item->mchGoods) : [];
            $newItem['cats'] = isset($item->goodsWarehouse->cats) ? ArrayHelper::toArray($item->goodsWarehouse->cats) : [];
            $newItem['mchCats'] = isset($item->goodsWarehouse->mchCats) ? ArrayHelper::toArray($item->goodsWarehouse->mchCats) : [];
            $newItem['mallGoods'] = isset($item->mallGoods) ? ArrayHelper::toArray($item->mallGoods) : [];
            $newItem['name'] = isset($item->goodsWarehouse->name) ? $item->goodsWarehouse->name : '';
            $goodsStock = 0;
            foreach ($item->attr as $aItem) {
                $goodsStock += $aItem->stock;
            }
            $newItem['goods_stock'] = $goodsStock;
            $newList[] = $newItem;
        }

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'list' => $newList,
                'pagination' => $pagination,
            ]
        ];
    }

    /**
     * @param $catIds
     * @param Query $query
     * @return mixed
     */
    private function addCatWhere($catIds, $query)
    {
        if (!$catIds) {
            return $query;
        }
        $cat = GoodsCats::find()->select('id')
            ->where([
                'is_delete' => 0,
                'mall_id' => \Yii::$app->mall->id,
                'mch_id' => \Yii::$app->user->identity->mch_id,
                'status' => 1
            ])
            ->andWhere([
                'OR',
                ['parent_id' => GoodsCats::find()->where([
                    'parent_id' => $catIds
                ])->select('id')],
                ['parent_id' => $catIds],
                ['id' => $catIds],
            ]);
        $goodsCatRelation = GoodsCatRelation::find()->select('goods_warehouse_id')
            ->where(['is_delete' => 0])->andWhere(['in', 'cat_id', $cat]);
        $goodsWarehouseId = GoodsWarehouse::find()->where(['mall_id' => \Yii::$app->mall->id, 'is_delete' => 0])
            ->andWhere(['id' => $goodsCatRelation])->select('id');
        $query->andWhere(['goods_warehouse_id' => $goodsWarehouseId]);

        return $query;
    }

    public function getDetail()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        try {
            $common = CommonGoods::getCommon();
            $detail = $common->getGoodsDetail($this->id);

            $mallGoods = $common->getMallGoods($this->id);
            if (!$mallGoods) {
                throw new \Exception('数据异常，mallGoods商品不存在');
            }
            $detail['status'] = intval($detail['status']);
            $detail = array_merge($detail, [
                'is_quick_shop' => $mallGoods->is_quick_shop,
                'is_sell_well' => $mallGoods->is_sell_well,
                'is_negotiable' => $mallGoods->is_negotiable,
            ]);

            if ($detail) {
                return [
                    'code' => ApiCode::CODE_SUCCESS,
                    'msg' => '请求成功',
                    'data' => [
                        'detail' => $detail
                    ]
                ];
            }

            throw new \Exception('请求失败');
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
                'error' => [
                    'line' => $e->getLine(),
                ]
            ];
        }
    }

    public function getRecommendGoodsList()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        try {
            $search = \Yii::$app->serializer->decode($this->search);
        } catch (\Exception $exception) {
            $search = [];
        }

        $form = new CommonGoodsList();
        $form->sign = ['', 'mch'];
        $form->relations = ['goodsWarehouse'];
        $form->status = 1;
        $form->page = $this->page;
        $form->keyword = isset($search['keyword']) ? $search['keyword'] : '';
        $list = $form->search();

        $newList = [];
        /** @var Goods $item */
        foreach ($list as $item) {
            $arr = ArrayHelper::toArray($item);
            $arr['name'] = $item->getName();
            $arr['cover_pic'] = $item->getCoverPic();
            $newList[] = $arr;
        }

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'list' => $newList,
                'pagination' => $form->pagination
            ]
        ];
    }

    public function batchUpdateQuick()
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

        $res = MallGoods::updateAll(['is_quick_shop' => $this->status], $where);
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '更新成功',
            'data' => [
                'num' => $res
            ]
        ];
    }

    public function batchUpdateNegotiable()
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

        $res = MallGoods::updateAll(['is_negotiable' => $this->status], $where);
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '更新成功',
            'data' => [
                'num' => $res
            ]
        ];
    }
}
