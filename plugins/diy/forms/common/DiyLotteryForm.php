<?php
/**
 * link: http://www.67930603.top/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: Alpha
 */

namespace app\plugins\diy\forms\common;


use app\forms\api\goods\ApiGoods;
use app\models\Model;
use app\plugins\lottery\models\Goods;

class DiyLotteryForm extends Model
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
            'is_delete' => 0,
        ])->with('goodsWarehouse', 'lotteryGoods')->all();

        $newList = [];
        /** @var Goods $item */
        foreach ($list as $item) {
            if ($item->lotteryGoods->end_at <= mysql_timestamp()) {
                continue;
            }
            $apiGoods = ApiGoods::getCommon();
            $apiGoods->goods = $item;
            $apiGoods->isSales = 0;
            $arr = $apiGoods->getDetail();
            $arr['start_time'] = $item->lotteryGoods->start_at;
            $arr['end_time'] = $item->lotteryGoods->end_at;
            $arr['page_url'] = '/plugins/lottery/goods/goods?lottery_id=' . $item->lotteryGoods->id;
            $arr['is_level'] = 0;
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
                    $newArr[] = $gItem;
                    break;
                }
            }
        }

        $data['list'] = $newArr;

        return $data;
    }
}
