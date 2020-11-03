<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/5/28
 * Time: 17:04
 * @copyright: (c)2019 天幕网络
 * @link: http://www.67930603.top
 */

namespace app\jobs;


use app\forms\mall\statistics\InitDataForm;
use app\models\Mall;
use yii\base\BaseObject;
use yii\queue\JobInterface;

class ChangeShareLevelJob extends BaseObject implements JobInterface
{
    public $mall;

    public function execute($queue)
    {
        $mall = Mall::findOne($this->mall->id);
        \Yii::$app->setMall($mall);
        \Yii::error('--我进来啦--');
        $t = \Yii::$app->db->beginTransaction();
        try {
            $form = new InitDataForm();
            $form->share();
            $t->commit();
        } catch (\Exception $exception) {
            $t->rollBack();
            \Yii::error($exception);
        }
    }
}
