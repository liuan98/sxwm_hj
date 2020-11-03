<?php
/**
 * link: http://www.67930603.top/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: Alpha
 */

namespace app\plugins\pintuan\forms\mall;


use app\core\response\ApiCode;
use app\forms\common\CommonMallMember;
use app\models\GoodsShare;
use app\models\Model;
use app\plugins\pintuan\models\Goods;
use app\plugins\pintuan\Plugin;

/**
 * Class GoodsEditForm
 * @package app\plugins\pintuan\forms\mall
 */
class PinTuanGoodsForm extends Model
{
    public $id;

    private $attrGroups;

    public function rules()
    {
        return [
            [['id'], 'required'],
        ];
    }

    public function detail()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $detail = Goods::find()->with(['attr.memberPrice', 'attr.share', 'attr.shareLevel',
            'groups.attr.memberPrice', 'groups.attr.goodsAttr', 'groups.shareLevel',
            'groups.attr.shareLevel'])
            ->where([
                'mall_id' => \Yii::$app->mall->id,
                'id' => $this->id,
                'sign' => (new Plugin())->getName()
            ])->asArray()->one();

        if (!$detail) {
            throw new \Exception('商品信息有误');
        }
        $detail = $this->transformAttr($detail);
        $detail['individual_share'] = intval($detail['individual_share']);
        $detail['attr_setting_type'] = intval($detail['attr_setting_type']);
        $detail['share_type'] = intval($detail['share_type']);
        $detail['use_attr'] = intval($detail['use_attr']);
        $detail['shareLevelList'] = [];
        $goodsShare = GoodsShare::find()->select([
            'share_commission_first', 'share_commission_second', 'share_commission_third', 'level'
        ])->where(['goods_id' => $detail['id'], 'goods_attr_id' => 0, 'is_delete' => 0])->all();
        if ($goodsShare) {
            $detail['shareLevelList'] = $goodsShare;
        }

        foreach ($detail['groups'] as $key => $group) {
            $newGroup = $this->transformAttr2($group, $detail);
            $detail['groups'][$key] = $newGroup;
            if (!$detail['use_attr']) {
                $detail['groups'][$key]['defaultMemberPrice'] = $this->defaultMemberPrice($newGroup, $detail);
            }
            $shareLevelList = [];
            foreach ($group['shareLevel'] as $item) {
                if ($item['pintuan_goods_attr_id'] != 0) {
                    continue;
                }
                $shareLevelList[] = [
                    'level' => $item['level'],
                    'share_commission_first' => $item['share_commission_first'],
                    'share_commission_second' => $item['share_commission_second'],
                    'share_commission_third' => $item['share_commission_third'],
                ];
            }
            $detail['groups'][$key]['shareLevelList'] = $shareLevelList;
        }

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'detail' => $detail,
            ]
        ];
    }

    /**
     * 转换商品规格格式
     * @param $detail
     * @return mixed
     * @throws \Exception
     */
    private function transformAttr($detail)
    {
        if (!isset($detail['attr']) || !$detail['attr']) {
            throw new \Exception('商品规格信息有误');
        }

        $attrGroups = \Yii::$app->serializer->decode($detail['attr_groups']);
        $attrList = (new Goods())->resetAttr($attrGroups);

        $mallMembers = CommonMallMember::getAllMember();

        foreach ($detail['attr'] as $key => $attrItem) {
            $detail['attr'][$key]['attr_list'] = $attrList[$attrItem['sign_id']];

            $result = [];
            foreach ($attrItem['memberPrice'] as $item) {
                $result['level' . $item['level']] = $item['price'];
            }
            foreach ($mallMembers as $member) {
                if (isset($result['level' . $member['level']]) === false) {
                    $result['level' . $member['level']] = 0;
                }
            }
            $detail['attr'][$key]['member_price'] = $result;
            $shareLevel = [];
            if ($attrItem['shareLevel']) {
                foreach ($attrItem['shareLevel'] as $share) {
                    $shareLevel[] = [
                        'level' => $share['level'],
                        'share_commission_first' => $share['share_commission_first'],
                        'share_commission_second' => $share['share_commission_second'],
                        'share_commission_third' => $share['share_commission_third'],
                    ];
                }
            }
            $detail['attr'][$key]['shareLevelList'] = $shareLevel;
        }
        $detail['attr_groups'] = $attrGroups;

        return $detail;
    }

    /**
     * 转换商品规格格式
     * @param $group
     * @param $detail
     * @return mixed
     * @throws \Exception
     */
    private function transformAttr2($group, $detail)
    {
        if (!isset($group['attr']) || !$group['attr']) {
            throw new \Exception('商品规格信息有误');
        }

        $attrList = (new Goods())->resetAttr($detail['attr_groups']);

        $mallMembers = CommonMallMember::getAllMember();
        foreach ($group['attr'] as $key => $attrItem) {
            foreach ($attrItem['goodsAttr'] as $gAttrKey => $gAttrValue) {
                if ($gAttrKey !== 'id') {
                    $group['attr'][$key][$gAttrKey] = $gAttrValue;
                }
            }
            $group['attr'][$key]['attr_list'] = $attrList[$attrItem['goodsAttr']['sign_id']];
            // 处理会员价
            $result = [];
            foreach ($attrItem['memberPrice'] as $item) {
                $result['level' . $item['level']] = $item['price'];
            }
            foreach ($mallMembers as $member) {
                if (isset($result['level' . $member['level']]) === false) {
                    $result['level' . $member['level']] = 0;
                }
            }
            $group['attr'][$key]['member_price'] = $result;
            // 处理分销价
            $shareLevel = [];
            if ($attrItem['shareLevel']) {
                foreach ($attrItem['shareLevel'] as $share) {
                    $shareLevel[] = [
                        'level' => $share['level'],
                        'share_commission_first' => $share['share_commission_first'],
                        'share_commission_second' => $share['share_commission_second'],
                        'share_commission_third' => $share['share_commission_third'],
                    ];
                }
            }
            $group['attr'][$key]['shareLevelList'] = $shareLevel;
        }

        return $group;
    }

    public function defaultMemberPrice($group, $detail)
    {
        $memberPrice = CommonMallMember::getAllMember();
        $newArr = [];
        foreach ($memberPrice as $key => $item) {
            $arr = [];
            $arr['id'] = $key;
            $arr['name'] = $item['name'];
            $arr['level'] = (int)$item['level'];
            $memberPriceValue = 0;
            if ($detail['use_attr'] == 0 && count($detail['attr']) > 0) {
                $key = 'level' . $item['level'];
                $value = $group['attr'][0]['member_price'][$key];
                $memberPriceValue = $value ? $value : $memberPriceValue;
            }
            $arr['value'] = $memberPriceValue;
            $newArr[] = $arr;
        }
        return $newArr;
    }
}
