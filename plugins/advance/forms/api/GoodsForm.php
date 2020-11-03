<?php
/**
 * link: http://www.67930603.top/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: zbj
 */

namespace app\plugins\advance\forms\api;

use app\core\response\ApiCode;
use app\forms\common\goods\CommonGoodsDetail;
use app\forms\common\goods\CommonGoodsList;
use app\forms\common\goods\CommonGoodsMember;
use app\forms\common\template\TemplateList;
use app\models\GoodsMemberPrice;
use app\models\MallMembers;
use app\models\Model;
use app\models\User;
use app\plugins\advance\forms\common\SettingForm;
use app\plugins\advance\models\AdvanceGoods;
use app\plugins\advance\models\AdvanceGoodsAttr;
use app\plugins\advance\models\AdvanceOrder;
use app\plugins\advance\models\Goods;
use app\plugins\advance\Plugin;
use yii\helpers\ArrayHelper;

class GoodsForm extends Model
{
    public $id;
    public $page;
    public $goods_id;
    public $keyword;

    public function rules()
    {
        return [
            [['page', 'goods_id', 'id'], 'integer'],
            [['keyword'], 'string'],
            [['page'], 'default', "value" => 1]
        ];
    }

    public function getList()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $form = new CommonGoodsList();
        $form->model = 'app\plugins\advance\models\Goods';
        if ($this->goods_id) {
            $advance_goods = Goods::find()->with('cat')->where(['id' => $this->goods_id])->one();
            if (empty($advance_goods)) {
                return [
                    'code' => ApiCode::CODE_SUCCESS,
                    'msg' => '参数错误',
                ];
            }
            $form->cat_id = $advance_goods->cat->cat_id;
        }

        $form->page = $this->page;
        $form->keyword = $this->keyword;
        $form->sign = 'advance';
        $form->sort = 1;
        $form->relations = ['goodsWarehouse.cats', 'attr', 'advanceGoods'];
        $form->is_array = 1;
        $form->status = 1;
        $form->getQuery();
        $query = $form->query->select('g.*,ag.start_prepayment_at,ag.end_prepayment_at')
            ->leftJoin(['ag' => AdvanceGoods::tableName()], 'g.id = ag.goods_id')
            ->andWhere(['<=', 'ag.start_prepayment_at', date('Y-m-d H:i:s', time())])
            ->andWhere(['>=', 'ag.end_prepayment_at', date('Y-m-d H:i:s', time())]);
        if ($this->goods_id) {
            $query = $query->andWhere(['<>', 'ag.goods_id', $this->goods_id]);
        }
        $list = $query->page($form->pagination, $form->limit, $form->page)
            ->groupBy($form->group_by_name)
            ->asArray($form->is_array)
            ->all();

