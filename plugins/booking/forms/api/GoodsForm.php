<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/3/14
 * Time: 10:29
 * @copyright: (c)2019 天幕网络
 * @link: http://www.67930603.top
 */

namespace app\plugins\booking\forms\api;

use app\core\response\ApiCode;
use app\forms\common\goods\CommonGoodsDetail;
use app\forms\common\video\Video;
use app\models\Model;
use app\plugins\booking\forms\common\CommonBooking;
use app\plugins\booking\forms\common\CommonBookingGoods;

/**
 * @property
 */
class GoodsForm extends Model
{

    public $user;
    public $goods_id;

    public function rules()
    {
        return [
            [['goods_id'], 'required'],
            [['goods_id'], 'integer'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'goods_id' => '预约商品id'
        ];
    }

    public function search()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        try {
            $bookingGoods = CommonBookingGoods::getGoods($this->goods_id);
            if (!$bookingGoods) {
                throw new \Exception('商品不存在或已删除');
            }
            //@todo 可减少查询
            $commonGoods = CommonGoodsDetail::getCommonGoodsDetail(\Yii::$app->mall);

            //todo 过公共方法，用于记录足迹，需优化
            $commonGoods->getGoods($this->goods_id);

            $commonGoods->user = \Yii::$app->user->identity;
            $commonGoods->goods = $bookingGoods->goods;
            $detail = $commonGoods->getAll();
            $setting = CommonBooking::getSetting();
            $newGoods = [
                'is_negotiable' => 0,
                'attr_group' => $detail['attr_groups'],
                'attr' => $detail['attr'],
                'share' => $setting['is_share'] == 1 ? $detail['share'] : 0,
                'cover_pic' => $detail['cover_pic'],
                'name' => $detail['name'],
                'pic_url' => $detail['pic_url'],
                'video_url' => Video::getUrl($detail['video_url']),
                'unit' => $detail['unit'],
                'price' => $detail['price'],
                'price_str' => $detail['price'] > 0 ? sprintf("￥%s元", $detail['price']) : '免费预约',
                'app_share_pic' => $detail['app_share_pic'],
                'app_share_title' => $detail['app_share_title'],
                'detail' => $detail['detail'],
                'goods_id' => $this->goods_id,
                'store' => $bookingGoods->currentStore,
                'level_show' => $detail['level_show'],
                'price_member_max' => $detail['price_member_max'] ?? 0,
                'price_member_min' => $detail['price_member_min'] ?? 0,
                'price_max' => $detail['price_max'] ?? 0,
                'price_min' => $detail['price_min'] ?? 0,
                'is_sales' => $detail['is_sales'] ?? 0,
                'sales' => $detail['sales'] ?? 0,
                'services' => $detail['services'],
                'goods_num' => $detail['goods_num'],
                'vip_card_appoint' => $detail['vip_card_appoint']
            ];
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    'list' => $newGoods
                ]
            ];
        } catch (\Exception $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage(),
            ];
        }
    }
}
