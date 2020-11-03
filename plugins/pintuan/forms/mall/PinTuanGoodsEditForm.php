<?php
/**
 * link: http://www.67930603.top/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: Alpha
 */

namespace app\plugins\pintuan\forms\mall;


use app\core\response\ApiCode;
use app\forms\mall\goods\GoodsShareForm;
use app\models\Goods;
use app\models\Model;
use app\plugins\pintuan\forms\common\CommonGoods;
use app\plugins\pintuan\models\PintuanGoodsAttr;
use app\plugins\pintuan\models\PintuanGoodsGroups;
use app\plugins\pintuan\models\PintuanGoodsMemberPrice;
use app\plugins\pintuan\models\PintuanGoodsShare;
use app\plugins\pintuan\Plugin;

/**
 * Class GoodsEditForm
 * @package app\plugins\pintuan\forms\mall
 * @property \app\plugins\pintuan\models\Goods $goods
 */
class PinTuanGoodsEditForm extends Model
{
    public $id;
    public $use_attr;
    public $individual_share;
    public $attr_setting_type;
    public $is_level;
    public $is_level_alone;
    public $share_type;
    public $data;

    private $goods;

    public function rules()
    {
        return [
            [['id'], 'required'],
            [['individual_share', 'attr_setting_type', 'is_level',
                'is_level_alone', 'share_type', 'use_attr'], 'integer'],
            [['data'], 'safe'],
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $t = \Yii::$app->db->beginTransaction();
        try {
            $this->checkData();

            /** @var Goods $goods */
            $goods = Goods::find()->where([
                'mall_id' => \Yii::$app->mall->id,
                'id' => $this->id,
                'is_delete' => 0,
                'sign' => (new Plugin())->getName()
            ])->one();

            if (!$goods) {
                throw new \Exception('商品不存在');
            }
            $this->goods = $goods;

            $res = PintuanGoodsGroups::updateAll([
                'is_delete' => 1,
            ], [
                'goods_id' => $this->goods->id,
                'is_delete' => 0
            ]);

            foreach ($this->data as $item) {
                $defaultMemberPrice = [];
                if (!$this->use_attr) {
                    $defaultMemberPrice = $this->transformDefaultMemberPrice($item['defaultMemberPrice']);
                }
                $groups = $this->pintuanGoodsGroups($item);
                $this->pintuanGoodsShareLevel($groups, $item['shareLevelList'], 0, 0);
                foreach ($item['attr'] as $attr) {
                    $pintuanAttr = $this->pintuanGoodsAttr($attr, $groups);
                    $this->pintuanGoodsShareLevel($groups, $attr['shareLevelList'], $attr['id'], $pintuanAttr->id);
                    $this->pintuanGoodsMemberPrice($attr, $groups, $pintuanAttr, $defaultMemberPrice);
                }
            }

            // 删除
            if (count($this->data) == 0) {
                $commonGoods = new CommonGoods();
                $commonGoods->goods = $this->goods;
                $commonGoods->destroyPintuanGroups();
            }

            $t->commit();
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功'
            ];
        } catch (\Exception $exception) {
            $t->rollBack();
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage()
            ];
        }
    }

    /**
     * 拼团商品组
     * @param $item
     * @return PintuanGoodsGroups|array|null|\yii\db\ActiveRecord
     * @throws \Exception
     */
    private function pintuanGoodsGroups($item)
    {
        $id = isset($item['id']) ? $item['id'] : 0;
        $pintuanGoodsGroups = PintuanGoodsGroups::find()->where([
            'id' => $id,
        ])->one();
        if (!$pintuanGoodsGroups) {
            $pintuanGoodsGroups = new PintuanGoodsGroups();
            $pintuanGoodsGroups->goods_id = $this->goods->id;
        }
        $pintuanGoodsGroups->people_num = $item['people_num'];
        $pintuanGoodsGroups->group_num = $item['group_num'];
        $pintuanGoodsGroups->preferential_price = $item['preferential_price'];
        $pintuanGoodsGroups->pintuan_time = $item['pintuan_time'];
        $pintuanGoodsGroups->is_delete = 0;
        $res = $pintuanGoodsGroups->save();
        if (!$res) {
            throw new \Exception($this->getErrorMsg($pintuanGoodsGroups));
        }

        return $pintuanGoodsGroups;
    }

    /**
     * 拼团商品规格
     * @param $attr
     * @param $groups
     * @return PintuanGoodsAttr|array|null|\yii\db\ActiveRecord
     * @throws \Exception
     */
    private function pintuanGoodsAttr($attr, $groups)
    {
        $pintuanGoodsAttr = PintuanGoodsAttr::find()->where([
            'pintuan_goods_groups_id' => $groups->id,
            'goods_attr_id' => $attr['goodsAttr']['id'],
            'goods_id' => $this->goods->id,
            'is_delete' => 0
        ])->one();
        if (!$pintuanGoodsAttr) {
            $pintuanGoodsAttr = new PintuanGoodsAttr();
            $pintuanGoodsAttr->goods_id = $this->goods->id;
            $pintuanGoodsAttr->goods_attr_id = $attr['goodsAttr']['id'];
            $pintuanGoodsAttr->pintuan_goods_groups_id = $groups->id;
        }

        $pintuanGoodsAttr->pintuan_price = $attr['pintuan_price'];
        $pintuanGoodsAttr->pintuan_stock = $attr['pintuan_stock'];
        $res = $pintuanGoodsAttr->save();
        if (!$res) {
            throw new \Exception($this->getErrorMsg($pintuanGoodsAttr));
        }

        return $pintuanGoodsAttr;
    }

    /**
     * 拼团商品分销等级
     * @param PintuanGoodsGroups $group
     * @param array $shareLevelList
     * @param integer $goodsAttrId
     * @param integer $pintuanGoodsAttrId
     * @throws \Exception
     */
    private function pintuanGoodsShareLevel($group, $shareLevelList, $goodsAttrId, $pintuanGoodsAttrId)
    {
        $list = [];
        $res = PintuanGoodsShare::find()
            ->where([
                'goods_id' => $this->goods->id, 'goods_attr_id' => $goodsAttrId, 'is_delete' => 0,
                'pintuan_goods_groups_id' => $group->id, 'pintuan_goods_attr_id' => $pintuanGoodsAttrId
            ])
            ->all();
        /* @var PintuanGoodsShare[] $res */
        foreach ($res as $item) {
            $item->is_delete = 1;
            $list[$item->level] = $item;
        }
        foreach ($shareLevelList as $shareLevel) {
            if (!isset($list[$shareLevel['level']])) {
                $pintuanGoodsShare = new PintuanGoodsShare();
                $pintuanGoodsShare->goods_id = $this->goods->id;
                $pintuanGoodsShare->goods_attr_id = $goodsAttrId;
                $pintuanGoodsShare->pintuan_goods_groups_id = $group->id;
                $pintuanGoodsShare->pintuan_goods_attr_id = $pintuanGoodsAttrId;
            } else {
                $pintuanGoodsShare = $list[$shareLevel['level']];
            }
            // 校验分销金额是否合法
            $model = new GoodsShareForm();
            $model->attributes = $shareLevel;
            if (!$model->validate()) {
                throw new \Exception($this->getErrorMsg($model));
            }
            $pintuanGoodsShare->is_delete = 0;
            $pintuanGoodsShare->share_commission_first = $model->share_commission_first;
            $pintuanGoodsShare->share_commission_second = $model->share_commission_second;
            $pintuanGoodsShare->share_commission_third = $model->share_commission_third;
            $pintuanGoodsShare->level = $model->level;
            $list[$shareLevel['level']] = $pintuanGoodsShare;
        }
        foreach ($list as $item) {
            if (!$item->save()) {
                throw new \Exception($this->getErrorMsg($item));
            }
        }
    }

    /**
     * 拼团商品会员价
     * @param $attr
     * @param $groups
     * @param $pintuanAttr
     * @param $defaultMemberPrice
     * @throws \Exception
     */
    private function pintuanGoodsMemberPrice($attr, $groups, $pintuanAttr, $defaultMemberPrice)
    {
        foreach ($attr['member_price'] as $lKey => $lValue) {
            // 例如键值为 `level1` 去除`level`后就是会员等级
            $memberLevel = (int)substr($lKey, 5);
            $pintuanGoodsMemberPrice = PintuanGoodsMemberPrice::find()->where([
                'level' => $memberLevel,
                'is_delete' => 0,
                'pintuan_goods_attr_id' => $pintuanAttr->id
            ])->one();
            if (!$pintuanGoodsMemberPrice) {
                $pintuanGoodsMemberPrice = new PintuanGoodsMemberPrice();
                $pintuanGoodsMemberPrice->level = $memberLevel;
                $pintuanGoodsMemberPrice->goods_attr_id = $attr['id'];
                $pintuanGoodsMemberPrice->pintuan_goods_groups_id = $groups->id;
                $pintuanGoodsMemberPrice->pintuan_goods_attr_id = $pintuanAttr->id;
                $pintuanGoodsMemberPrice->goods_id = $this->goods->id;
            }
            if ($this->use_attr) {
                $pintuanGoodsMemberPrice->price = $lValue;
            } else {
                // 默认规格会员价
                $pintuanGoodsMemberPrice->price = isset($defaultMemberPrice[$lKey]) ? $defaultMemberPrice[$lKey] : 0;
            }
            $res = $pintuanGoodsMemberPrice->save();
            if (!$res) {
                throw new \Exception($this->getErrorMsg($pintuanGoodsMemberPrice));
            }
        }
    }

    private function transformDefaultMemberPrice($members)
    {
        $newArr = [];
        foreach ($members as $item) {
            $newArr['level' . $item['level']] = $item['value'];
        }

        return $newArr;
    }

    private function checkData()
    {
        foreach ($this->data as $key => &$item) {
            $item['people_num'] = isset($item['people_num']) ? (int)$item['people_num'] : 2;
            $item['preferential_price'] = isset($item['preferential_price']) ? (float)$item['preferential_price'] : 0;
            $item['pintuan_time'] = isset($item['pintuan_time']) ? (int)$item['pintuan_time'] : 1;
            $item['group_num'] = isset($item['group_num']) ? (int)$item['group_num'] : 0;

            if ($item['people_num'] < 2) {
                throw new \Exception('拼团人数最少2人');
            }
            if ($item['preferential_price'] < 0) {
                throw new \Exception('团长优惠不能小于0');
            }
            if ($item['pintuan_time'] < 1) {
                throw new \Exception('拼团时间不能小于1小时');
            }
            if ($item['group_num'] < 0) {
                throw new \Exception('团长数量不能小于0');
            }

            foreach ($item['attr'] as $aKey => &$aItem) {
                $aItem['pintuan_price'] = isset($aItem['pintuan_price']) ? (float)$aItem['pintuan_price'] : 0;
                if ($aItem['pintuan_price'] < 0) {
                    throw new \Exception('拼团价不能小于0');
                }

                if ($aItem['pintuan_stock'] === '') {
                    throw new \Exception('拼团库存不能为空');
                }
            }
            unset($aItem);
        }
        unset($item);
    }
}
