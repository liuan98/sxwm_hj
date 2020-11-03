<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/9/4
 * Time: 14:01
 * @copyright: (c)2019 天幕网络
 * @link: http://www.67930603.top
 */
?>
<style>
    @media screen and (min-width: 1370px) {
        .form-body {
            padding: 20px 0;
            background-color: #fff;
            margin-bottom: 20px;
            min-width: 1400px;
            padding-right: 50%;
        }
    }

    @media screen and (max-width: 1369px) {
        .form-body {
            padding: 20px 0;
            background-color: #fff;
            margin-bottom: 20px;
            padding-right: 20%;
        }
    }

    .set-el-button {
        padding: 0 !important;
        border: 0;
        margin: 0 5px;
    }

    .form-button {
        margin: 0 !important;
    }

    .form-button .el-form-item__content {
        margin-left: 0 !important;
    }

    .button-item {
        padding: 9px 25px;
    }

    .start_price .el-input-group__append {
        border: none;
    }

    .start_price .el-input-group__prepend {
        border: none;
    }
</style>
<div id="app" v-cloak>
    <el-card class="box-card" shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;"
             v-loading="cardLoading">
        <div slot="header">
            <div>
                <span>同城配送</span>
            </div>
        </div>

        <div class="form-body">
            <el-form :model="ruleForm" :rules="rules" size="small" ref="ruleForm" label-width="240px">
                <el-form-item label="发货地址" prop="address" required>
                    <app-map @map-submit="mapEvent"
                             :address="ruleForm.address.address"
                             :lat="ruleForm.address.latitude"
                             :long="ruleForm.address.longitude">
                        <template v-if="ruleForm.address && ruleForm.address.address">
                            <div flex="dir:left">
                                <div>{{ruleForm.address.address}}</div>
                                <el-button type="text">修改</el-button>
                            </div>
                        </template>
                        <el-button type="text" v-else>设置</el-button>
                    </app-map>
                </el-form-item>
                <el-form-item label="联系方式" prop="contact_way" required>
                    <el-input v-model="ruleForm.contact_way" placeholder="请输入联系方式"></el-input>
                </el-form-item>
                <el-form-item label="配送说明" prop="explain" required>
                    <el-input type="textarea" v-model="ruleForm.explain" :row="3" placeholder="请输入配送说明"></el-input>
                    <div>例如：周一至周六，上午9点到下午5点配送</div>
                </el-form-item>
                <el-form-item label="配送人员" prop="mobile">
                    <el-table :data="ruleForm.mobile" v-if="ruleForm.mobile && ruleForm.mobile.length > 0" border>
                        <el-table-column
                                prop="name"
                                label="姓名"
                                width="180">
                        </el-table-column>
                        <el-table-column
                                prop="mobile"
                                label="联系方式1"
                                width="180">
                        </el-table-column>
                        <el-table-column label="操作">
                            <template slot-scope="scope">
                                <el-button type="text" class="set-el-button" size="mini" circle
                                           @click="mobileClick(scope.$index)">
                                    <el-tooltip class="item" effect="dark" content="编辑" placement="top">
                                        <img src="statics/img/mall/edit.png" alt="">
                                    </el-tooltip>
                                </el-button>
                                <el-button type="text" class="set-el-button" size="mini" circle
                                           @click="mobileDestroy(scope.$index)">
                                    <el-tooltip class="item" effect="dark" content="删除" placement="top">
                                        <img src="statics/img/mall/del.png" alt="">
                                    </el-tooltip>
                                </el-button>
                            </template>
                        </el-table-column>
                    </el-table>
                    <el-button type="text" @click="mobileClick(-1)">+<span style="color: #353535;">新增配送人员</span>
                    </el-button>
                    <el-dialog
                            title="配送人员"
                            :visible.sync="dialogVisible"
                            width="30%">
                        <el-form label-width="120px" size="small">
                            <el-form-item label="姓名" required>
                                <el-input v-model="mobile.name" placeholder="请输入姓名"></el-input>
                            </el-form-item>
                            <el-form-item label="联系方式1" required>
                                <el-input v-model="mobile.mobile" placeholder="请输入联系方式1"></el-input>
                            </el-form-item>
                        </el-form>
                        <span slot="footer" class="dialog-footer">
                            <el-button @click="dialogVisible = false">取 消</el-button>
                            <el-button type="primary" @click="mobileConfirm">确 定</el-button>
                        </span>
                    </el-dialog>
                </el-form-item>
                <el-form-item label="运费叠加" prop="is_superposition">
                    <label slot="label">运费叠加
                        <el-tooltip class="item" effect="dark"
                                    content="运费叠加时，配送费是按照单件商品运费*商品数量来计算"
                                    placement="top">
                            <i class="el-icon-info"></i>
                        </el-tooltip>
                    </label>
                    <el-switch
                            v-model="ruleForm.is_superposition"
                            :active-value="1"
                            :inactive-value="0">
                    </el-switch>
                </el-form-item>
                <el-form-item label="起送金额">
                    <el-input class="start_price" placeholder="起送金额" type="number"
                              v-model="ruleForm.price_enable">
                        <template slot="append">元</template>
                    </el-input>
                </el-form-item>
                <el-form-item label="免配送费">
                    <el-form-item label="是否开启" label-width="90px">
                        <el-switch
                                v-model="ruleForm.is_free_delivery"
                                :active-value="1"
                                :inactive-value="0">
                        </el-switch>
                    </el-form-item>
                    <el-form-item label="满足金额" label-width="90px" v-if="ruleForm.is_free_delivery == 1">
                        <label slot="label">满足金额
                            <el-tooltip class="item" effect="dark"
                                        content="商品售价满足该金额则免配送费"
                                        placement="top">
                                <i class="el-icon-info"></i>
                            </el-tooltip>
                        </label>
                        <el-input class="start_price" placeholder="满足金额" type="number"
                                  v-model="ruleForm.free_delivery" style="width: 200px;">
                            <template slot="append">元</template>
                        </el-input>
                    </el-form-item>
                </el-form-item>
                <el-form-item label="计费方式" prop="price_mode">
                    <el-row>
                        <el-col :span="12">
                            <el-input class="start_price" placeholder="公里数" type="number"
                                      v-model="ruleForm.price_mode.start_distance">
                                <template slot="append">公里内 起步价</template>
                            </el-input>
                        </el-col>
                        <el-col :span="12">
                            <el-input class="start_price" placeholder="价格" type="number"
                                      v-model="ruleForm.price_mode.start_price">
                                <template slot="append">元</template>
                            </el-input>
                        </el-col>
                    </el-row>
                    <el-row style="margin-top: 12px;">
                        <el-col :span="12">
                            <el-input class="start_price" placeholder="公里数" type="number"
                                      v-model="ruleForm.price_mode.add_distance">
                                <template slot="prepend">超出起步范围</template>
                                <template slot="append">公里内</template>
                            </el-input>
                        </el-col>
                        <el-col :span="12">
                            <el-input class="start_price" placeholder="价格" type="number"
                                      v-model="ruleForm.price_mode.add_price">
                                <template slot="prepend"> 每增加1公里</template>
                                <template slot="append">元</template>
                            </el-input>
                        </el-col>
                    </el-row>
                    <el-row style="margin-top: 12px;">
                        <el-col :span="12">
                            <el-input class="start_price" placeholder="公里数" v-model="exceed" disabled>
                                <template slot="prepend">超出</template>
                                <template slot="append">公里</template>
                            </el-input>
                        </el-col>
                        <el-col :span="12">
                            <el-input class="start_price" placeholder="价格" type="number"
                                      v-model="ruleForm.price_mode.fixed">
                                <template slot="prepend"> 固定价格</template>
                                <template slot="append">元</template>
                            </el-input>
                        </el-col>
                    </el-row>
                </el-form-item>
                <!--<el-form-item label="高德地图开放平台key" prop="web_key" required>-->
                <!--    <el-input @focus="hidden.web_key = false"-->
                <!--              v-if="hidden.web_key"-->
                <!--              readonly-->
                <!--              placeholder="已隐藏内容，点击查看或编辑">-->
                <!--    </el-input>-->
                <!--    <el-input v-else v-model.trim="ruleForm.web_key"></el-input>-->
                <!--    <div style="color: #ff4544;">注：必须注册web服务类型的应用key才有效</div>-->
                <!--</el-form-item>-->
                <el-form-item label="配送范围设置" prop="range" required>
                    <!--<div class="app-map" id="delivery-map" style="width: 662px;height: 500px;"-->
                    <!--     :data="ruleForm.range"></div>-->
                      <div class="app-map" id="map" style="width: 662px;height: 500px;"
                         :data="ruleForm.range"></div>
                </el-form-item>
            </el-form>
        </div>
        <el-button class="button-item" :loading="btnLoading" type="primary" @click="store('ruleForm')" size="small">保存
        </el-button>
    </el-card>
