<?php
/**
 * link: http://www.67930603.top/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\forms\mall\order;

use app\core\response\ApiCode;
use app\forms\common\template\tplmsg\Tplmsg;
use app\models\Order;
use app\models\Express;
use app\models\Model;
use app\events\OrderSendEvent;
use app\models\OrderDetail;
use app\models\OrderDetailExpressRelation;

class OrderSendForm extends Model
{
    public $order_id;
    public $is_express;
    public $express;
    public $express_no;
    public $words;

    public $arrCSV;
    public $url;

    public function rules()
    {
        return [
            [['order_id', 'is_express'], 'required'],
            [['order_id', 'is_express'], 'integer'],
            [['words', 'express_no', 'express', 'url'], 'string'],
            [['words', 'express', 'express_no'], 'default', 'value' => ''],
            [['arrCSV'], 'trim'],
        ];
    }

    public function batchDetail()
    {
        $express_list = Express::getExpressList();
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => [
                'express_list' => $express_list
            ],
        ];
    }

    public function batchSave()
    {
        try {
            $this->validateExpress();
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
            ];
        };

        if (!$this->url) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '模板不能为空',
            ];
        }

        $arrCSV = array();
        if (($handle = fopen($this->url, "r")) !== false) {
            $key = 0;
            while (($data = fgetcsv($handle, 0, ",")) !== false) {
                $c = count($data);
                for ($x = 0; $x < $c; $x++) {
                    $arrCSV[$key][$x] = trim($data[$x]);
                }
                $key++;
            }
            fclose($handle);
        }
        unset($arrCSV[0]);

        $info = $this->batch($arrCSV);
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '操作成功',
            'data' => [
                'list' => $info
            ],
        ];
    }

    private function batch($arrCSV)
    {
        $empty = [];  //是否存在
        $error = [];   //操作失败
        $cancel = [];  //是否取消
        $offline = []; //到店自提
        $send = [];  //是否发货
        $success = []; //是否成功
        $pay = []; //未支付(已发货)

        foreach ($arrCSV as $v) {
            /** @var Order $order */
            $order = Order::find()->where([
                'is_delete' => 0,
                'mall_id' => \Yii::$app->mall->id,
                'order_no' => $v[1],
                'mch_id' => \Yii::$app->user->identity->mch_id,
            ])->with('detail')->one();

            if (!$order) {
                $empty[] = $v[1];
                continue;
            }

            if ($order->status == 0) {
                continue;
            }

            if ($order->cancel_status != 0) {
                $cancel[] = $v[1];
                continue;
            }
            if ($order->is_send) {
                $send[] = $v[1];
                continue;
            }
            if ($order->send_type == 1) {
                $offline[] = $v[1];
                continue;
            }
            if ($order->is_pay == 0 && $order->pay_type != 2) {
                $pay[] = $v[1];
            }

            if ($order->send_type == 1) {
                $order->send_type = 0;
            }

            $detailIds = [];
            /** @var OrderDetail $detail */
            foreach ($order->detail as $detail) {
                $relation = OrderDetailExpressRelation::find()->where(['order_detail_id' => $detail->id, 'is_delete' => 0, 'mall_id' => \Yii::$app->mall->id])->one();
                if (!$relation) {
                    $detailIds[] = $detail->id;
                }
            }

            if (!$order->save()) {
                $error[] = $v[1];
            } else {
                $orderSendForm = new \app\forms\common\order\OrderSendForm();
                $orderSendForm->order_id = $order->id;
                $orderSendForm->is_express = 1;
                $orderSendForm->order_detail_id = $detailIds;
                $orderSendForm->express = $this->express;
                $orderSendForm->express_no = $v[2];
                $orderSendForm->merchant_remark = '';
                $orderSendForm->saveOrderDetailExpress();
                $success[] = $v[1];
            }
        };

        $data = [];
        $max = max(count($empty), count($error), count($cancel), count($send), count($offline), count($pay), count($success));
        for ($i = 0, $k = 0; $i < $max; $k++, $i++) {
            $data[$k]['empty'] = $empty[$k] ?? '';
            $data[$k]['cancel'] = $cancel[$k] ?? '';
            $data[$k]['send'] = $send[$k] ?? '';
            $data[$k]['offline'] = $offline[$k] ?? '';
            $data[$k]['pay'] = $pay[$k] ?? '';
            $data[$k]['error'] = $error[$k] ?? '';
            $data[$k]['success'] = $success[$k] ?? '';
        }
        return $data;
    }

    private function validateExpress()
    {
        $expressList = Express::getExpressList();
        $sentinel = false;
        foreach ($expressList as $value) {
            if ($value['name'] == $this->express) {
                $sentinel = true;
                break;
            }
        }
        if (!$sentinel && $this->is_express) {
            throw new \Exception('快递公司错误');
        }
    }
}
