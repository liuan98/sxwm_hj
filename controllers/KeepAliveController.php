<?php
/**
 * @copyright (c)天幕网络
 * @author Lu Wei
 * @link http://www.67930603.top/
 * Created by IntelliJ IDEA
 * Date Time: 2019/1/29 16:46
 */


namespace app\controllers;


use app\controllers\behaviors\LoginFilter;

class KeepAliveController extends Controller
{
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'loginFilter' => [
                'class' => LoginFilter::class,
            ],
        ]);
    }

    public function actionIndex()
    {
    }
}