</div>
<script src="https://webapi.amap.com/maps?v=1.4.15&key=d3b41e4d52d3b71b7a268360734e3cfd&plugin=AMap.PolyEditor"></script>
<!--谷歌地图-->
 <script src="https://polyfill.io/v3/polyfill.min.js?features=default"></script>
   <script
      src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBZxAbfgeDc2z6YUOaBs8b0NuQgm_cHLdw&callback=initMap&libraries=places&v=weekly"
      defer
    ></script>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                cardLoading: false,
                btnLoading: false,
                polygon:"",
                blueCoords:[{"lat":5.322564,"lng":-4.239522},{"lat":5.291263,"lng":-4.105797},{"lat":5.235345,"lng":-4.062711},{"lat":5.20466,"lng":-3.824337},{"lat":5.296327,"lng":-3.793727},{"lat":5.343545,"lng":-3.849635},{"lat":5.492138,"lng":-3.827684},{"lat":5.578081,"lng":-3.920135},{"lat":5.609345,"lng":-4.097729}],
        locationList:[],
                ruleForm: {
                    is_superposition: 0,
                    mobile: [],
                    price_mode: {
                        start_price: 0,
                        start_distance: 0,
                        add_distance: 0,
                        add_price: 0,
                        fixed: 0
                    },
                    // web_key: "",
                    address: {address: "", longitude: "", latitude: ""},
                    explain: "",
                    range: [],
                    price_enable: 0,
                    contact_way: '',
                    free_delivery: 0,
                    is_free_delivery: 0,
                },
                rules: {
                    explain: [
                        {required: true, message: '请填写配送说明'},
                    ],
                    contact_way: [
                        {required: true, message: '请输入联系方式'},
                    ],
                },
                dialogVisible: false,
                mobile: {
                    id: 0,
                    name: '',
                    mobile: '',
                    index: -1
                },
                map: [],
                hidden: {
                    web_key: true
                },
            }
        },
        
        created(){
           
        },
        mounted() {
            this.loadData();
            this.initMap() 
        },
        watch: {
            'ruleForm.address'() {
                this.initMap();
            }
        },
        computed: {
            exceed() {
                let distance = 0;
                if (this.ruleForm.price_mode) {
                    if (this.ruleForm.price_mode.start_distance) {
                        distance += parseFloat(this.ruleForm.price_mode.start_distance);
                    }
                    if (this.ruleForm.price_mode.add_distance) {
                        distance += parseFloat(this.ruleForm.price_mode.add_distance);
                    }
                }
                return distance;
            }
        },
        methods: {
            // 初始化地图
              initMap() {
        // Create the map.
        this.map = new google.maps.Map(document.getElementById("map"), {
          zoom: 10,
          center: {
           "lat":5.322564,"lng":-4.239522
          },
        //   mapTypeId: "terrain",
             mapTypeId: "roadmap",
            streetViewControl: false,
            gestureHandling: 'greedy',
            mapTypeControlOptions: {
                mapTypeIds: []
            }
        }); // Construct the circle for each value in citymap.
        // Note: We scale the area of the circle based on the population.

       this.polygon = new google.maps.Polygon({
            map,
            paths: this.blueCoords,
            strokeColor: "#0000FF",
            strokeOpacity: 0.8,
            strokeWeight: 2,
            fillColor: "#0000FF",
            fillOpacity: 0.35,
            draggable: true,
            geodesic: false,
            editable: true,

        });
        this.polygon.setMap(this.map)
            // console.log(polygon)
            // polygon.addListener("draggable_changed", showNewRect);
            // polygon.addListener("editable_changed", showNewRect2);
            // map.addListener('bounds_changed', getMyLocation)
            // bermudaTriangle.setMap(map);
        google.maps.event.addListener(this.polygon, "dragend", this.getPolygonCoords);
        google.maps.event.addListener(this.polygon.getPath(), "insert_at", this.getPolygonCoords);
        google.maps.event.addListener(this.polygon.getPath(), "remove_at", this.getPolygonCoords);
        google.maps.event.addListener(this.polygon.getPath(), "set_at", this.getPolygonCoords);
      },
         getPolygonCoords() {
        var len = this.polygon.getPath().getLength();
        var htmlStr = "";
        // console.log(polygon.overlay.getPath().getArray())
        console.log(this.polygon.getPath().getArray()[0].lat())
        console.log(len)
        this.locationList = []
        for (var i = 0; i < len; i++) {
            console.log(this.polygon.getPath().getAt(i))
            htmlStr += this.polygon.getPath().getAt(i).toUrlValue(5) + "";
            let lat = this.polygon.getPath().getAt(i).lat()
            let lng = this.polygon.getPath().getAt(i).lng();
            let obj = {
                lat,
                lng
            };
            this.locationList.push(obj)
        }
        this.ruleForm.range=this.locationList;
        console.log(this.locationList)
        console.log(htmlStr)
            // document.getElementById('info').innerHTML = htmlStr;
    },
            loadData() {
                this.cardLoading = true;
                request({
                    params: {
                        r: 'mall/delivery/index',
                    },
                    method: 'get'
                }).then(response => {
                    console.log(response)
                    this.cardLoading = false;
                    if (response.data.code === 0) {
                        this.ruleForm = response.data.data.list;
                        console.log(this.ruleForm)
                    } else {
                        this.$message.error(response.data.msg);
                    }
                }).catch(response => {
                    console.log(response);
                });
            },
            store(formName) {
                this.$refs[formName].validate((valid) => {
                    if (valid) {
                        this.btnLoading = true;
                        request({
                            params: {
                                r: 'mall/delivery/edit'
                            },
                            data: {
                                set_data: JSON.stringify(this.ruleForm)
                            },
                            method: 'post'
                        }).then(response => {
                            this.btnLoading = false;
                            if (response.data.code === 0) {
                                this.$message.success('保存成功');
                            } else {
                                this.$message.error(response.data.msg);
                            }
                        }).catch(e => {
                            console.log(e);
                            this.btnLoading = false;
                        })
                    }
                });
            },
            mobileClick(index = -1) {
                if (index > -1) {
                    let {id, name, mobile} = JSON.parse(JSON.stringify(this.ruleForm.mobile[index]));
                    this.mobile = {id, name, mobile, index};
                }
                this.dialogVisible = true;
            },
            mobileConfirm() {
                if (this.mobile.name === '') {
                    this.$message.error('请填写姓名');
                    return;
                }
                if (this.mobile.mobile === '') {
                    this.$message.error('请填写一个联系方式');
                    return;
                }
                let {id, name, mobile} = JSON.parse(JSON.stringify(this.mobile));
                request({
                    params: {
                        r: 'mall/delivery/man'
                    },
                    data: {id, name, mobile},
                    method: 'post'
                }).then(response => {
                    if (response.data.code === 0) {
                        if (!this.ruleForm.mobile) {
                            this.ruleForm.mobile = [];
                        }
                        if (this.mobile.index === -1) {
                            this.ruleForm.mobile.push(response.data.data.model);
                        } else {
                            this.ruleForm.mobile.splice(this.mobile.index, 1, response.data.data.model)
                        }
                        this.mobile = {
                            id: 0,
                            name: '',
                            mobile: '',
                            index: -1
                        };
                        this.dialogVisible = false;
                    } else {
                        this.$message.error(response.data.msg);
                    }
                });
            },
            mobileDestroy(index) {
                this.$confirm('删除该条数据, 是否继续?', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning'
                }).then(() => {
                    let mobile = this.ruleForm.mobile.splice(index, 1);
                    request({
                        params: {
                            r: 'mall/delivery/man-delete'
                        },
                        data: mobile[0],
                        method: 'post'
                    }).then(response => {
                        if (response.data.code === 0) {
                            this.$message.success('删除成功');
                        } else {
                            this.$message.error(response.data.msg);
                        }
                    });
                });
            },
            mapEvent(e) {
                this.ruleForm.address = {
                    address: e.address,
                    latitude: e.lat,
                    longitude: e.long
                };
                // this.ruleForm.range = [];
            },
            // 初始化地图
            // initMap() {
            //     let self = this;
            //     let latitude = parseFloat(self.ruleForm.address.latitude || '30.747440');
            //     let longitude = parseFloat(self.ruleForm.address.longitude || '120.784830');
            //     self.map = new AMap.Map('delivery-map', {
            //         center: [longitude, latitude],
            //         zoom: 15
            //     });
            //     let key = 0.005;
            //     let path = [
            //         new AMap.LngLat(longitude - key, latitude - key),
            //         new AMap.LngLat(longitude + key, latitude - key),
            //         new AMap.LngLat(longitude + key, latitude + key),
            //         new AMap.LngLat(longitude - key, latitude + key)
            //     ];
            //     if (this.ruleForm.range.length > 0) {
            //         path = [];
            //         for (let i in this.ruleForm.range) {
            //             path.push(new AMap.LngLat(this.ruleForm.range[i].lng, this.ruleForm.range[i].lat))
            //         }
            //     }
            //     let polygon = new AMap.Polygon({
            //         path: path,
            //         strokeColor: "#FF33FF",
            //         strokeOpacity: 0.2,
            //         fillOpacity: 0.4,
            //         fillColor: '#1791fc',
            //         zIndex: 50,
            //     });
            //     this.addRange(polygon);

            //     self.map.add(polygon)
            //     // 缩放地图到合适的视野级别
            //     self.map.setFitView([polygon])

            //     let polyEditor = new AMap.PolyEditor(self.map, polygon);
            //     polyEditor.open();

            //     polyEditor.on('addnode', function (event) {
            //         console.log('触发事件：addnode')
            //         self.addRange(polygon);
            //     })

            //     polyEditor.on('adjust', function (event) {
            //         console.log('触发事件：adjust')
            //         self.addRange(polygon);
            //     })

            //     polyEditor.on('removenode', function (event) {
            //         console.log('触发事件：removenode')
            //         self.addRange(polygon);
            //     })

            //     polyEditor.on('end', function (event) {
            //         console.log('触发事件： end')
            //         self.addRange(polygon);
            //         // event.target 即为编辑后的多边形对象
            //     })
            // },
            addRange(polygon) {
                this.ruleForm.range = [];
                polygon.getPath().forEach((item) => {
                    this.ruleForm.range.push({
                        lat: item.lat,
                        lng: item.lng,
                    });
                })
            }
        }
    });
</script>
