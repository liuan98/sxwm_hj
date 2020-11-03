<?php
/**
 * link: http://www.67930603.top/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: Alpha
 */

namespace app\forms\mall\goods;


use app\core\response\ApiCode;
use app\models\BaseQuery\BaseActiveQuery;
use app\models\Goods;
use app\models\GoodsCatRelation;
use app\models\GoodsCats;
use app\models\GoodsWarehouse;
use app\models\Model;
use app\plugins\mch\models\MchGoods;
use yii\db\Query;
use yii\helpers\ArrayHelper;

abstract class BaseGoodsList extends Model
{
    public $search;
    public $goodsModel = 'app\models\Goods';

    public function rules()
    {
        return [
            [['search'], 'safe']
        ];
    }


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

        /** @var Goods $model */
        $model = $this->goodsModel;
        $query = $model::find()->alias('g')->where([
            'g.mall_id' => \Yii::$app->mall->id,
            'g.is_delete' => 0,
        ]);

        // 商品名称搜索
        if (isset($search['keyword']) && $search['keyword']) {
            $keyword = trim($search['keyword']);
            $goodsIds = GoodsWarehouse::find()->andWhere(['is_delete' => 0])
                ->keyword($keyword, ['LIKE', 'name', $keyword])->select('id');
            $query->andWhere([
                'or',
                ['like', 'g.id', $search['keyword']],
                ['g.goods_warehouse_id' => $goodsIds]
            ]);
        }
        // 商品排序
        if (isset($search['sort_prop']) && $search['sort_prop'] && isset($search['sort_type'])) {
            $sortType = $search['sort_type'] ? SORT_ASC : SORT_DESC;
            if ($search['sort_prop'] == 'mchGoods.sort') {
                $query->leftJoin(['mg' => MchGoods::tableName()], 'mg.goods_id=g.id');
                $query->orderBy(['mg.sort' => $sortType]);
            } else {
                $query->orderBy(['g.' . $search['sort_prop'] => $sortType]);
            }
        } else {
            $query->orderBy(['g.created_at' => SORT_DESC]);
        }
        if (isset($search['status']) && $search['status'] != -1) {
            if ($search['status'] != '' && ($search['status'] == 0 || $search['status'] == 1)) {
                // 上下架状态
                $query->andWhere(['g.status' => $search['status']]);
            } elseif ($search['status'] == 2) {
                // 售罄
                $query->andWhere(['g.goods_stock' => 0]);
            }
        }

        // 分类搜索
        if (isset($search['cats']) && $search['cats']) {
            $query = $this->addCatWhere($search['cats'], $query);
        }

        // 日期搜索
        if (isset($search['date_start']) && $search['date_start'] && isset($search['date_end']) && $search['date_end']) {
            $query->andWhere(['>=', 'g.created_at', $search['date_start']]);
            $query->andWhere(['<=', 'g.created_at', $search['date_end']]);
        }

        $query = $this->setQuery($query);
        if (\Yii::$app->request->post('flag') == 'EXPORT') {
            return $query;
        }

        $newQuery = clone $query;
        $goodsCount = $newQuery->count();
        $list = $query->with('goodsWarehouse.cats', 'attr')->page($pagination)->all();
        $newList = $this->handleData($list);

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'list' => $newList,
                'pagination' => $pagination,
                'goods_count' => $goodsCount
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
        $query->andWhere(['g.goods_warehouse_id' => $goodsWarehouseId]);

        return $query;
    }

    /**
     * 提供额外参数
     * @param $query
     * @return BaseActiveQuery mixed
     */
    protected function setQuery($query)
    {
        return $query;
    }

    /**
     * 处理商品列表数据
     * @param $list
     * @return array
     */
    protected function handleData($list)
    {
        $newList = [];
        /** @var Goods $item */
        foreach ($list as $item) {
            $newItem = ArrayHelper::toArray($item);
            $newItem['goodsWarehouse'] = isset($item->goodsWarehouse) ? ArrayHelper::toArray($item->goodsWarehouse) : [];
            $newItem['cats'] = isset($item->goodsWarehouse->cats) ? ArrayHelper::toArray($item->goodsWarehouse->cats) : [];
            $newItem['name'] = isset($item->goodsWarehouse->name) ? $item->goodsWarehouse->name : '';
            $goodsStock = 0;
            foreach ($item->attr as $aItem) {
                $goodsStock += $aItem->stock;
            }
            $newItem['goods_stock'] = $goodsStock;
	    $newItem['sold'] = $item->getSales();
            $newItem = array_merge($newItem, $this->handleGoodsData($item));
            $newList[] = $newItem;
        }

        return $newList;
    }

    /**
     * 如果只有小部分参数不同，可重写此接口
     * @param Goods $goods
     * @return array
     */
    protected function handleGoodsData($goods)
    {
        return [];
    }
}