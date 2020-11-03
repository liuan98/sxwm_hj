<?php
/**
 * link: http://www.67930603.top/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\plugins\booking\forms\mall;

use app\core\response\ApiCode;
use app\forms\common\CommonOptionP;
use app\models\Model;
use app\plugins\booking\forms\common\CommonBooking;
use app\plugins\booking\forms\common\CommonOption;
use app\plugins\booking\models\BookingSetting;

class BookingSettingForm extends Model
{
    public $id;
    public $is_share;
    public $is_sms;
    public $is_mail;
    public $is_print;
    public $is_cat;
    public $form_data;
    public $is_form;
    public $payment_type;
    public $goods_poster;

    public function rules()
    {
        return [
            [['is_cat'], 'required'],
            [['is_share', 'is_sms', 'is_mail', 'is_print', 'is_cat', 'is_form'], 'integer'],
            [['form_data', 'goods_poster'], 'trim'],
            [['payment_type'], 'safe']
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'mall_id' => 'Mall ID',
            'is_share' => '是否开启分销',
            'is_sms' => '是否开启短信通知',
            'is_mail' => '是否开启邮件通知',
            'is_print' => '是否开启订单打印',
            'is_cat' => 'Is Cat',
            'form_data' => 'form默认表单',
            'payment_type' => '支付方式',
            'goods_poster' => '自定义海报',
        ];
    }

    public function getList()
    {
        $setting = CommonBooking::getSetting();
        $setting = \yii\helpers\ArrayHelper::toArray($setting);
        $setting['goods_poster'] = (new CommonOptionP())->poster($setting['goods_poster'], CommonOption::getPosterDefault());
        foreach ($setting['form_data'] as &$v) {
            $v['is_required'] = $v['is_required'] == 1;
        }

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '',
            'data' => $setting
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        };

        try {
            $this->checkData();
            $model = BookingSetting::findOne([
                'mall_id' => \Yii::$app->mall->id,
            ]);
            if (!$model) {
                $model = new BookingSetting();
            }

            if (!$this->payment_type || empty($this->payment_type)) {
                throw new \Exception('请选择支付方式');
            }
            $model->attributes = $this->attributes;
            $model->form_data = json_encode($this->form_data);
            $model->payment_type = json_encode($this->payment_type);
            $model->mall_id = \Yii::$app->mall->id;
            $model->goods_poster = \Yii::$app->serializer->encode((new CommonOptionP())->saveEnd($this->goods_poster));
            $model->save();
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功',
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
            ];
        }
    }

    // 检测数据
    public function checkData()
    {
        if (!$this->form_data) {
            return;
        }
        foreach ($this->form_data as $item) {
            if (!$item['name']) {
                throw new \Exception('请检查信息是否填写完整x02');
            }
            if (isset($item['list'])) {
                foreach ($item['list'] as $item2) {
                    if (!$item2['label']) {
                        throw new \Exception('请检查信息是否填写完整x03');
                    }
                }
            }
        }
    }
}
