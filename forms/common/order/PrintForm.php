<?php
/**
 * link: http://www.67930603.top/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\forms\common\order;

use app\core\response\ApiCode;
use app\forms\common\CommonOption;
use app\models\Delivery;
use app\models\Express;
use app\models\MallSetting;
use app\models\Model;
use app\models\Option;
use app\models\Order;
use app\models\OrderDetailExpress;
use app\models\OrderExpressSingle;

class PrintForm extends Model
{
    public $order_id;
    public $express;
    public $zip_code;
    public $mch_id;
    public $delivery_account;

    public function rules()
    {
        return [
            [['order_id', 'express'], 'required'],
            [['order_id', 'mch_id'], 'integer'],
            [['zip_code', 'express', 'delivery_account'], 'string'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'express' => "快递公司名称",
            'delivery_account' => '面单账户'
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        try {
            /** @var Order $order */
            $order = Order::find()->where([
                'id' => $this->order_id,
                'is_delete' => 0,
                'mall_id' => \Yii::$app->mall->id,
                'mch_id' => $this->mch_id ?: \Yii::$app->user->identity->mch_id,
            ])->with('detailExpress')->one();

            if (!$order) {
                throw new \Exception('订单不存在');
            }

            if ($order->status == 0) {
                throw new \Exception('订单进行中,不能进行操作');
            }

            $express = Express::getOne($this->express);
            if (!$express) {
                throw new \Exception('快递公司不正确');
            }

            //历史数据
            $setting = MallSetting::findAll([
                'mall_id' => \Yii::$app->mall->id,
                'is_delete' => 0
            ]);
            $setting = array_column($setting, 'value', 'key');

            $express_single = OrderExpressSingle::findAll([
                'mall_id' => \Yii::$app->mall->id,
                'ebusiness_id' => $setting['kdniao_mch_id'],
                'order_id' => $order->id
            ]);

            foreach ($express_single as $item) {
                $detailExpress = OrderDetailExpress::find()->where(['express_single_id' => $item->id, 'is_delete' => 0, 'mall_id' => \Yii::$app->mall->id])->one();
                if ($item->express_code == $express['code'] && !$detailExpress) {
                    $result = [
                        'EBusinessID' => $item->ebusiness_id,
                        'Order' => json_decode($item->order, true),
                        'PrintTemplate' => $item->print_teplate,
                        'express_single' =>$item
                    ];
                    return [
                        'code' => ApiCode::CODE_SUCCESS,
                        'msg' => 'success',
                        'data' => $result
                    ];
                }
            }

            if (!empty($order->detailExpress)) {
                $orderCode = $order->order_no . "-" . count($order->detailExpress);
            } else {
                $orderCode = $order->order_no ;
            }


            //构造电子面单提交信息
            $eorder = [];
            $otherWhere = [];

            $this->delivery_account && $otherWhere = ['customer_account' => $this->delivery_account];
            $delivery = Delivery::findOne(array_merge([
                'express_id' => $express['id'],
                'is_delete' => 0,
                'mch_id' => $this->mch_id ?: \Yii::$app->user->identity->mch_id,
                'mall_id' => \Yii::$app->mall->id
            ], $otherWhere));

            if ($delivery) {
                $pay_type = 3;
                $eorder['CustomerName'] = $delivery->customer_account;
                $eorder['CustomerPwd'] = $delivery->customer_pwd;
                $eorder['SendSite'] = $delivery->outlets_code;
                $eorder['MonthCode'] = $delivery->month_code;
            } else {
                $pay_type = 1;
                $delivery = CommonOption::get(Option::NAME_DELIVERY_DEFAULT_SENDER, \Yii::$app->mall->id, 'app');
            }

            if (!$delivery) {
                throw new \Exception('请先设置发件人信息');
            }

            $eorder["ShipperCode"] = $express['code'];
            $eorder["OrderCode"] = $orderCode;
            $eorder["PayType"] = $pay_type;
            $eorder["ExpType"] = 1;
            $eorder["IsReturnPrintTemplate"] = 1;
            $eorder["Quantity"] = 1; //包裹数(最多支持30件)

            $sender = [];
            $sender["Company"] = $delivery->company;
            $sender["Name"] = $delivery->name;
            $sender["Mobile"] = $delivery->mobile ?: $delivery->tel;
            $sender["ProvinceName"] = $delivery->province;
            $sender["CityName"] = $delivery->city;
            $sender["ExpAreaName"] = $delivery->district;
            $sender["Address"] = $delivery->address;
            $sender["PostCode"] = $delivery->zip_code;


            //可能不严谨
            $address_data = explode(' ', $order->address);
            $address = ['province' => '空', 'city' => '空', 'district' => '空', 'detail' => $order->address];

            $receiver = [];
            $receiver["Name"] = $order->name;
            $receiver["Mobile"] = $order->mobile;
            $receiver["ProvinceName"] = $address_data[0] ?: '空';
            $receiver["CityName"] = $address_data[1] ?: '空';
            $receiver["ExpAreaName"] = $address_data[2] ?: '空';
            $receiver["Address"] = str_replace(PHP_EOL, '', $address_data[3] ?: $order->address);
            $receiver["PostCode"] = $this->zip_code;

            if (isset($delivery['is_goods']) && $delivery->is_goods) {
                foreach ($order->detail as $v) {
                    $goods_attr = \Yii::$app->serializer->decode($v['goods_info'])['goods_attr'];
                    $commodityOne = [];
                    $commodityOne["GoodsName"] = $goods_attr['name'];
                    $commodityOne["GoodsCode"] = "";
                    $commodityOne["Goodsquantity"] = $v->num;
                    $commodityOne["GoodsPrice"] = $goods_attr['price'];
                    $commodityOne["GoodsWeight"] = $goods_attr['weight'];
                    $commodityOne['GoodsDesc'] = "";
                    $commodityOne['GoodsVol'] = "";
                    $commodity[] = $commodityOne;
                }
            } else {
                //订单商品信息
                $commodityOne = [];
                $commodityOne["GoodsName"] = '商品';
                $commodityOne["GoodsCode"] = "";
                $commodityOne["Goodsquantity"] = "";
                $commodityOne["GoodsPrice"] = "";
                $commodityOne["GoodsWeight"] = "";
                $commodityOne['GoodsDesc'] = "";
                $commodityOne['GoodsVol'] = "";
                $commodity[] = $commodityOne;
            }

            $eorder["Sender"] = $sender;
            $eorder["Receiver"] = $receiver;
            $eorder["Commodity"] = $commodity;

            $eorder['TemplateSize'] = $delivery['template_size'] ?? '';
            $eorder['IsSendMessage'] = $delivery['is_sms'] ?? 0;

            //京东物流
            if ($eorder["ShipperCode"] === 'JD') {
                $eorder["ExpType"] = 6;
            }
            //调用电子面单
            $jsonParam = \Yii::$app->serializer->encode($eorder);
            $jsonResult = \Yii::$app->kdOrder->submitEOrder($jsonParam);

            //解析电子面单返回结果
            $result = json_decode($jsonResult, true);
            if (isset($result["ResultCode"]) && $result["ResultCode"] == "100" || $result["ResultCode"] == '106') {
                $form = new OrderExpressSingle();
                $form->mall_id = \Yii::$app->mall->id;
                $form->order_id = $order->id;
                $form->ebusiness_id = $result['EBusinessID'];
                $form->order = \Yii::$app->serializer->encode($result['Order']);
                $form->print_teplate = $result['PrintTemplate'];
                $form->is_delete = 0;
                $form->express_code = $express['code'];
                $form->save();

                $result['express_single'] = $form;

                return [
                    'code' => ApiCode::CODE_SUCCESS,
                    'msg' => 'success',
                    'data' => $result
                ];
            } else {
                return [
                    'code' => ApiCode::CODE_ERROR,
                    'msg' => $result['Reason'],
                    'data' => $result
                ];
            }
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
