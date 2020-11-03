<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/3/14
 * Time: 9:15
 * @copyright: (c)2019 天幕网络
 * @link: http://www.67930603.top
 */

namespace app\plugins\booking\forms\api;

use app\core\response\ApiCode;
use app\forms\common\goods\CommonGoodsList;
use app\forms\common\goods\CommonGoodsMember;
use app\forms\common\video\Video;
use app\models\Model;
use app\plugins\booking\models\Goods;

/**
 * @property
 */
class GoodsListForm extends Model
{
    public $mall;
    public $page;
    public $limit;
    public $cat_id;

    public function rules()
    {
        return [
            [['page', 'limit', 'cat_id'], 'integer'],
            [['page'], 'default', 'value' => 1],
            [['limit'], 'default', 'value' => 10],
        ];
    }

    public function search()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $form = new CommonGoodsList();
        $form->model = 'app\plugins\booking\models\Goods';
        $form->status = 1;
        $form->cat_id = $this->cat_id;
        $form->sign = \Yii::$app->plugin->getCurrentPlugin()->getName();
        $form->relations = ['goodsWarehouse', 'bookingGoods', 'attr'];
        $form->getQuery();

        $list = $form->query->orderBy('sort ASC, id DESC')->page($pagination)->all();

        $newList = [];
        $permission = \Yii::$app->branch->childPermission(\Yii::$app->mall->user->adminInfo);
        try {
            $plugin = \Yii::$app->plugin->getPlugin('vip_card');
        } catch (\Exception $e) {
            $plugin = false;
        }
        /**
         * @var  $k
         * @var Goods  $goods
         */
        foreach ($list as $k => $goods) {
            $goodsStock = 0;
            try {
                foreach ($goods->attr as $item) {
                    $goodsStock += $item->stock;
                }
            }catch (\Exception $exception) {
            }
            $newList[$k] = [
                'name' => $goods['goodsWarehouse']['name'],
                'cover_pic' => $goods['goodsWarehouse']['cover_pic'],
                'goods_id' => $goods['id'],
                'price' => $goods['price'],
                'price_str' => $goods['price'] > 0 ? "￥" . $goods['price'] : '免费预约',
                'is_level' => $goods['is_level'],
                'level_price' => CommonGoodsMember::getCommon()->getGoodsMemberPrice($goods),
                'video_url' => Video::getUrl($goods['goodsWarehouse']['video_url']),
                'goods_stock' => $goodsStock
            ];
            if ($plugin && in_array('vip_card', $permission)) {
                $vip_card_appoint =  $plugin->getAppoint($goods);
                $newList[$k] = array_merge($newList[$k],$vip_card_appoint);
            }
        }

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => [
                'list' => $newList,
                'pagination' => $pagination
            ]
        ];
    }
}
