<?php
/**
 * @copyright (c)天幕网络
 * @author Lu Wei
 * @link http://www.67930603.top/
 * Created by IntelliJ IDEA
 * Date Time: 2019/1/4 18:21:00
 */


namespace app\core\cloud;


use app\forms\common\CommonOption;
use app\models\AdminInfo;
use app\models\CorePlugin;
use GuzzleHttp\Client;
use \Exception;
use yii\base\Component;

class CloudBase extends Component
{
    public $classVersion = '4.2.10';
    public $urlEncodeQueryString = true;
    // todo 开发完成此处请切换
    private $xBaseUrl = 'aHR0cHM6Ly9iZGF1dGguempoZWppYW5nLmNvbQ=='; // 正式
//    private $xBaseUrl = 'aHR0cDovL2xvY2FsaG9zdC9iZGF1dGgvd2Vi'; // 开发
    private $xLocalAuthInfo;
    //优化多次DB查询使用
    private $cps;
    private $ad_count;

    /**
     * @param $url
     * @param array $params
     * @return mixed
     * @throws CloudException
     * @throws CloudNotLoginException
     * @throws Exception
     */
    public function httpGet($url, $params = [])
    {
        $url = $this->getUrl($url);
        $url = $this->appendParams($url, $params);
        $body = $this->curlRequest('get', $url);
        $res = json_decode($body, true);
        if (!$res) {
            throw new \Exception('Cloud response body `' . $body . '` could not be decode.');
        }
        if ($res['code'] !== 0) {
            if ($res['code'] === -1) {
                throw new CloudNotLoginException($res['msg']);
            } else {
                throw new CloudException($res['msg'], $res['code'], null, $res);
            }
        }
        return $res['data'];
    }

    /**
     * @param $url
     * @param array $params
     * @return mixed
     * @throws CloudException
     * @throws Exception
     */
    public function httpPost($url, $params = [], $data = [])
    {
        $url = $this->getUrl($url);
        $url = $this->appendParams($url, $params);
        $body = $this->curlRequest('post', $url, $data);
        $res = json_decode($body, true);
        if (!$res) {
            throw new \Exception('Cloud response body `' . $body . '` could not be decode.');
        }
        if ($res['code'] !== 0) {
            throw new CloudException($res['msg'], $res['code'], null, $res);
        }
        return $res['data'];
    }

    private function getUrl($url)
    {
        if (mb_stripos($url, 'http') === 0) {
            return $url;
        }
        $url = mb_stripos($url, '/') === 0 ? mb_substr($url, 1) : $url;
        $baseUrl = base64_decode($this->xBaseUrl);
        $baseUrl = mb_stripos($baseUrl, '/') === (mb_strlen($baseUrl) - 1) ? $baseUrl : $baseUrl . '/';
        return $baseUrl . $url;
    }

    private function appendParams($url, $params = [])
    {
        if (!is_array($params)) {
            return $url;
        }
        if (!count($params)) {
            return $url;
        }
        $url = trim($url, '?');
        $url = trim($url, '&');
        $queryString = $this->paramsToQueryString($params);
        if (mb_stripos($url, '?')) {
            return $url . '&' . $queryString;
        } else {
            return $url . '?' . $queryString;
        }
    }

    private function paramsToQueryString($params = [])
    {
        if (!is_array($params)) {
            return '';
        }
        if (!count($params)) {
            return '';
        }
        $str = '';
        foreach ($params as $k => $v) {
            if ($this->urlEncodeQueryString) {
                $v = urlencode($v);
            }
            $str .= "{$k}={$v}&";
        }
        return trim($str, '&');
    }

    public function getLocalAuthInfo()
    {
        if ($this->xLocalAuthInfo) {
            return $this->xLocalAuthInfo;
        }
        $this->xLocalAuthInfo = CommonOption::get('local_auth_info');
        if (!$this->xLocalAuthInfo) {
            $this->xLocalAuthInfo = [];
        }
        return $this->xLocalAuthInfo;
    }

    public function setLocalAuthInfo($data)
    {
        return CommonOption::set('local_auth_info', $data);
    }

    public function getLocalAuthDomain()
    {
        $localAuthInfo = $this->getLocalAuthInfo();
        if ($localAuthInfo && !empty($localAuthInfo['domain'])) {
            return $localAuthInfo['domain'];
        }
        return \Yii::$app->request->hostName;
    }

    /**
     * @return Client
     */
    protected function getClient()
    {
        try {
            $version = app_version();
        } catch (\Exception $e) {
            $version = '0.0.0';
        }
        $client = new Client([
            'verify' => false,
            'headers' => [
                'X-Domain' => \Yii::$app->request->hostName,
                'X-Version' => $version,
                'X-Plugins' => $this->getPluginsJson(),
                'X-Account-Num' => $this->getAccountNum(),
            ],
        ]);
        return $client;
    }

    public function download($url, $file)
    {
        if (!is_dir(dirname($file))) {
            if (!make_dir(dirname($file))) {
                throw new CloudException('无法创建目录，请检查文件写入权限。');
            }
        }
        $fp = fopen($file, 'w+');
        if ($fp === false) {
            throw new CloudException('无法保存文件，请检查文件写入权限。');
        }

        $client = new Client([
            'verify' => false,
            'stream' => true,
        ]);
        $response = $client->get($url);
        $body = $response->getBody();
        while (!$body->eof()) {
            fwrite($fp, $body->read(1024));
        }
        fclose($fp);
        return $file;
    }

    private function getPluginsJson()
    {
        $list = [];
        try {
            if (!$this->cps) {
                $cps = CorePlugin::find()->select('name')->where(['is_delete' => 0,])->all();
                $this->cps = $cps;
            } else {
                $cps = $this->cps;
            }
            foreach ($cps as $cp) {
                $list[] = $cp->name;
            }
        } catch (\Exception $exception) {
        }
        return json_encode($list);
    }

    private function getAccountNum()
    {
        $count = 0;
        try {
            if ($this->ad_count) {
                $count = AdminInfo::find()->where([
                    'AND',
                    ['!=', 'user_id', 1],
                    ['is_delete' => 0,],
                    ['we7_user_id' => 0,],
                ])->count();
                $count = $count ? intval($count) : 0;
                $this->ad_count = $count;
            } else {
                $count = $this->ad_count;
            }
        } catch (\Exception $exception) {
        }
        return $count;
    }

    /**
     * @param string $method get or post
     * @param string $url request url
     * @param array | null $data the post data
     * @return bool|string
     * @throws \Exception
     */
    private function curlRequest($method, $url, $data = null)
    {
        try {
            $version = app_version();
        } catch (\Exception $e) {
            $version = '0.0.0';
        }
        $requestHeader = [
            'X-Domain: ' . \Yii::$app->request->hostName,
            'X-Version: ' . $version,
            'X-Plugins: ' . $this->getPluginsJson(),
            'X-Account-Num: ' . $this->getAccountNum(),
            'X-Request-Info: ' . base64_encode(json_encode([
                'current_dir' => dirname(__DIR__),
            ])),
            'X-Type: 1',
        ];
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 600);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $requestHeader);
        if (strtolower($method) === 'post') {
            curl_setopt($ch, CURLOPT_POST, 1);
            if ($data) {
                $data = is_string($data) ? $data : http_build_query($data);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            }
        }
        $content = curl_exec($ch);
        $error = curl_error($ch);
        $errno = curl_errno($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);
        if ($errno) {
            throw new Exception('Cloud: ' . $error);
        }
        return $content;
    }
}
