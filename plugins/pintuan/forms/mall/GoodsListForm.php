<?php
/**
 * link: http://www.67930603.top/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: Alpha
 */

namespace app\plugins\pintuan\forms\mall;


use app\forms\mall\goods\BaseGoodsList;
use app\models\BaseQuery\BaseActiveQuery;
use app\plugins\pintuan\models\Goods;
use yii\helpers\ArrayHelper;

class GoodsListForm extends BaseGoodsList
{
    public $goodsModel = 'app\plugins\pintuan\models\Goods';

    /**
     * @param BaseActiveQuery $query
     * @return mixed
     */
    protected function setQuery($query)
    {
        $query->andWhere([
            'g.sign' => \Yii::$app->plugin->getCurrentPlugin()->getName(),
        ])->with('pintuanGoods', 'groups.attr');

        return $query;
    }

    /**
     * @param Goods $goods
     * @return array
     */
    protected function handleGoodsData($goods)
    {
        $newItem = [];
        $newItem['pintuanGoods'] = isset($goods->pintuanGoods) ? ArrayHelper::toArray($goods->pintuanGoods) : [];
        $newItem['groups'] = isset($goods->groups) ? ArrayHelper::toArray($goods->groups) : [];
        $newItem['name'] = $goods->goodsWarehouse ? $goods->goodsWarehouse->name : '';
        $newItem['cover_pic'] = $goods->goodsWarehouse ? $goods->goodsWarehouse->cover_pic : '';
        $newItem['num_count'] = 0;
        $newItem['status'] = $goods->status;
        $newItem['is_sell_well'] = $goods->pintuanGoods ? $goods->pintuanGoods->is_sell_well : 0;

        foreach ($goods->attr as $attr) {
            $newItem['num_count'] += $attr->stock;
        }

        foreach ($goods->groups as $gItem) {
            foreach ($gItem->attr as $aItem) {
                $newItem['num_count'] += $aItem->pintuan_stock;
            }
        }

        return $newItem;
    }
}