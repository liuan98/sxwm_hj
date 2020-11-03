<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/3/18
 * Time: 14:31
 * @copyright: (c)2019 天幕网络
 * @link: http://www.67930603.top
 */

namespace app\plugins\bargain\forms\api;


use app\core\response\ApiCode;
use app\plugins\bargain\forms\common\CommonBargainOrder;
use app\plugins\bargain\models\BargainOrder;
use app\plugins\bargain\models\BargainUserOrder;

class ActivityForm extends ApiModel
{
    public $bargain_order_id;
    public $page;

    public function rules()
    {
        return [
            [['bargain_order_id'], 'required'],
            [['page'], 'integer'],
            [['page'], 'default', 'value' => 1]
        ];
    }

    public function search()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        try {
            $commonBargainOrder = CommonBargainOrder::getCommonBargainOrder($this->mall);

            /* @var BargainOrder $bargainOrder */
            $bargainOrder = $commonBargainOrder->getBargainOrder($this->bargain_order_id);
            if (!$bargainOrder) {
                throw new \Exception('砍价信息不存在');
            }
            if ($bargainOrder->bargainGoods->is_delete == 1) {
                throw new \Exception('砍价活动已关闭');
            }
            if ($bargainOrder->goods->is_delete == 1) {
                throw new \Exception('砍价活动已关闭');
            }
            if ($bargainOrder->goods->status == 0) {
                throw new \Exception('砍价活动已关闭');
            }
            if ($bargainOrder->goods->goodsWarehouse->is_delete == 1) {
                throw new \Exception('砍价活动已关闭');
            }

            $bargainInfo = $commonBargainOrder->getBargainInfo($bargainOrder, $this->page);
            $bargainInfo['goods_name'] = $bargainOrder->goods->name;
            $bargainInfo['cover_pic'] = $bargainOrder->goods->coverPic;
            $bargainInfo['goods_id'] = $bargainOrder->goods->id;
            $bargainInfo['goods_attr_id'] = $bargainOrder->bargainGoods->goodsAttr->id;
            $bargainInfo['initiator_avatar'] = $bargainOrder->user->userInfo->avatar;
            $bargainInfo['initiator_user_id'] = $bargainOrder->user->id;
            $bargainInfo['total_people'] = count($bargainOrder->userOrderList);
            $bargainInfo['app_share_pic'] = $bargainOrder->goods->app_share_pic;
            $bargainInfo['app_share_title'] = $bargainOrder->goods->app_share_title;
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => $bargainInfo
            ];
        } catch (\Exception $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage()
            ];
        }
    }
}
