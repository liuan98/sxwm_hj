<?php
/**
 * @copyright (c)天幕网络
 * @author Lu Wei
 * @link http://www.67930603.top/
 * Created by IntelliJ IDEA
 * Date Time: 2018/12/7 14:03
 */


namespace app\controllers\api;

use app\core\response\ApiCode;
use app\forms\api\BuyDataForm;
use app\forms\api\ConfigForm;
use app\forms\api\IndexForm;
use app\forms\common\CommonDelivery;
use app\models\CityDeliverySetting;

class IndexController extends ApiController
{
        //默认数据
    private $default = [
        'is_superposition' => 0,//是否叠加 1叠加0不
        'mobile' => [],
        'price_mode' => ["start_price" => "0", "start_distance" => "0", "add_distance" => "0", "add_price" => "0", "fixed" => "0"],//计费方式
        'web_key' => '',//高德key
        'address' => ["address" => "", "longitude" => "", "latitude" => ""],//配送起点地址
        'explain' => '',//配送说明（配送时间什么的）
        'range' => [],//经纬度划定范围
        'price_enable' => 0,
        'contact_way' => '',
        'is_free_delivery' => 0,
        'free_delivery' => 0,
    ];
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
        ]);
    }

    public function actionConfig()
    {
        return (new ConfigForm())->search();
    }

    public function actionPurchase()
    {
        return (new BuyDataForm())->search();
    }

    public function actionIndex()
    {
        $form = new IndexForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->getIndex());
    }
    
    public function actionDeny()
    {
        $data = CityDeliverySetting::find()->select('key,value')
            ->where(['mall_id' => 1, 'is_delete' => 0])->asArray()->all();//var_dump($data);
        $list = [];
        if ($data && !empty($data)) {
            foreach ($data as $value) {
                $list[$value['key']] = json_decode($value['value'], true);
            }
        }
        foreach ($this->default as $key => $item) {
            if (!isset($list[$key])) {
                $list[$key] = $item;
            }
        }
        $list['mobile'] = CommonDelivery::getInstance()->getManList();
        return $this->asJson(
            [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    'list' => $list
                ]
            ]
        );
    }
}
