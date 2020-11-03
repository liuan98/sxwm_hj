<?php
/**
 * link: http://www.67930603.top/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: Alpha
 */

namespace app\plugins\diy\forms\common;


use app\forms\api\goods\ApiGoods;
use app\models\Goods;
use app\models\Model;
use app\plugins\bargain\models\BargainGoods;

class DiyBargainForm extends Model
{
    public function getGoodsIds($data)
    {
        $goodsIds = [];
        foreach ($data['list'] as $item) {
            $goodsIds[] = $item['id'];
        }

        return $goodsIds;
    }

    public function getGoodsById($goodsIds)
    {
        if (!$goodsIds) {
            return [];
        }

        $list = Goods::find()->where([
            'id' => $goodsIds,
            'status' => 1,
            'is_delete' => 0
        ])->with('goodsWarehouse')->all();

        $newList = [];
        /** @var Goods $item */
        foreach ($list as $item) {
            // TODO 可以优化 不要循环查询
            $bargainGoods = BargainGoods::findOne(['goods_id' => $item['id']]);
            if (!$bargainGoods) {
                throw new \Exception('砍价商品不存在,商品ID:' . $item['id']);
            }
            $apiGoods = ApiGoods::getCommon();
            $apiGoods->goods = $item;
            $apiGoods->isSales = 0;
            $arr = $apiGoods->getDetail();
            $arr['price'] = $bargainGoods->min_price;
            $arr['price_content'] = $bargainGoods->min_price ? '￥' . $bargainGoods->min_price : '免费';
            $arr['start_time'] = $bargainGoods->begin_time;
            $arr['end_time'] = $bargainGoods->end_time;
            $newList[] = $arr;
        }

        return $newList;
    }

    public function getNewGoods($data, $goods)
    {
        $newArr = [];
        foreach ($data['list'] as $item) {
            foreach ($goods as $gItem) {
                if ($item['id'] == $gItem['id']) {
                    $newArr[]  = $gItem;
                    break;
                }
            }
        }
        $data['list'] = $newArr;

        return $data;
    }
}
