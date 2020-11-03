<?php
/**
 * link: http://www.67930603.top/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: Alpha
 */

namespace app\plugins\miaosha\forms\mall;


use app\core\response\ApiCode;
use app\forms\mall\goods\BaseGoodsEdit;
use app\models\GoodsAttr;
use app\models\User;
use app\plugins\miaosha\jobs\MiaoshaActivityJob;
use app\plugins\miaosha\models\MiaoshaGoods;
use app\plugins\miaosha\Plugin;

class GoodsEditForm extends BaseGoodsEdit
{
    public $open_date;
    public $open_time;
    public $buy_limit;
    public $virtual_miaosha_num;
    public $price;

    private $arr = [];


    public function rules()
    {
        return array_merge(parent::rules(), [
            [['open_date', 'open_time', 'price'], 'safe'],
            [['buy_limit', 'virtual_miaosha_num'], 'integer'],
        ]);
    }

    public function setGoodsSign()
    {
        return (new Plugin())->getName();
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        try {
            $this->checkData();
            $user = User::findOne(\Yii::$app->user->id);
            $dateArr = $this->diffTime($this->open_date[0], $this->open_date[1]);
            foreach ($dateArr as $item) {
                foreach ($this->open_time as $tItem) {
                    $date = $item . ' ' . $tItem . ':59:59';
                    if (strtotime($date) >= time()) {
                        $this->arr['open_time'] = $tItem;
                        $this->arr['open_date'] = $item;
                        $queueId = \Yii::$app->queue->delay(0)->push(new MiaoshaActivityJob([
                            'open_date' => $item,
                            'open_time' => $tItem,
                            'mall' => \Yii::$app->mall,
                            'miaoshaGoods' => $this,
                            'user' => $user
                        ]));
//                        $this->executeSave();
                    }
                }
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功'
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
                'error' => [
                    'list' => $e->getLine()
                ]
            ];
        }
    }

    private function checkData()
    {
        if (!$this->goods_warehouse_id) {
            throw new \Exception('请先摘取商城商品');
        }
        if (!count($this->open_date)) {
            throw new \Exception('请选择秒杀开放日期');
        }

        if (!count($this->open_time)) {
            throw new \Exception('请选择秒杀开放时间段');
        }

        if ($this->virtual_miaosha_num < 0) {
            throw new \Exception('已秒杀数不能小于0');
        }

        if ($this->use_attr == 1) {
            $goodsStock = 0;
            foreach ($this->attr as $item) {
                if (!isset($item['price']) || $item['price'] < 0) {
                    throw new \Exception('请填写规格价格');
                }
                $goodsStock += $item['stock'];
            }
            if ($goodsStock === '') {
                throw new \Exception('请填写规格库存');
            }
        } else {
            if ($this->price < 0 || $this->price === '') {
                throw new \Exception('请填写秒杀价格');
            }
        }
    }

    private function diffTime($date1, $date2)
    {
        $time1 = strtotime($date1);
        $time2 = strtotime($date2);

        $diff = intval(($time2 - $time1) / 86400);

        $arr = [$date1];
        for ($i = 1; $i <= $diff; $i++) {
            $arr[] = date('Y-m-d', strtotime($date1) + 86400 * $i);
        }

        return $arr;
    }

    public function executeSave()
    {
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            $miaoshaGoods = MiaoshaGoods::findOne([
                'open_date' => $this->arr['open_date'],
                'open_time' => $this->arr['open_time'],
                'goods_warehouse_id' => $this->goods_warehouse_id,
                'is_delete' => 0,
            ]);
            if ($miaoshaGoods) {
                $this->id = $miaoshaGoods->goods_id;
            }

            $this->setGoods();
            $this->setAttr();
            $this->setCard();
            $this->setGoodsService();
            $this->setListener();

            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            \Yii::warning($e->getMessage());
        }
    }

    public function setExtraGoods($goods)
    {
        $miaoshaGoods = MiaoshaGoods::findOne([
            'goods_id' => $goods->id,
            'is_delete' => 0,
            'mall_id' => \Yii::$app->mall->id,
        ]);
        if (!$miaoshaGoods) {
            $miaoshaGoods = new MiaoshaGoods();
            $miaoshaGoods->mall_id = \Yii::$app->mall->id;
            $miaoshaGoods->goods_id = $goods->id;
            $miaoshaGoods->open_time = $this->arr['open_time'];
            $miaoshaGoods->open_date = $this->arr['open_date'];
        }
        $miaoshaGoods->buy_limit = $this->buy_limit;
        $miaoshaGoods->virtual_miaosha_num = $this->virtual_miaosha_num;
        $miaoshaGoods->goods_warehouse_id = $goods->goods_warehouse_id;
        $res = $miaoshaGoods->save();

        if (!$res) {
            \Yii::error($this->getErrorMsg($miaoshaGoods));
        }
    }

    /**
     * @param GoodsAttr $goodsAttr
     * @param $newAttr
     * @throws \Exception
     */
    protected function setExtraAttr($goodsAttr, $newAttr)
    {
        if (!$this->use_attr) {
            $goodsAttr->price = $this->price;
            $res = $goodsAttr->save();
            if (!$res) {
                throw new \Exception($this->getErrorMsg($goodsAttr));
            }
        }
    }
}
