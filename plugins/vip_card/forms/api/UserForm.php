<?php
/**
 * @copyright ©2019 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2019/8/27
 * Time: 16:24
 */

namespace app\plugins\vip_card\forms\api;

use app\core\Pagination;
use app\core\response\ApiCode;
use app\models\Goods;
use app\models\GoodsCats;
use app\models\GoodsWarehouse;
use app\models\Model;
use app\plugins\vip_card\models\VipCardUser;

class UserForm extends Model
{
    public function getInfo()
    {
        $user = VipCardUser::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'user_id' => \Yii::$app->user->id,
            'is_delete' => 0,
        ])->with(['order' => function($query) {
            $query->select(["DATE_FORMAT(`created_at`, '%Y-%m-%d') AS `created_at`","expire","detail_name","user_id","detail_id"])->where([
                'AND',
                ['status' => 1],
            ])->orderBy('created_at DESC, id DESC');
        }])->asArray()->one();

        if ($user) {
            $user['expire'] = round((strtotime($user['end_time']) - time())/3600/24);
            $user['image_type_info'] = json_decode($user['image_type_info'],true);
            $goodList = Goods::find()->where(['goods_warehouse_id' => $user['image_type_info']['goods'],'mall_id' => \Yii::$app->mall->id, 'is_delete' => 0, 'sign' => ''])->all();
            foreach ($goodList as $goods) {
                try {
                    $temp['name'] = $goods->name;
                    $temp['id'] = $goods->id;
                    $temp['sales'] = "已售{$goods->getSales()}件";
                    $temp['price'] = $goods->price;
                    $temp['cover_pic'] = $goods->coverPic;
                    $temp['page_url'] = "/pages/goods/goods?id={$goods->id}";
                    $user['image_type_info_detail']['goods'][] = $temp;
                } catch (\Exception $e) {
                    \Yii::error('会员卡指定商品不存在');
                    \Yii::error($e);
                }
            }
            unset($temp);
            $user['image_type_info_detail']['cats'] = GoodsCats::find()->where(['mall_id' => \Yii::$app->mall->id, 'is_delete' => 0,'id' => $user['image_type_info']['cats']])->asArray()->all();
            foreach ($user['image_type_info_detail']['cats'] as &$item) {
                $item['value'] = $item['id'];
                $item['label'] = $item['name'];
            }
            unset($item);
            $user['image_type_info_detail']['all'] = $user['image_type_info']['all'];
        }

        return [
            'code' => 0,
            'msg' => '数据请求成功',
            'data' => [
                'user' => $user
            ]
        ];
    }

    public function right()
    {
        $userCard = VipCardUser::find()->where(['user_id' => \Yii::$app->user->id, 'is_delete' => 0])->one();
        if (empty($userCard)) {
            throw new \Exception('会员卡用户不存在');
        }
        $type = json_decode($userCard->image_type_info,true);
        $which = \Yii::$app->request->get('type', 1);
        if ($which == 1) {
            $count = count($type['goods']);
            $page = \Yii::$app->request->get('page', 1);
            $pagination = new Pagination(['totalCount' => $count, 'pageSize' => 10]);
            if ($page) {
                $pagination->page = $page - 1;
            } else {
                $pagination->page = \Yii::$app->request->get('page', 1) - 1;
            }
            $query = Goods::find()->where(['goods_warehouse_id' => $type['goods'], 'is_delete' => 0,'mall_id' => \Yii::$app->mall->id, 'sign' => '']);
            $goodsList = $query->limit($pagination->limit)->offset($pagination->offset)->all();
            $list = [];
            foreach ($goodsList as $goods) {
                try {
                    $temp['name'] = $goods->name;
                    $temp['id'] = $goods->id;
                    $temp['sales'] = "已售{$goods->getSales()}件";
                    $temp['price'] = $goods->price;
                    $temp['cover_pic'] = $goods->coverPic;
                    $temp['page_url'] = "/pages/goods/goods?id={$goods->id}";
                    $temp['video_url'] = $goods->videoUrl;
                    $list[] = $temp;
                } catch (\Exception $e) {
                    \Yii::error('会员卡指定商品不存在');
                    \Yii::error($e);
                }
            }
        } else {
            $count = count($type['cats']);
            $page = \Yii::$app->request->get('page', 1);
            $pagination = new Pagination(['totalCount' => $count, 'pageSize' => 10]);
            if ($page) {
                $pagination->page = $page - 1;
            } else {
                $pagination->page = \Yii::$app->request->get('page', 1) - 1;
            }
            $query = GoodsCats::find()->where(['id' => $type['cats'], 'mall_id' => \Yii::$app->mall->id, 'is_delete' => 0,]);
            $list = $query->limit($pagination->limit)->offset($pagination->offset)->all();
        }
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'list' => $list,
                'pagination' => $pagination
            ]
        ];
    }
}