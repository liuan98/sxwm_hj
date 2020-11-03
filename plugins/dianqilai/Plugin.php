<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/7/3
 * Time: 11:42
 * @copyright: (c)2019 天幕网络
 * @link: http://www.67930603.top
 */

namespace app\plugins\dianqilai;


use app\plugins\dianqilai\forms\TemplateForm;

class Plugin extends \app\plugins\Plugin
{
    /**
     * 插件唯一id，小写英文开头，仅限小写英文、数字、下划线
     * @return string
     */
    public function getName()
    {
        return 'dianqilai';
    }

    /**
     * 插件显示名称
     * @return string
     */
    public function getDisplayName()
    {
        return '客服系统';
    }

    public function getMenus()
    {
        return [
            [
                'name' => '客服设置',
                'route' => 'plugin/dianqilai/mall/index/index',
                'icon' => 'el-icon-star-on',
            ],
            [
                'name' => '模板消息',
                'route' => 'plugin/dianqilai/mall/template/template',
                'icon' => 'el-icon-star-on',
            ],
        ];
    }

    public function getIndexRoute()
    {
        return 'plugin/dianqilai/mall/index/index';
    }

    public function getTemplateForm()
    {
        return new TemplateForm();
    }
}
