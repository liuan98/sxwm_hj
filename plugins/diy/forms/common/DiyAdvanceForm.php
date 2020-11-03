<?php
/**
 * link: http://www.67930603.top/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: Alpha
 */

namespace app\plugins\diy\forms\common;


use app\forms\common\goods\CommonGoodsMember;
use app\models\Model;
use app\plugins\advance\models\AdvanceGoods;
use app\plugins\pintuan\models\Goods;
use yii\helpers\ArrayHelper;

class DiyAdvanceForm extends Model
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
        $list = AdvanceGoods::find()->where([
            'goods_id' => $goodsIds, 'is_delete' => 0
        ])->with(['attr.attr' ,'goods' => function ($query){
            $query->where(['is_delete' => 0])->with(['goodsWarehouse.cats']);
        }])->all();
        $permission = \Yii::$app->branch->childPermission(\Yii::$app->mall->user->adminInfo);
        try {
            $plugin = \Yii::$app->plugin->getPlugin('vip_card');
        } catch (\Exception $e) {
            $plugin = false;
        }
        if ($plugin) {
            $setting = $plugin->getSetting();
        } else {
            $setting['rules'] = [];
        }
        $newList = [];
        foreach ($list as $item) {
            $newItem = ArrayHelper::toArray($item);
            $newItem['attr'] = ArrayHelper::toArray($item['attr']);
            $newItem['goods'] = ArrayHelper::toArray($item['goods']);
            $newItem['goods']['goodsWarehouse'] = ArrayHelper::toArray($item['goods']['goodsWarehouse']);
            if ($item['goods']['use_attr'] == 1) {
                $minDeposit = $item['attr'][0]['attr']['deposit'];
                $minSwellDeposit = $item['attr'][0]['attr']['swell_deposit'];
                foreach ($item['attr'] as $k => $v) {
                    if ($minDeposit < $v['attr']['deposit']) {
                        $minDeposit = $v['attr']['deposit'];
                        $minSwellDeposit = $v['attr']['swell_deposit'];
                    }
                }
                $newItem['deposit'] = round($minDeposit,2);
                $newItem['swell_deposit'] = round($minSwellDeposit,2);
            }
            $newItem['page_url'] = '/plugins/advance/detail/detail?id=' . $item['goods_id'];
            $newItem['mch'] = $item['goods']['mch_id'];
            $newItem['is_negotiable'] = "0";
            $newItem['is_level'] = $item['goods']['is_level'];
            $newItem['sign'] = $item['goods']['sign'];
            $newItem['video_url'] = $item['goods']['goodsWarehouse']['video_url'];
            if (in_array('vip_card', $permission) ) {
                $newItem['vip_card_appoint'] = $plugin->getAppoint($item['goods']);
            }
            $newItem['level_price'] = CommonGoodsMember::getCommon()->getGoodsMemberPrice($item['goods']);
            $newList[] = $newItem;
        }
        unset($item);

        return $newList;
    }

    public function getNewGoods($data, $goods)
    {
        $newArr = [];
        foreach ($data['list'] as &$item) {
            foreach ($goods as $gItem) {
                //商品下架
                if ($gItem['goods']['status'] == 0) {
                    continue;
                }
                //已过定金时间
                if (strtotime($gItem['end_prepayment_at']) < time()) {
                    continue;
                }
                if ($item['id'] == $gItem['goods_id']) {
                    $newArr[] = $gItem;
                    break;
                }
            }
        }

        $data['list'] = $newArr;

        return $data;
    }
}
