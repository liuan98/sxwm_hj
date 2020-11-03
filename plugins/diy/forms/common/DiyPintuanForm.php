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
use app\plugins\pintuan\forms\common\CommonGoods;
use app\plugins\pintuan\models\Goods;
use app\plugins\pintuan\models\PintuanGoods;

class DiyPintuanForm extends Model
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
        $goodsIds = PintuanGoods::find()->where([
            'goods_id' => $goodsIds,
        ])->andWhere(['>', 'end_time', date('Y-m-d H:i:s')])
            ->select('goods_id');

        $list = Goods::find()->where([
            'id' => $goodsIds,
            'status' => 1,
            'is_delete' => 0
        ])
            ->with('goodsWarehouse', 'groups.attr')->all();
        $newList = [];
        /** @var Goods $item */
        foreach ($list as $item) {
            if (count($item->groups) > 0) {
                $apiGoods = ApiGoods::getCommon();
                $apiGoods->goods = $item;
                $apiGoods->isSales = 0;
                $arr = $apiGoods->getDetail();
                $arr['people_num'] = 0;
                $arr['pintuan_price'] = 0;
                if ($item->groups) {
                    $arr['people_num'] = $item->groups[0]['people_num'];
                    $arr['pintuan_price'] = $item->groups[0]['attr'][0]['pintuan_price'];
                    $arr['price_content'] = '￥' . $item->groups[0]['attr'][0]['pintuan_price'];
                }
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
                try {
                    if ($item['id'] == $gItem['id']) {
                        /** @var Goods $ptGoods */
                        $ptGoods = Goods::find()->where([
                            'id' => $gItem['id']
                        ])->with('groups.attr', 'attr')->one();
                        $goodsStock = 0;
                        foreach ($ptGoods->attr as $aItem) {
                            $goodsStock += $aItem->stock;
                        }
                        foreach ($ptGoods->groups as $groupItem) {
                            foreach ($groupItem->attr as $aItem) {
                                $goodsStock += $aItem->pintuan_stock;
                            }
                        }
                        $dItem['goods_stock'] = $goodsStock;
                        $newArr[] = $gItem;
                        break;
                    }
                }catch (\Exception $exception) {

                }
            }
        }

        $data['list'] = $newArr;

        return $data;
    }
}
