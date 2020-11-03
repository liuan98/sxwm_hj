<?php
/**
 * 本项目所有web端控制器的基类
 *
 * @copyright (c)天幕网络
 * @author Lu Wei
 * @link http://www.67930603.top/
 * Created by IntelliJ IDEA
 * Date Time: 2018/10/30 12:00
 */


namespace app\controllers;


class Controller extends \yii\web\Controller
{
    public function init()
    {
        parent::init();
        if (\Yii::$app->request->get('_layout')) {
            $this->layout = \Yii::$app->request->get('_layout');
        }
    }
}
