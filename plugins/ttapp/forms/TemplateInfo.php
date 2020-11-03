<?php
/**
 * @copyright ©2019 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2019/9/23
 * Time: 15:17
 */

namespace app\plugins\ttapp\forms;

class TemplateInfo
{
    private $data;

    public function __construct($type,$info)
    {
        foreach ($info as $k => $v) {
            unset($info[$k]['color']);
        }

        $this->data =  $info;
    }

    public function getData()
    {
        return $this->data;
    }
}