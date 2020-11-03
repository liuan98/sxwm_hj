<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/4/24
 * Time: 15:43
 * @copyright: (c)2019 天幕网络
 * @link: http://www.67930603.top
 */

namespace app\plugins\diy\forms\mall;


use app\core\response\ApiCode;
use app\models\Mall;
use app\models\Model;
use app\plugins\diy\forms\common\CommonTemplate;

/**
 * @property Mall $mall
 */
class TemplateForm extends Model
{
    public $page;
    public $limit;

    public function rules()
    {
        return [
            [['page', 'limit'], 'integer'],
            [['page'], 'default', 'value' => 1],
            [['limit'], 'default' ,'value' => 20]
        ];
    }

    public function search()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $common = CommonTemplate::getCommon();
        $pagination = null;
        $list = $common->getList($pagination, $this->page, $this->limit);
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'list' => $list,
                'pagination' => $pagination
            ]
        ];
    }

    public function destroy($id)
    {
        try {
            $common = CommonTemplate::getCommon();
            $common->destroy($id);
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '删除成功'
            ];
        } catch (\Exception $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage()
            ];
        }
    }
}
