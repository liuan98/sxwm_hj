<?php
/**
 * link: http://www.67930603.top/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: Alpha
 */

namespace app\forms\api\order;

use app\core\response\ApiCode;
use app\models\Mall;
use app\models\Model;
use app\models\Order;
use app\models\OrderDetailExpress;
use Hejiang\Express\Exceptions\TrackingException;
use Hejiang\Express\Trackers\TrackerInterface;
use Hejiang\Express\Waybill;

class OrderExpressForm extends Model
{
    public $mobile; // 手机号，用于顺丰查信息
    public $express;
    public $express_no;
    public $customer_name;//京东物流特殊要求字段，商家编码

    public function rules()
    {
        return [
            [['customer_name', 'mobile'], 'string'],
            [['express', 'express_no'], 'required'],
        ];
    }

    public function search()
    {
        if (!$this->validate()) {
            return $this->errorResponse;
        }
        if ($this->customer_name === 'undefined') $this->customer_name = null;
        try {
            if (substr_count($this->express, '京东') && empty($this->customer_name)) {
                throw new \Exception('京东物流必须填写京东商家编码');
            }
            $expressData = $this->getExpressData($this->express, $this->express_no);
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => 'success',
                'data' => [
                    'express' => $expressData,
                    'order' => [
                        'express' => $this->express,
                        'express_no' => $this->express_no,
                    ],
                ]
            ];
        } catch (\Exception $exception) {
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => $exception->getMessage(),
                'data' => [
                    'express' => null,
                    'order' => [
                        'express' => $this->express,
                        'express_no' => $this->express_no,
                    ],
                ]
            ];
        }
    }

    private function transExpressName($name)
    {
        $staticNameList = [
            '百世快运',
            '京东快运',
        ];
        if (!$name) {
            return false;
        }
        if (in_array($name, $staticNameList)) {
            return $name;
        }
        $append_list = [
            '快递',
            '快运',
            '物流',
            '速运',
            '速递',
        ];
        foreach ($append_list as $append) {
            $name = str_replace($append, '', $name);
        }

        $name_map_list = [
            '邮政快递包裹' => '邮政',
            '邮政包裹信件' => '邮政',
        ];
        if (isset($name_map_list[$name])) {
            $name = $name_map_list[$name];
        }
        return $name;
    }

    private function getKuaidiniaoConfig()
    {
        $mall = (new Mall())->getMallSetting(['kdniao_mch_id', 'kdniao_api_key']);
        if (!$mall || !$mall['kdniao_mch_id'] || !$mall['kdniao_api_key']) {
            return ['', ''];
        }
        $mch_id = $mall['kdniao_mch_id'];
        $api_key = $mall['kdniao_api_key'];
        return [$mch_id, $api_key];
    }


    private function getExpressData($expressName, $expressNo)
    {
        $statusMap = [
            -1 => '已揽件',
            0 => '已揽件',
            1 => '已发出',
            2 => '在途中',
            3 => '派件中',
            4 => '已签收',
            5 => '已自取',
            6 => '问题件',
            7 => '已退回',
            8 => '已退签',
        ];
        $waybillParams = [
            'class' => 'Hejiang\Express\Waybill',
            'id' => $expressNo,
            'express' => $this->transExpressName($expressName),
            'customerName' => $this->customer_name ? $this->customer_name : $this->getMobileLast4Num(),
        ];
        /** @var Waybill $waybill */
        $waybill = \Yii::createObject($waybillParams);
        $ExpressClassList = [
            'Hejiang\Express\Trackers\Kuaidiniao',
            'Hejiang\Express\Trackers\Kuaidiwang',
            // 'Hejiang\Express\Trackers\Kuaidi100',
        ];
        $list = [];
        $status = null;
        $statusText = null;
        foreach ($ExpressClassList as $class) {
            try {
                $classArgs = [
                    'class' => $class,
                ];
                if ($class == 'Hejiang\Express\Trackers\Kuaidiniao') {
                    list($EBusinessID, $AppKey) = $this->getKuaidiniaoConfig();
                    $classArgs['EBusinessID'] = $EBusinessID;
                    // $classArgs['EBusinessID'] = '';
                    $classArgs['AppKey'] = $AppKey;
                }
                /** @var TrackerInterface $tracker */
                $tracker = \Yii::createObject($classArgs);
                try {
                    $list = $waybill->getTraces($tracker)->toArray();
                    if (!is_array($list)) {
                        throw new \Exception('物流信息查询失败');
                    }
                    foreach ($list as &$item) {
                        $item['datetime'] = $item['time'];
                        unset($item['time']);
                    }
                } catch (TrackingException $ex) {
                    continue;
                }
                $status = $waybill->status;
                if (isset($statusMap[$waybill->status])) {
                    $statusText = $statusMap[$waybill->status];
                } else {
                    $statusText = '状态未知';
                }
                return [
                    'status' => $status,
                    'status_text' => $statusText,
                    'list' => $list,
                ];
            } catch (TrackingException $e) {
                continue;
            }
        }
        throw new \Exception('暂无物流信息');
    }

    /**
     * 获取订单收件人手机号最后4位
     * @return mixed|string|null
     */
    private function getMobileLast4Num()
    {
        if ($this->mobile) {
            $mobile = $this->mobile;
        } else {
            $order = null;
            $orderDetailExpress = OrderDetailExpress::find()->where([
                'express' => $this->express,
                'express_no' => $this->express_no,
            ])->orderBy('id DESC')->one();
            if ($orderDetailExpress) {
                $order = Order::findOne($orderDetailExpress->order_id);
            } else {
                $order = Order::find()
                    ->where([
                        'express' => $this->express,
                        'express_no' => $this->express_no,
                    ])->one();
            }
            $mobile = $order ? $order->mobile : null;
        }
        if (!$mobile || mb_strlen($mobile) < 4) {
            return '';
        }
        return mb_substr($mobile, 0 - 4);
    }
}
