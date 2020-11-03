<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/3/11
 * Time: 17:20
 * @copyright: (c)2019 天幕网络
 * @link: http://www.67930603.top
 */

namespace app\core\mail;


use app\models\MailSetting;
use app\models\Mall;
use app\models\Order;
use yii\base\Component;
use yii\swiftmailer\Mailer;

/**
 * @property Mall $mall
 * @property Order $order
 */
class SendMail extends Component
{
    public $mall;
    public $mch_id = 0;
    public $order;
    public $mailSetting;

    /**
     * @param $view
     * @param $params
     * @return bool
     * 邮件发送配置
     */
    protected function send($view, $params)
    {
        $mailSetting = $this->mailSetting;
        $receive = str_replace("，", ",", $mailSetting->receive_mail);
        $receiveMail = explode(",", $receive);
        $messages = [];
        /* @var Mailer $mailer */
        $mailer = \Yii::$app->mailer;
        $mailer->transport = [
            'class' => 'Swift_SmtpTransport',
            'host' => 'smtp.qq.com',
            'username' => $mailSetting->send_mail,
            'password' => $mailSetting->send_pwd,
            'port' => '465',
            'encryption' => 'ssl',//    tls | ssl
        ];
        foreach ($receiveMail as $mail) {
            $compose = $mailer->compose($view, $params);
            $compose->setFrom($mailSetting->send_mail); //要发送给那个人的邮箱
            $compose->setTo($mail); //要发送给那个人的邮箱
            $compose->setSubject($mailSetting->send_name); //邮件主题
            $messages[] = $compose;
        }
        $mailer->sendMultiple($messages);
        return true;
    }

    /**
     * @return bool
     * 订单支付提醒
     */
    public function orderPayMsg()
    {
        try {
            $this->mailSetting = $this->getMailSetting();
            $this->send('order', [
                'order' => $this->order,
                'mall' => $this->mall
            ]);
            return true;
        } catch (\Exception $exception) {
            \Yii::error('--发送邮件：--' . $exception->getMessage());
            return false;
        }
    }

    public function test()
    {
        $this->send('test', []);
        return true;
    }

    public function getMailSetting()
    {
        $mailSetting = MailSetting::findOne([
            'mall_id' => $this->mall->id,
            'is_delete' => 0,
            'status' => 1,
            'mch_id' => $this->order->mch_id,
        ]);
        if (!$mailSetting) {
            throw new \Exception('商城未设置邮件发送');
        }
        return $mailSetting;
    }

    public function refundMsg()
    {
        try {
            $this->mailSetting = $this->getMailSetting();
            $this->send('refund', [
                'mall' => $this->mall
            ]);
            return true;
        } catch (\Exception $exception) {
            \Yii::error('--发送邮件：--' . $exception->getMessage());
            return false;
        }
    }
}
