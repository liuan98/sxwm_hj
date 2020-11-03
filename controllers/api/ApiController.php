<?php
/**
 * @copyright (c)天幕网络
 * @author Lu Wei
 * @link http://www.67930603.top/
 * Created by IntelliJ IDEA
 * Date Time: 2018/11/7 18:19
 */


namespace app\controllers\api;


use app\controllers\api\filters\BlackListFilter;
use app\controllers\api\filters\MallDisabledFilter;
use app\controllers\Controller;
use app\forms\common\share\CommonShare;
use app\models\Formid;
use app\models\Mall;
use app\models\User;
use app\models\We7App;
use yii\web\NotFoundHttpException;

class ApiController extends Controller
{

    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'disabled' => [
                'class' => MallDisabledFilter::class,
            ],
            'blackList' => [
                'class' => BlackListFilter::class
            ]
        ]);
    }

    public function init()
    {
        parent::init();
        $this->enableCsrfValidation = false;
        $this->setMall()->login()->saveFormIdList()->bindParent();
    }

    private function setMall()
    {
        $acid = \Yii::$app->request->get('_acid');
        if ($acid && $acid > 0) {
            $we7app = We7App::findOne([
                'acid' => $acid,
                'is_delete' => 0,
            ]);
            $mallId = $we7app ? $we7app->mall_id : null;
        } else {
            $mallId = \Yii::$app->request->get('_mall_id');
        }
        $mall = Mall::findOne([
            'id' => $mallId,
            'is_delete' => 0,
            'is_recycle' => 0,
        ]);
        if (!$mall) {
            throw new NotFoundHttpException('商城不存在，id = ' . $mallId);
        }
        \Yii::$app->setMall($mall);
        return $this;
    }

    private function login()
    {
        $headers = \Yii::$app->request->headers;
        $accessToken = empty($headers['x-access-token']) ? null : $headers['x-access-token'];
        if (!$accessToken) {
            return $this;
        }
        $user = User::findOne([
            'access_token' => $accessToken,
            'mall_id' => \Yii::$app->mall->id,
            'is_delete' => 0,
        ]);
//        \Yii::$app->setMall(Mall::findOne(2));
//        $user = User::findOne(311837);

        if ($user) {
            \Yii::$app->user->login($user);
        }
        return $this;
    }

    private function saveFormIdList()
    {
        if (\Yii::$app->user->isGuest) {
            return $this;
        }
        if (empty(\Yii::$app->request->headers['x-form-id-list'])) {
            return $this;
        }
        $rawData = \Yii::$app->request->headers['x-form-id-list'];
        $list = json_decode($rawData, true);
        if (!$list || !is_array($list) || !count($list)) {
            return $this;
        }
        foreach ($list as $item) {
            $formid = new Formid();
            $formid->user_id = \Yii::$app->user->id;
            $formid->form_id = $item['value'];
            $formid->remains = $item['remains'];
            $formid->expired_at = $item['expires_at'];
            $formid->save();
        }
        return $this;
    }

    private function bindParent()
    {
        if (\Yii::$app->user->isGuest) {
            return $this;
        }
        $headers = \Yii::$app->request->headers;
        $userId = empty($headers['x-user-id']) ? null : $headers['x-user-id'];
        if (!$userId) {
            return $this;
        }
        $common = CommonShare::getCommon();
        $common->mall = \Yii::$app->mall;
        $common->user = \Yii::$app->user->identity;
        try {
            $common->bindParent($userId, 1);
        } catch (\Exception $exception) {
            \Yii::error($exception->getMessage());
            $userInfo = $common->user->userInfo;
            $userInfo->temp_parent_id = $userId;
            $userInfo->save();
        }
        return $this;
    }
}
