<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/7/3
 * Time: 11:51
 * @copyright: (c)2019 天幕网络
 * @link: http://www.67930603.top
 */

namespace app\plugins\dianqilai\forms;



class TemplateForm extends \app\forms\common\template\TemplateForm
{
    protected function getDefault()
    {
        $iconUrlPrefix = \Yii::$app->request->hostInfo . \Yii::$app->request->baseUrl .
            '/statics/img/mall/tplmsg/';

        $newDefault = [
            [
                'name' => '咨询回复通知',
                'contact_tpl' => '',
                'tpl_name' => 'contact_tpl',
                'img_url' => [
                    'aliapp' => $iconUrlPrefix . 'aliapp/account_change_tpl.png',
                    'bdapp' => $iconUrlPrefix . 'bdapp/contact_tpl.png',
                    'ttapp' => $iconUrlPrefix . 'ttapp/contact_tpl.png',
                ],
                'platform' => ['aliapp', 'bdapp', 'ttapp'],
                'tpl_number' => [
                    'aliapp' => '(模板编号: AT0133)',
                    'bdapp' => '(模板编号：BD1941)',
                    'ttapp' => '',
                ]
            ]
        ];

        return $newDefault;
    }

    protected function getTemplateInfo()
    {
        return [
            'bdapp' => [
                'contact_tpl' => [
                    'id' => 'BD1941',
                    'keyword_id_list' => [1, 4, 5],
                    'title' => '咨询回复通知'
                ],
            ]
        ];
    }
}
