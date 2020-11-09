<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/3/18
 * Time: 17:27
 * @copyright: (c)2019 天幕网络
 * @link: http://www.67930603.top
 */

namespace app\plugins\bargain\forms\api;


use app\core\response\ApiCode;
use app\plugins\bargain\forms\common\CommonBargainOrder;
use app\plugins\bargain\models\BargainGoods;
use app\plugins\bargain\models\BargainOrder;
use app\plugins\bargain\models\Code;

class BargainOrderListForm extends ApiModel
{
    public $page;
    public $limit;

    public function rules()
    {
        return [
            [['page', 'limit'], 'integer'],
            [['page'], 'default', 'value' => 1],
            [['limit'], 'default', 'value' => 10]
        ];
    }

    public function search()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        try {
            $common = CommonBargainOrder::getCommonBargainOrder($this->mall);
            /* @var BargainOrder[] $bargainOrderList */
            $bargainOrderList = $common->getBargainOrderByUserId($this->user->id, $this->page, $this->limit);
            $newList = [];
            foreach ($bargainOrderList as $bargainOrder) {
                $bargainUserOrderList = $bargainOrder->userOrderList;
                $totalPrice = array_sum(array_column($bargainUserOrderList, 'price'));
                $nowPrice = $bargainOrder->getNowPrice($totalPrice);
                $resetPrice = $nowPrice - $bargainOrder->min_price;
                /* @var BargainGoods $bargainGoodsData */
                $bargainGoodsData = \Yii::$app->serializer->decode($bargainOrder->bargain_goods_data);
                $newItem = [
                    'goods_name' => $bargainOrder->goods->name,
                    'cover_pic' => $bargainOrder->goods->coverPic,
                    'status' => $bargainOrder->status,
                    'status_content' => '',
                    'content' => '进行中',
                    'reset_time' => $bargainOrder->status == Code::BARGAIN_PROGRESS ? $bargainOrder->resetTime : 0,
                    'price' => price_format($bargainOrder->price, 'float', 2),
                    'min_price' => price_format($bargainOrder->min_price, 'float', 2),
                    'now_price' => price_format($nowPrice, 'float', 2),
                    'reset_price' => price_format($resetPrice, 'float', 2),
                    'type' => $bargainGoodsData->type,
                    'finish_at' => $bargainOrder->finishAt,
                    'bargain_order_id' => $bargainOrder->id,
                    'goods_id' => $bargainOrder->goods->id,
                    'goods_attr_id' => $bargainOrder->bargainGoods->goodsAttr->id
                ];
                switch ($bargainOrder->status) {
                    case 0:
                        $newItem['status_content'] = '砍价进行中';
                        break;
                    case 1:
                        $newItem['status_content'] = "砍价成功FCFA{$newItem['now_price']}";
                        $newItem['content'] = '已结束';
                        break;
                    case 2:
                        $newItem['status_content'] = '砍价失败';
                        $newItem['content'] = '已结束';
                        break;
                    default:
                }
                $newList[] = $newItem;
            }
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    'list' => $newList
                ]
            ];
        } catch (\Exception $exception) {
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => $exception->getMessage()
            ];
        }
    }
}
