<?php
/**
 * @copyright (c)天幕网络
 * @author Lu Wei
 * @link http://www.67930603.top/
 * Created by IntelliJ IDEA
 * Date Time: 2018/12/5 16:13
 */


namespace app\controllers\api;

use app\controllers\api\filters\LoginFilter;
use app\forms\api\AddressForm;
use app\forms\api\FavoriteForm;
use app\forms\api\FavoriteListForm;
use app\forms\api\user\SmsForm;
use app\forms\api\user\UserInfoForm;
use app\forms\api\WechatDistrictForm;

class UserController extends ApiController
{
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'login' => [
                'class' => LoginFilter::class,
                'ignore' => ['config']
            ],
        ]);
    }

    public function actionUserInfo()
    {
        $form = new UserInfoForm();
        return $form->getInfo();
    }

    public function actionConfig()
    {
        $form = new UserInfoForm();
        return $form->config();
    }

    //收货地址列表
    public function actionAddress()
    {
        $form = new AddressForm();
        $form->hasCity = \Yii::$app->request->get('hasCity');
        return $form->getList();
    }

    public function actionAddressDefault()
    {
        $form = new AddressForm();
        $form->attributes = \Yii::$app->request->post();
        $form->id = \Yii::$app->request->post('id');
        $form->is_default = \Yii::$app->request->post('is_default');
        return $form->default();
    }

    public function actionAddressDetail()
    {
        $form = new AddressForm();
        $form->id = \Yii::$app->request->get('id');
        return $form->detail();
    }

    public function actionAddressDestroy()
    {
        $form = new AddressForm();
        $form->attributes = \Yii::$app->request->get();
        $form->attributes = \Yii::$app->request->post();
        $form->id = \Yii::$app->request->post('id');
        return $form->destroy();
    }

    public function actionAddressSave()
    {
        $form = new AddressForm();
        $form->attributes = \Yii::$app->request->post();
        $form->province_id = \Yii::$app->request->post('province_id');
        $form->city_id = \Yii::$app->request->post('city_id');
        $form->district_id = \Yii::$app->request->post('district_id');
        $form->id = \Yii::$app->request->post('id');
        return $form->save();
    }

    //根据微信地址获取数据库省市区数据
    public function actionWechatDistrict()
    {
        $form = new WechatDistrictForm();
        $form->attributes = \Yii::$app->request->get();
        return $form->getList();
    }

    //添加微信获取的地址
    // public function actionAddWechatAddress()
    // {
    //     $form = new AddWechatAddressForm();
    //     $form->attributes = \Yii::$app->request->post();
    //     $form->store_id = $this->store->id;
    //     $form->user_id = \Yii::$app->user->id;
    //     return new BaseApiResponse($form->save());
    // }

    public function actionFavoriteAdd()
    {
        $form = new FavoriteForm();
        $form->attributes = \Yii::$app->request->get();
        return $form->create();
    }

    public function actionFavoriteRemove()
    {
        $form = new FavoriteForm();
        $form->attributes = \Yii::$app->request->get();
        return $form->delete();
    }

    public function actionMyFavoriteGoods()
    {
        $form = new FavoriteListForm();
        $form->attributes = \Yii::$app->request->get();
        return $form->goods();
    }

    public function actionMyFavoriteTopic()
    {
        $form = new FavoriteListForm();
        $form->attributes = \Yii::$app->request->get();
        return $form->topic();
    }


    public function actionPhoneCode()
    {
        $form = new SmsForm();
        $form->attributes = \Yii::$app->request->get();
        return $form->code();
    }

    public function actionPhoneEmpower()
    {
        $form = new SmsForm();
        $form->attributes = \Yii::$app->request->post();
        return $form->empower();
    }
}
