<?php
/**
 * link: http://www.67930603.top/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\forms\common\grafika;

use app\forms\common\CommonQrCode;
use app\models\UserInfo;

trait CustomizeFunction
{
    /**
     * @param array $option 海报配置
     * @param array $params 小程序码配置
     * @param GrafikaOption $model
     * @return string 缓存路径
     * @throws \Exception
     */
    public function qrcode(array $option, array $params, GrafikaOption $model): string
    {
        $code = (new CommonQrCode())->getQrCode($params[0], $params[1], $params[2]);
        $code_path = self::saveTempImage($code['file_path']);
        if ($option['qr_code']['type'] == 1) {
            $code_path = self::avatar($code_path, $model->temp_path, $option['qr_code']['size'], $option['qr_code']['size']);
        }
        return $model->destroyList($code_path);
    }

    /**
     * @param GrafikaOption $model
     * @return string
     */
    public static function head(GrafikaOption $model): string
    {
        $user = UserInfo::findOne(['user_id' => \Yii::$app->user->id]);
        $avatar = self::avatar(self::saveTempImage($user->avatar, $model->default_avatar_url), $model->temp_path, 0, 0);
        return $model->destroyList($avatar);
    }
}