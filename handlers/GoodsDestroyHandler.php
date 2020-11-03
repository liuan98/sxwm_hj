<?php
/**
 * @copyright (c)天幕网络
 * @author Lu Wei
 * @link http://www.67930603.top/
 * Created by IntelliJ IDEA
 * Date Time: 2019/1/23 16:31
 */


namespace app\handlers;


use app\events\GoodsEvent;
use app\models\Goods;
use app\models\GoodsAttr;
use app\models\GoodsCardRelation;
use app\models\GoodsCatRelation;
use app\models\GoodsServiceRelation;
use app\models\Model;
use app\plugins\mch\models\MchGoods;

class GoodsDestroyHandler extends HandlerBase
{
    /**
     * 事件处理
     */
    public function register()
    {
        \Yii::$app->on(Goods::EVENT_DESTROY, function ($event) {
            /** @var GoodsEvent $event */
            // 删除服务关联
            GoodsServiceRelation::updateAll([
                'is_delete' => 1,
            ], [
                'goods_id' => $event->goods->id,
                'is_delete' => 0,
            ]);

            // 删除商品关联
            $res = GoodsAttr::updateAll([
                'is_delete' => 1,
            ], [
                'goods_id' => $event->goods->id,
                'is_delete' => 0,
            ]);

            // 删除卡券关联
            GoodsCardRelation::updateAll([
                'is_delete' => 1,
            ], [
                'goods_id' => $event->goods->id,
                'is_delete' => 0,
            ]);

            $attrIds = [];
            foreach ($event->goods->attr as $item) {
                $attrIds[] = $item->id;
            }

            $res = Goods::updateAll([
                'is_delete' => 1,
            ], [
                'goods_warehouse_id' => $event->goods->goods_warehouse_id,
                'mch_id' => 0,
            ]);

            // TODO 删除多商户关联商品 应该写在多商户插件中
            if ($event->goods->sign == 'mch') {
                $mchGoods = MchGoods::findOne(['goods_id' => $event->goods->id]);
                if (!$mchGoods) {
                    throw new \Exception('商品不存在');
                }
                $mchGoods->is_delete = 1;
                $res = $mchGoods->save();
                if (!$res) {
                    throw new \Exception((new Model())->getErrorMsg($mchGoods));
                }

                // 删除分类关联
                GoodsCatRelation::updateAll([
                    'is_delete' => 1
                ], [
                    'goods_warehouse_id' => $event->goods->goods_warehouse_id,
                    'is_delete' => 0
                ]);
            }
        });
    }
}
