<?php
/**
 * link: http://www.67930603.top/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: Alpha
 */

namespace app\forms\mall\goods;

use app\core\response\ApiCode;
use app\events\GoodsEvent;
use app\forms\common\goods\CommonGoods;
use app\forms\common\mch\MchSettingForm;
use app\models\Goods;
use app\models\GoodsCatRelation;
use app\models\GoodsWarehouse;
use app\models\MallGoods;
use app\plugins\mch\models\MchGoods;

/**
 * @property MallGoods $mallGoods;
 */
class GoodsEditForm extends BaseGoodsEdit
{
    // 商品库商品字段
    public $name;
    public $original_price;
    public $cost_price;
    public $detail;
    public $video_url;
    public $unit;
    public $pic_url;

    // 商城商品字段
    public $is_negotiable;
    public $is_quick_shop;
    public $is_sell_well;

    protected $mallGoods;

    public function rules()
    {
        return array_merge(parent::rules(), [
            [['name', 'original_price', 'cost_price', 'detail'
                , 'unit',], 'required'],
            [['is_quick_shop', 'is_sell_well', 'is_negotiable'], 'integer'],
            [['video_url'], 'string'],
            [['original_price', 'cost_price'], 'number', 'min' => 0],
            [['pic_url'], 'safe'],
            [['is_quick_shop', 'is_sell_well', 'is_negotiable', ], 'default', 'value' => 0],
            [['cost_price', 'original_price'], 'number', 'max' => 9999999]
        ]);
    }

    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'name' => '商品名称',
            'original_price' => '商品原价',
            'cost_price' => '商品成本价',
            'detail' => '商品详情',
            'cover_pic' => '商品缩略图',
            'video_url' => '商品视频',
            'unit' => '商品单位',
            'is_quick_shop' => '是否快速购买',
            'is_sell_well' => '是否热销',
        ]);
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        if (count($this->pic_url) <= 0) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '请上传商品轮播图'
            ];
        }

        $transaction = \Yii::$app->db->beginTransaction();

        try {
            $this->attrValidator();
            $this->attrGroupNameValidator();
            if (!$this->id) {
                $this->add();
            } else {
                $this->update();
            }

            $this->setAttr();
            $this->setGoodsCat();
            $this->setGoodsService();
            $this->setCard();
            $this->setListener();

            $transaction->commit();
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功',
            ];
        } catch (\Exception $e) {
            $transaction->rollBack();
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
                'error' => [
                    'line' => $e->getLine(),
                ]
            ];
        }
    }

    protected function setGoodsSign()
    {
        return $this->mch_id > 0 ? 'mch' : '';
    }

    /**
     * @throws \Exception
     * 新增商品库商品
     */
    private function add()
    {
        $goodsWarehouse = $this->editGoodsWarehouse();
        $this->goods_warehouse_id = $goodsWarehouse->id;
        $this->setGoods();
        $this->editMallGoods();
        $this->editMchGoods();
    }

    /**
     * @throws \Exception
     * 修改商品库商品
     */
    private function update()
    {
        $common = CommonGoods::getCommon();
        $this->setGoods();
        if (!$this->goods->goodsWarehouse) {
            throw new \Exception('商品库错误：查找不到id为' . $this->goods->goods_warehouse_id . '的商品');
        }
        $this->editGoodsWarehouse($this->goods->goodsWarehouse);

        $mallGoods = $common->getMallGoods($this->goods->id);
        if (!$mallGoods) {
            throw new \Exception('mall_goods商品不存在或者已删除');
        }
        $this->editMallGoods($mallGoods);
        $this->editMchGoods($this->goods->id);
    }

    /**
     * @param null|GoodsWarehouse $goodsWarehouse
     * @return GoodsWarehouse|null
     * @throws \Exception
     * 编辑商品库
     */
    private function editGoodsWarehouse($goodsWarehouse = null)
    {
        if (!$goodsWarehouse) {
            $goodsWarehouse = new GoodsWarehouse();
            $goodsWarehouse->mall_id = \Yii::$app->mall->id;
            $goodsWarehouse->is_delete = 0;
        }
        $goodsWarehouse->name = $this->name;
        $goodsWarehouse->original_price = $this->original_price;
        $goodsWarehouse->cost_price = $this->cost_price;
        $goodsWarehouse->detail = $this->detail;
        $goodsWarehouse->cover_pic = $this->pic_url[0]['pic_url'];
        $goodsWarehouse->pic_url = \Yii::$app->serializer->encode($this->pic_url);
        $goodsWarehouse->video_url = $this->video_url;
        $goodsWarehouse->unit = $this->unit;
        if (!$goodsWarehouse->save()) {
            throw new \Exception('商品保存失败：' . $this->getErrorMsg($goodsWarehouse));
        }
        $this->goodsWarehouse = $goodsWarehouse;
        return $goodsWarehouse;
    }

    /**
     * @param null|MallGoods $mallGoods
     * @return MallGoods|null
     * @throws \Exception
     * 编辑商城商品
     */
    private function editMallGoods($mallGoods = null)
    {
        if (!$mallGoods) {
            $mallGoods = new MallGoods();
            $mallGoods->is_delete = 0;
            $mallGoods->mall_id = \Yii::$app->mall->id;
            $mallGoods->goods_id = $this->goods->id;
        }
        $mallGoods->is_quick_shop = $this->is_quick_shop;
        $mallGoods->is_sell_well = $this->is_sell_well;
        $mallGoods->is_negotiable = $this->is_negotiable;
        if (!$mallGoods->save()) {
            throw new \Exception('商品保存失败：' . $this->getErrorMsg($mallGoods));
        }
        return $mallGoods;
    }

    /**
     * @return MchGoods|null
     * @throws \Exception
     * 编辑多商户商品
     */
    private function editMchGoods($goodsId = null)
    {
        if ($this->mch_id <= 0) {
            return false;
        }
        $mchGoods = null;
        if ($goodsId) {
            $mchGoods = MchGoods::findOne(['goods_id' => $goodsId]);
            if (!$mchGoods) {
                throw new \Exception('商户商品不存在');
            }
        }

        if (!$mchGoods) {
            $mchGoods = new MchGoods();
            $mchGoods->is_delete = 0;
            $mchGoods->mall_id = \Yii::$app->mall->id;
            $mchGoods->mch_id = $this->mch_id;
            $mchGoods->goods_id = $this->goods->id;
        }

        // 多商户开启商品上架审核,每次编辑都需下架
        $form = new MchSettingForm();
        $setting = $form->search();
        if ($setting['is_goods_audit'] == 1) {
            $this->goods->status = 0;
            $res = $this->goods->save();
            if (!$res) {
                throw new \Exception($this->goods);
            }
            $mchGoods->status = 0;
            $mchGoods->remark = '';
        }

        $mchGoods->sort = $this->sort;
        if (!$mchGoods->save()) {
            throw new \Exception('商品保存失败：' . $this->getErrorMsg($mchGoods));
        }

        return $mchGoods;
    }

    /**
     * 商品分类
     */
    protected function setGoodsCat()
    {
        if (!is_array($this->cats) || !is_array($this->mchCats)) {
            throw new \Exception('分类必须为数组');
        }
        $goodsCatRelationList = $this->goodsWarehouse->goodsCatRelation;

        $catIdList = array_column($goodsCatRelationList, 'cat_id');
        $cats = array_merge($this->cats, $this->mchCats);
        $catIdListDiff = array_diff($catIdList, $cats);
        $catsDiff = array_diff($cats, $catIdList);
        if (count($catIdListDiff) > 0) {
            foreach ($catIdListDiff as $key => $value) {
                $goodsCatRelation = $goodsCatRelationList[$key];
                $goodsCatRelation->is_delete = 1;
                $goodsCatRelation->save();
            }
        }
        if (count($catsDiff) > 0) {
            foreach ($catsDiff as $item) {
                $goodsCatRelation = new GoodsCatRelation();
                $goodsCatRelation->cat_id = $item;
                $goodsCatRelation->goods_warehouse_id = $this->goodsWarehouse->id;
                $goodsCatRelation->save();
            }
        }
    }
}
