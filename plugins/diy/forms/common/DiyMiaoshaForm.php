<?php
/**
 * link: http://www.67930603.top/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: Alpha
 */

namespace app\plugins\diy\forms\common;


use app\forms\api\goods\ApiGoods;
use app\forms\common\goods\CommonGoodsMember;
use app\models\Model;
use app\plugins\miaosha\forms\common\SettingForm;
use app\plugins\miaosha\models\Goods;
use app\plugins\miaosha\models\MiaoshaGoods;


class DiyMiaoshaForm extends Model
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
        ])->with('goodsWarehouse', 'miaoshaGoods')->all();
        $newList = [];
        $setting = (new SettingForm())->search();
        /** @var Goods $item */
        foreach ($list as $item) {
            if (in_array($item->miaoshaGoods->open_time, $setting['open_time'])) {
                $apiGoods = ApiGoods::getCommon();
                $apiGoods->goods = $item;
                $apiGoods->isSales = 0;
                $arr = $apiGoods->getDetail();
                $arr['start_time'] = $item->miaoshaGoods->open_date . ' ' . $item->miaoshaGoods->open_time . ':00:00';
                $arr['end_time'] = $item->miaoshaGoods->open_date . ' ' . $item->miaoshaGoods->open_time . ':59:59';
                $newList[] = $arr;
            }
        }

        return $newList;
    }

    public function getNewGoods($data, $goods)
    {
        $newArr = [];
        foreach ($data['list'] as &$item) {
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