        $permission = \Yii::$app->branch->childPermission(\Yii::$app->mall->user->adminInfo);
        try {
            $plugin = \Yii::$app->plugin->getPlugin('vip_card');
        } catch (\Exception $e) {
            $plugin = false;
        }
        foreach ($list as &$item) {
            $attrList = AdvanceGoodsAttr::find()->where([
                'goods_id' => $item['id'],
                'is_delete' => 0,
            ])->asArray()->all();

            $goodsStock = 0;
            foreach ($item['attr'] as &$aItem) {
                foreach ($attrList as $aLItem) {
                    if ($aItem['id'] == $aLItem['goods_attr_id']) {
                        $aItem['deposit'] = floatval($aLItem['deposit']);
                        $aItem['swell_deposit'] = floatval($aLItem['swell_deposit']);
                        //取最小定金
                        if ($item['advanceGoods']['deposit'] == 0) {
                            $item['advanceGoods']['deposit'] = floatval($aLItem['deposit']);
                            $item['advanceGoods']['swell_deposit'] = floatval($aLItem['swell_deposit']);
                        }
                        if ($item['advanceGoods']['deposit'] > $aLItem['deposit']) {
                            $item['advanceGoods']['deposit'] = floatval($aLItem['deposit']);
                            $item['advanceGoods']['swell_deposit'] = floatval($aLItem['swell_deposit']);
                        } else {
                            $item['advanceGoods']['deposit'] = floatval($item['advanceGoods']['deposit']);
                            $item['advanceGoods']['swell_deposit'] = floatval($item['advanceGoods']['swell_deposit']);
                        }
                    }
                }
                $goodsStock += $aItem['stock'];
            }

            $item['goods_stock'] = $goodsStock;
            $item['cover_pic'] = $item['goodsWarehouse']['cover_pic'];
            $item['name'] = $item['goodsWarehouse']['name'];
            $item['price_content'] = $item['price'];
            $item['original_price'] = $item['goodsWarehouse']['original_price'];
            $item['page_url'] = (new Plugin())->getGoodsUrl($item);
            //预售销量
            $count = AdvanceOrder::find()->andWhere(['mall_id' => \Yii::$app->mall->id, 'goods_id' => $item['id'], 'is_delete' => 0, 'is_cancel' => 0, 'is_refund' => 0, 'is_pay' => 1, 'is_recycle' => 0])->sum('goods_num');
            $item['sales'] = '已售' . ($item['virtual_sales'] + $count) . $item['goodsWarehouse']['unit'];

            //计算会员价
//            $member_price = $item['price'];
//            if ($item['is_level'] == 1 && $item['is_level_alone'] != 1) {
//                $level_info = MallMembers::findOne(['mall_id' => \Yii::$app->mall->id, 'level' => \Yii::$app->user->identity['identity']['member_level']]);
//                if (!empty($level_info)) {
//                    $member_price = bcdiv(bcmul($member_price, $level_info->discount), 10);
//                }
//            } elseif ($item['is_level'] == 1 && $item['is_level_alone'] == 1) {
//                /* @var \app\models\GoodsMemberPrice $goods_member_price */
//                if (\Yii::$app->user->identity['identity']['member_level'] > 0) {
//                    $goods_member_price = GoodsMemberPrice::find()
//                        ->where(['level' => \Yii::$app->user->identity['identity']['member_level'], 'is_delete' => 0,
//                            'goods_id' => $item['id']])->one();
//                    if (!empty($goods_member_price)) {
//                        $member_price = $goods_member_price->price;
//                    }
//                }
//            }
//            $item['member_price'] = $member_price;
            $item['level_price'] = CommonGoodsMember::getCommon()->getGoodsMemberPrice((object)$item);
            if ($plugin && in_array('vip_card', $permission)) {
                $item['vip_card_appoint'] = $plugin->getAppoint($item);
            }
        }


        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'list' => $list,
                'pagination' => $form->pagination,
            ]
        ];
    }

    public function detail()
    {
        try {
            $form = new CommonGoodsDetail();
            $form->mall = \Yii::$app->mall;
            $form->user = User::findOne(\Yii::$app->user->id);
            $goods = $form->getGoods($this->id);
            if (!$goods) {
                throw new \Exception('商品不存在');
            }
            if ($goods->status != 1) {
                throw new \Exception('商品未上架');
            }
            $form->goods = $goods;
            $goods = $form->getAll();
            //预售销量
            $count = AdvanceOrder::find()->andWhere(['mall_id' => \Yii::$app->mall->id, 'goods_id' => $form->goods->id, 'is_delete' => 0, 'is_cancel' => 0, 'is_refund' => 0, 'is_pay' => 1, 'is_recycle' => 0])->sum('goods_num');
            $goods['sales'] = $form->goods->virtual_sales + $count;


            $attrList = AdvanceGoodsAttr::find()->where([
                'goods_id' => $goods['id'],
                'is_delete' => 0,
            ])->asArray()->all();
            foreach ($goods['attr'] as &$aItem) {
                foreach ($attrList as $alItem) {
                    if ($aItem['id'] == $alItem['goods_attr_id']) {
                        $aItem['deposit'] = floatval($alItem['deposit']);
                        $aItem['swell_deposit'] = floatval($alItem['swell_deposit']);
                    }
                }
            }

            $advanceGoods = AdvanceGoods::findOne(['goods_id' => $goods['id']]);
            if (strtotime($advanceGoods->end_prepayment_at) < time()) {
                throw new \Exception('该预售商品已过预售时间');
            }
            $goods = ArrayHelper::toArray($goods);
            $advanceGoods->ladder_rules = json_decode($advanceGoods->ladder_rules, true);
            $goods['advanceGoods'] = $advanceGoods;

            $setting = (new SettingForm())->search();
            $goods['goods_marketing']['limit'] = $setting['is_territorial_limitation']
                ? $goods['goods_marketing']['limit'] : '';
            $groupMinMemberPrice = 0;
            $groupMaxMemberPrice = 0;

            foreach ($goods['attr'] as &$aItem) {
                $aItem['extra'] = [
                    [
                        'value' => floatval($aItem['deposit']),
                        'name' => '定金'
                    ],
                    [
                        'value' => floatval($aItem['swell_deposit']),
                        'name' => '膨胀金'
                    ]
                ];
                if (!$groupMinMemberPrice) {
                    $groupMinMemberPrice = $aItem['price_member'];
                    $groupMaxMemberPrice = $aItem['price_member'];
                }
                $groupMinMemberPrice = min($aItem['price_member'], $groupMinMemberPrice);
                $groupMaxMemberPrice = max($aItem['price_member'], $groupMaxMemberPrice);
            }
            unset($aItem);

            $goods['group_min_member_price'] = $groupMinMemberPrice;
            $goods['group_max_member_price'] = $groupMaxMemberPrice;

            // 判断插件分销是否开启
            if (!$setting['is_share']) {
                $goods['share'] = 0;
            }

            try {
                $goods['template_message'] = TemplateList::getInstance()->getTemplate(\Yii::$app->appPlatform, [
                    'pay_advance_balance',
                ]);
            } catch (\Exception $exception) {
                $goods['template_message'] = [];
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '请求成功',
                'data' => [
                    'detail' => $goods
                ]
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
                'error' => [
                    'line' => $e->getLine()
                ]
            ];
        }
    }
}
