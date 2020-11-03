<?php

namespace app\forms\api;

use app\core\response\ApiCode;
use app\forms\common\CommonDelivery;
use app\models\Address;
use app\models\DistrictArr;
use app\models\Mall;
use app\models\Model;
use app\validators\PhoneNumberValidator;

class AddressForm extends Model
{
    public $id;
    public $name;
    public $limit;

    public $mobile;
    public $detail;
    public $is_default;

    public $province_id;
    public $city_id;
    public $district_id;
    public $latitude;
    public $longitude;
    public $location;
    public $hasCity;

    public function rules()
    {
        return [
            //            [['name', 'province_id', 'city_id', 'district_id', 'mobile', 'detail'], 'required'],
            [['name', 'province_id', 'mobile', 'detail'], 'required'],
            [['detail', 'hasCity'], 'string'],
            // [['id', 'province_id', 'city_id', 'district_id', 'is_default', 'limit'], 'integer'],
            [['province_id', 'is_default', 'limit'], 'integer'],
            [['is_default', ], 'default', 'value' => 0],
            [['name', 'mobile', 'latitude', 'longitude', 'location'], 'string', 'max' => 255],
            [['detail'], 'string', 'max' => 1000],
            [['latitude', 'longitude', 'location'], 'default', 'value' => ''],
            [['mobile'], PhoneNumberValidator::className(), 'when' => function($model) {
                $mall = Mall::findOne(['id' => \Yii::$app->mall->id]);
                $status = $mall->getMallSettingOne('mobile_verify');
                return $status == 1;
            }],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => '收货人',
            'province_id' => 'Province ID',
            'province' => '省份名称',
            'city_id' => 'City ID',
            'city' => '城市名称',
            'district_id' => 'District ID',
            'district' => '县区名称',
            'mobile' => '联系电话',
            'detail' => '详细地址',
            'latitude' => '定位地址',
            'longitude' => '定位地址',
            'location' => '定位地址',
        ];
    }

    public function autoAddressInfo()
    {
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '获取成功',
            'data' => file_get_contents('statics' . DIRECTORY_SEPARATOR . 'text' . DIRECTORY_SEPARATOR . 'auto_address.json')
        ];
    }

    //GET
    public function getList()
    {
        $this->limit = 20;
        $user_id = \Yii::$app->user->identity->id;

        $list = Address::find()->where([
            'is_delete' => 0,
            'user_id' => $user_id
        ])
        ->page($pagination, $this->limit)
        ->orderBy('is_default DESC,id DESC')
        ->asArray()
        ->all();

        $inPointList = [];
        $notInPointList = [];
        foreach ($list as $i => $item) {
            $list[$i]['address'] = $item['province'] . $item['city'] . $item['district'] . $item['detail'];
            if ($this->hasCity == 'true') {
                if (!$item['longitude'] || !$item['latitude']) {
                    $notInPointList[] = $list[$i];
                } else {
                    try {
                        $config = CommonDelivery::getInstance()->getConfig();
                        $range = $config['range'];
                        $point = [
                            'lng' => $item['longitude'],
                            'lat' => $item['latitude']
                        ];
                        if (is_point_in_polygon($point, $range)) {
                            $inPointList[] = $list[$i];
                        } else {
                            $notInPointList[] = $list[$i];
                        }
                    } catch (\Exception $exception) {
                        $notInPointList[] = $list[$i];
                    }
                }
            } else {
                $inPointList[] = $list[$i];
            }
        }

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => [
                'list' => $inPointList,
                'notInPointList' => $notInPointList,
            ]
        ];
    }
    
    public function detail()
    {
        $user_id = \Yii::$app->user->identity->id;
        $list = Address::findOne([
            'id' => $this->id,
            'is_delete' => 0,
            'user_id' => $user_id,
        ]);
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => [
                'list' => $list,
            ]
        ];
    }

    //DEFAULT
    public function default()
    {
        $user_id = \Yii::$app->user->identity->id;

        Address::updateAll(['is_default' => 0], [
            'is_delete' => 0,
            'user_id' => $user_id
        ]);
        $model = Address::findOne([
            'id' => $this->id,
            'is_delete' => 0,
            'user_id' => $user_id,
        ]);
        if (!$model) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '数据不存在或已删除',
            ];
        }
        $model->is_default = $this->is_default;
        $model->save();
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '处理成功'
        ];
    }
    
    //DELETE
    public function destroy()
    {
        $user_id = \Yii::$app->user->identity->id;
        $model = Address::findOne([
            'id' => $this->id,
            'is_delete' => 0,
            'user_id' => $user_id,
        ]);
        if (!$model) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '数据不存在或已删除',
            ];
        }
        $model->is_delete = 1;
        $model->deleted_at = date('Y-m-d H:i:s');
        $model->save();
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '删除成功'
        ];
    }

    //SAVE
    public function save()
    {
        // if (!$this->validate()) {
        //     return $this->getErrorResponse();
        // }

        $isRequiredPosition = \Yii::$app->mall->getMallSettingOne('is_required_position');
        if ($isRequiredPosition && \Yii::$app->appPlatform != APP_PLATFORM_TTAPP) {
            if ($this->longitude == '' || $this->latitude == '' || $this->location == '') {
                return [
                    'code' => ApiCode::CODE_ERROR,
                    'msg'  => '定位地址不能为空',
                ];
            }
        }

        $province = DistrictArr::getDistrict($this->province_id);
        // if (!$province) {
        //     return [
        //         'code' => ApiCode::CODE_ERROR,
        //         'msg'  => '省份数据错误，请重新选择',
        //     ];
        // }
    
        $city = DistrictArr::getDistrict($this->city_id);
        // if (!$city) {
        //     return [
        //         'code' => ApiCode::CODE_ERROR,
        //         'msg'  => '城市数据错误，请重新选择',
        //     ];
        // }
        
        $district = DistrictArr::getDistrict($this->district_id);
        // if (!$district) {
        //     return [
        //         'code' => ApiCode::CODE_ERROR,
        //         'msg'  => '地区数据错误，请重新选择',
        //     ];
        // }

        $user_id = \Yii::$app->user->identity->id;
        $address = Address::findOne([
            'id' => $this->id,
            'is_delete' => 0,
            'user_id' => $user_id
        ]);

        if (!$address) {
            $address = new Address();
            $address->is_delete = 0;
            $address->user_id = $user_id;
        }

        $address->attributes = $this->attributes;
        $address->province = $province->name;
        $address->city = $city->name;
        $address->district = $district->name;
        $address->province_id = $this->province_id?$this->province_id:'';
        $address->city_id = $this->city_id?$this->city_id:'';
        $address->district_id = $this->district_id?$this->district_id:'';
        // $address->id = $this->id;
        $list = Address::find()->where(['user_id' => $user_id, 'is_delete' => 0])->all();
        if (!$list || count($list) <= 0) {
            $address->is_default = 1;
        }
        if ($address->save()) {
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg'  => '保存成功',
            ];
        } else {
            return $this->getErrorResponse($address);
        }
    }
}
