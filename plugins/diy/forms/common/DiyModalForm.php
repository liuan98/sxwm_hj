<?php
/**
 * link: http://www.67930603.top/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: Alpha
 */

namespace app\plugins\diy\forms\common;


use app\models\Model;
use app\plugins\fxhb\models\FxhbActivity;

class DiyModalForm extends Model
{
    public function getModal()
    {
        try {
            // 暂时只有裂变红包
            return \Yii::$app->plugin->getPlugin('fxhb')->getHomePage('api');
        } catch (\Exception $exception) {
            return [];
        }
    }
}
