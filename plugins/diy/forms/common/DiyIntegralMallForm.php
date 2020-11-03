<?php
/**
 * link: http://www.67930603.top/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: Alpha
 */

namespace app\plugins\diy\forms\common;


use app\forms\api\goods\ApiGoods;
use app\models\Model;
use app\plugins\integral_mall\forms\common\CouponListForm;
use app\plugins\integral_mall\models\Goods;

class DiyIntegralMallForm extends Model
{
    public function getGoodsIds($data)
    {
        $goodsIds = [];
        // 显示商品
        if ($data['showGoods']) {
            foreach ($data['list'] as $item) {
                $goodsIds[] = $item['id'];
            }
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
        ])->with('goodsWarehouse', 'integralMallGoods')->all();

        $newList = [];
        /** @var Goods $item */
        foreach ($list as $item) {
            $apiGoods = ApiGoods::getCommon();
            $apiGoods->goods = $item;
            $apiGoods->isSales = 0;
            $arr = $apiGoods->getDetail();
            $newList[] = $arr;
        }

        return $newList;
    }

    public function getNewGoods($data, $goods)
    {
        $newGoodsList = [];
        foreach ($data['list'] as $item) {
            foreach ($goods as $gItem) {
                if ($item['id'] == $gItem['id']) {
                    $newGoodsList[] = $gItem;
                    break;
                }
            }
        }
        $data['list'] = $newGoodsList;

        if ($data['showCoupon']) {
            $common = new CouponListForm();
            $common->page = 10;
            $res = $common->getCouponList();

            $newList = [];
            foreach ($res['list'] as $item) {
                $arr = $item['coupon'];
                $arr['page_url'] = '/plugins/integral_mall/coupon/coupon?id=' . $item['id'];
                $arr['is_receive'] = '0';
                $newList[] = $arr;
            }

            $data['coupon_list'] = $newList;
        }

        return $data;
    }
}
