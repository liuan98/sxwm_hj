<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/3/18
 * Time: 16:03
 * @copyright: (c)2019 天幕网络
 * @link: http://www.67930603.top
 */

namespace app\plugins\bargain\forms\api;


use app\core\response\ApiCode;
use app\plugins\bargain\forms\common\CommonSetting;

class IndexForm extends ApiModel
{
    public function search()
    {
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => CommonSetting::getCommon()->getList()
        ];
    }
}
