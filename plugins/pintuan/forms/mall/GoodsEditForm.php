<?php
/**
 * link: http://www.67930603.top/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: Alpha
 */

namespace app\plugins\pintuan\forms\mall;


use app\core\response\ApiCode;
use app\forms\mall\goods\BaseGoodsEdit;
use app\plugins\pintuan\forms\common\CommonGoods;
use app\plugins\pintuan\jobs\PintuanGoodsJob;
use app\plugins\pintuan\models\PintuanGoods;
use app\plugins\pintuan\models\PintuanGoodsAttr;
use app\plugins\pintuan\models\PintuanGoodsGroups;
use app\plugins\pintuan\Plugin;
use yii\db\Exception;

/**
 * Class GoodsEditForm
 * @package app\plugins\pintuan\forms\mall
 */
class GoodsEditForm extends BaseGoodsEdit
{
    public $mall;
    public $is_alone_buy;
    public $end_time;
    public $groups_restrictions;
    public $is_sell_well;

    public function rules()
    {
        return array_merge(parent::rules(), [
            [['is_alone_buy', 'end_time', 'groups_restrictions', 'is_sell_well'], 'required'],
            [['mall'], 'safe'],
        ]);
    }

    public function attributes()
    {
        return array_merge(parent::attributes(), [
            'is_alone_buy' => '是否允许单独购买',
            'end_time' => '拼团结束时间',
            'groups_restrictions' => '拼团次数限制',
            'is_sell_well' => '是否热销',
        ]);
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        if (!$this->mall) {
            $this->mall = \Yii::$app->mall;
        }
        $t = \Yii::$app->db->beginTransaction();
        try {
            $this->setGoods();
            // 没有拼团组的商品 不能上架
            $goodsCount = PintuanGoodsGroups::find()->where(['goods_id' => $this->goods->id, 'is_delete' => 0])->count();
            if (!$goodsCount) {
                $this->goods->status = 0;
                $res = $this->goods->save();
                if (!$res) {
                    throw new \Exception($this->getErrorMsg($this->goods));
                }
            }

            $this->setAttr();
            $this->setGoodsService();
            $this->setCard();
            $this->pintuan();
            $this->setListener();

            $t->commit();
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功'
            ];
        } catch (Exception $exception) {
            $t->rollBack();
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage()
            ];
        }
    }

    protected function setGoodsSign()
    {
        return (new Plugin())->getName();
    }

    /**
     * @throws \Exception
     */
    private function pintuan()
    {
        $model = PintuanGoods::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'goods_id' => $this->goods->id,
        ])->one();

        if (!$model) {
            $model = new PintuanGoods();
            $model->mall_id = \Yii::$app->mall->id;
            $model->goods_id = $this->goods->id;
        }


        $attrList = $this->goods->resetAttr($this->attrGroups);
        $pintuanAttr = PintuanGoodsAttr::find()->where([
            'goods_id' => $this->goods->id,
            'is_delete' => 0,
        ])
            ->with('goodsAttr')
            ->groupBy('goods_attr_id')
            ->asArray()
            ->all();
        // 使用规格情况下 拼团商品有规格新增|减少操作,阶梯团全部组则失效
        if ($this->use_attr) {
            if (count($attrList) == count($pintuanAttr)) {
                foreach ($pintuanAttr as $item) {
                    if (!isset($attrList[$item['goodsAttr']['sign_id']])) {
                        $commonGoods = new CommonGoods();
                        $commonGoods->goods = $this->goods;
                        $commonGoods->destroyPintuanGroups();
                        break;
                    }
                }
            } else {
                if (count($pintuanAttr) > 0) {
                    $commonGoods = new CommonGoods();
                    $commonGoods->goods = $this->goods;
                    $commonGoods->destroyPintuanGroups();
                }
            }
        } else {
            // 商品默认规格编辑，规格ID是不会变的，可以通过ID判断
            if (count($this->goods->attr) != 1 || count($pintuanAttr) != 1
                || $this->goods->attr[0]['id'] != $pintuanAttr[0]['goodsAttr']['id']) {
                $commonGoods = new CommonGoods();
                $commonGoods->goods = $this->goods;
                $commonGoods->destroyPintuanGroups();
            }
        }

        $time = strtotime($this->end_time) - time();
        if ($time > 0) {
            $queueId = \Yii::$app->queue->delay($time)->push(new PintuanGoodsJob([
                'goodsId' => $this->goods->id
            ]));
        } else {
            $queueId = \Yii::$app->queue->delay(0)->push(new PintuanGoodsJob([
                'goodsId' => $this->goods->id
            ]));
        }

        $model->is_alone_buy = $this->is_alone_buy;
        $model->end_time = $this->end_time;
        $model->groups_restrictions = $this->groups_restrictions;
        $model->is_sell_well = $this->is_sell_well;
        $res = $model->save();

        if (!$res) {
            throw new \Exception($this->getErrorMsg($model));
        }
    }
}
