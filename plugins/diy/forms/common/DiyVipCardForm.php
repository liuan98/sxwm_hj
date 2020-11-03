<?php
/**
 * link: http://www.67930603.top/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: Alpha
 */

namespace app\plugins\diy\forms\common;


use app\models\Model;
use app\plugins\check_in\models\CheckInAwardConfig;
use app\plugins\check_in\models\CheckInUser;
use app\plugins\vip_card\Plugin;

class DiyVipCardForm extends Model
{
    public function getVipCard()
    {
        $plugin = new Plugin();
        return $plugin->getAppConfig();
    }
}
