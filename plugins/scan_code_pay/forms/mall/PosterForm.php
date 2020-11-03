<?php
/**
 * link: http://www.67930603.top/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: Alpha
 */

namespace app\plugins\scan_code_pay\forms\mall;


use app\core\response\ApiCode;
use app\forms\api\poster\BasePoster;
use app\forms\common\CommonQrCode;
use app\forms\common\grafika\GrafikaOption;
use app\plugins\scan_code_pay\forms\common\CommonScanCodePaySetting;
use yii\helpers\ArrayHelper;

class PosterForm extends GrafikaOption implements BasePoster
{
    public function get()
    {
        $common = new CommonScanCodePaySetting();
        $setting = $common->getSetting();

        if (isset($setting['poster']['bg_pic']['url']) && !$setting['poster']['bg_pic']['url']) {
            $setting['poster']['bg_pic']['url'] = \Yii::$app->request->hostInfo . \Yii::$app->request->baseUrl . '/statics/img/mall/poster_bg.png';
        }

        if (isset($setting['poster']['qr_code'])) {
            $qrCode = new CommonQrCode();
            $qrCode->appPlatform = 'all';
            $code = $qrCode->getQrCode([], 240, 'plugins/scan_code/index/index');
            $code_path = self::saveTempImage($code['wechat']['file_path']);
            if ($setting['poster']['qr_code']['type'] == 1) {
                $code_path = self::avatar($code_path, $this->temp_path, $setting['poster']['qr_code']['size'], $setting['poster']['qr_code']['size']);
            }
            $setting['poster']['qr_code']['file_path'] = $this->destroyList($code_path);
        }

        $option = ArrayHelper::toArray($setting['poster']);
        $this->setFile($option);
        $editor = $this->getPoster($option);

        if (strstr(\Yii::$app->request->hostInfo, 'https') == false) {
            $editor->qrcode_url = str_replace('https://', 'http://', $editor->qrcode_url);
        }

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'url' => $editor->qrcode_url
            ]
        ];
    }
}