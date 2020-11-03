<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/5/23
 * Time: 11:37:00
 * @copyright: (c)2019 天幕网络
 * @link: http://www.67930603.top
 */

namespace app\core\cloud;


class CloudCollect extends CloudBase
{
    public $classVersion = '4.2.10';

    public function collect($id)
    {
        $api = "/mall/copy/index";
        return $this->httpGet($api, ['vid' => $id]);
    }
}
