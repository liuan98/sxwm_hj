<?php
/**
 * @copyright (c)天幕网络
 * @author Lu Wei
 * @link http://www.67930603.top/
 * Created by IntelliJ IDEA
 * Date Time: 2019/2/20 17:17
 */


namespace app\controllers\admin;


class AppController extends AdminController
{
    public function actionRecycle()
    {
        return $this->render('recycle');
    }
}
