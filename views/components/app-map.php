<?php
/**
 * link: http://www.67930603.top/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: Alpha
 */
?>
<template id="app-map">
    <div class="app-map">
        <el-dialog :title="title ? title : '地图展示'"
                   :visible.sync="dialogVisible"
                   @opened="dialogOpened"
                   :close-on-click-modal="false">
            <el-form label-width="80px" size="small">
                <el-row>
                    <el-col :span="12">
                        <el-form-item label="地址搜索">
                            <el-input placeholder="请输入具体地址" v-model="mapKeyword">
                                <el-button @keyup.enter.native="mapSearch" @click="mapSearch" slot="append"
                                           icon="el-icon-search"></el-button>
                            </el-input>
                        </el-form-item>
                    </el-col>
                </el-row>
                <el-row>
                    <el-col :span="12">
                        <el-form-item label="地址">
                            <el-input disabled v-model="newAddress"></el-input>
                        </el-form-item>
                    </el-col>
                    <el-col :span="12">
                        <el-form-item label="纬度|经度">
                            <el-input disabled v-model="lat_long"></el-input>
                        </el-form-item>
                    </el-col>
                </el-row>
            </el-form>
            <div class="map" id="container" :style="style"></div>
            <span style="height:30px;display:none" id="city"></span>
            <div slot="footer" class="dialog-footer">
                <el-button type="primary" @click="confirm">确 定</el-button>
            </div>
        </el-dialog>
        <div @click="dialogVisible = !dialogVisible" style="display: inline-block">
            <slot></slot>
        </div>
    </div>
</template>
<!--<script charset="utf-8" src="https://map.qq.com/api/js?v=2.exp&key=OV7BZ-ZT3HP-6W3DE-LKHM3-RSYRV-ULFZV"></script>-->
    <!--<script src="https://polyfill.io/v3/polyfill.min.js?features=default"></script>-->
 <!--<script src="https://polyfill.io/v3/polyfill.min.js?features=default"></script>-->
    <!--<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBZxAbfgeDc2z6YUOaBs8b0NuQgm_cHLdw&callback=initMap&libraries=&v=weekly" defer></script>-->
    <!--<script src="https://maps.googleapis.com/maps/api/place/js/PlaceService"></script>-->
<script>
    Vue.component('app-map', {
        template: '#app-map',
        props: {
            width: String,
            height: String,
            lat: String,
            long: String,
            address: {
                type: String,
                default: '',
            },
            title: String,
        },

        data() {
            return {
                longitude: '', // 经度(大)
                latitude: '',// 纬度(小)
                lat_long: '',
                markers: [],
                map: [],
                searchService: {},
                mapKeyword: '',
                dialogVisible: false,
                newAddress: '',
                city: '',
                service:"",
                infowindow:""
            };
        },
        created() {
            // this.initMap()
        },
        computed: {
            style() {
                let width = '100%';
                let height = '400px';
                if (this.width) {
                    width = this.width + (isNaN(this.width) ? '' : 'px');
                }
                if (this.height) {
                    height = this.height + (isNaN(this.height) ? '' : 'px');
                }

                return `width:${width};height:${height};`;
            },
        },
        methods: {
            dialogOpened() {
                this.newAddress = this.address ? this.address : '';
                this.initMap();
            },
            // 谷歌地图

              initMap() {
              console.log(111)
                  console.log(this.lat,this.long)
            const sydney = new google.maps.LatLng(parseFloat(this.lat), parseFloat(this.long));
            this.infowindow = new google.maps.InfoWindow();
            this.map = new google.maps.Map(document.getElementById("container"), {
                center: sydney,
                zoom: 15
            });

            const marker = new google.maps.Marker({
                map:this.map,
                position: sydney
            });
            let that=this;
            google.maps.event.addListener(this.map, 'click', function(event) {
    that.addMarker(event.latLng, that.map);
  });
        },
        addMarker(location, map) {
  // Add the marker at the clicked location, and add the next-available label
  // from the array of alphabetical characters.
  var marker = new google.maps.Marker({
    position: location,
    // label: labels[labelIndex++ % labels.length],
    map: map
  });
  console.log(marker)
   this.getAddress(marker)
},

// 获取标记点位置
showAddress(map, marker)
        {
            var latlng = marker.getPosition();
            geocoder = new google.maps.Geocoder();

            //根据经纬度获取地址信息
            geocoder.geocode({'latLng': latlng}, function(results, status) {
                if (status == google.maps.GeocoderStatus.OK) {
                console.log(results)
                    if (results[1]) {
                        var address = results[1].formatted_address + "<br />";

                        address = results[0].formatted_address + "<br />";
                        address += "纬度：" + latlng.lat() + "<br />";
                        address += "经度：" + latlng.lng();
                         console.log(address)
                        this.infowindow.setContent(address);
                        this.infowindow.open(map, marker);
                        transfer(latlng.lat(),latlng.lng());
                    }
                } else {
                    alert("Geocoder failed due to: " + status);
                }
            });
        },
        // 搜索位置
        mapSearch(){
              const request = {
                query: "南昌红谷滩",
                fields: ["name", "geometry", "formatted_address"]
            };
            request.query =this.mapKeyword;
            console.log(request)
            // console.log(document.getElementById("input").value)
            // console.log(new google.maps)
            // console.log( google.maps.places)

            this.service =new google.maps.places.PlacesService(this.map);
            console.log(this.service)
            let that=this;
            this.service.findPlaceFromQuery(request, (results, status) => {
                console.log(request,results,status)
                if (status === google.maps.places.PlacesServiceStatus.OK) {

                    for (let i = 0; i < results.length; i++) {
                        that.createMarker(results[i]);
                    }
                    this.newAddress=results[0].formatted_address;

                    this.lat_long=results[0].geometry.location.lat()+","+results[0].geometry.location.lng()
                    this.latitude=results[0].geometry.location.lat()
                    this.longitude= results[0].geometry.location.lng()
                    that.map.setCenter(results[0].geometry.location);
                }
            });
        },
        // 创建标记
        createMarker(place) {
            const marker = new google.maps.Marker({
                map:this.map,
                position: place.geometry.location
            });
            google.maps.event.addListener(marker, "click", () => {
                this.infowindow.setContent(place.name);
                this.infowindow.open(map);
            });

        },
        // 根据经纬度获取地址信息
        getAddress(marker){
          var latlng = marker.getPosition();
                  let geocoder = new google.maps.Geocoder();

                    //根据经纬度获取地址信息
                    geocoder.geocode({'latLng': latlng}, function(results, status) {
                        if (status == google.maps.GeocoderStatus.OK) {
                            if (results[1]) {
                                var address = results[1].formatted_address + "<br />";
                                address = results[0].formatted_address + "<br />";
                                address += "纬度：" + latlng.lat() + "<br />";
                                address += "经度：" + latlng.lng();

                                infowindow.setContent(address);
                                infowindow.open(map, marker);
                                transfer(latlng.lat(),latlng.lng());
                            }
                        } else {
                            alert("Geocoder failed due to: " + status);
                        }

                        })
        },

            // 初始化地图
            // initMap() {
            //     let self = this;
            //     let center = new qq.maps.LatLng(self.latitude, self.longitude);// 默认坐标
            //     self.map = new qq.maps.Map(
            //         document.getElementById("container"),
            //         {
            //             center: center,
            //             zoom: 13,// 缩放级别
            //         }
            //     );

            //     let citylocation = new qq.maps.CityService({
            //         map: self.map,
            //         complete: function (results) {
            //             console.log('所在位置: ' + results.detail.name);
            //             self.city = results.detail.name;
            //             self.map.setCenter(results.detail.latLng);
            //             let marker = self.setMarker(results.detail.latLng);
            //             self.markers.push(marker);
            //         }
            //     });

            //     // 搜索服务 默认获取当前地址
            //     if (!self.lat && !self.long) {
            //         citylocation.searchLocalCity()
            //     } else {
            //         self.latitude = self.lat;
            //         self.longitude = self.long;
            //         self.lat_long = self.lat + ',' + self.long;
            //         citylocation.searchCityByLatLng(new qq.maps.LatLng(self.latitude, self.longitude));
            //     }

            //     this.clickEvent(center);
            //     this.initSearch();
            // },
            // // 地图点击事件
            // clickEvent(center) {
            //     let self = this;
            //     let listener = qq.maps.event.addListener(this.map, 'click', function (event) {
            //         self.longitude = event.latLng.getLng().toFixed(6);
            //         self.latitude = event.latLng.getLat().toFixed(6);
            //         self.lat_long = self.latitude + ',' + self.longitude;

            //         self.getAddressBylatLong();
            //         let coord = new qq.maps.LatLng(self.latitude, self.longitude);
            //         let marker = self.setMarker(coord);
            //         self.markers.push(marker);
            //     });
            // },
            // // 根据经纬度获取地址信息
            // getAddressBylatLong() {
            //     let self = this;
            //     // 根据经纬度查询城市信息
            //     let geocoder = new qq.maps.Geocoder({
            //         complete: function (result) {
            //             self.newAddress = result.detail.address;
            //         }
            //     });
            //     let coord = new qq.maps.LatLng(self.latitude, self.longitude);
            //     geocoder.getAddress(coord);
            // },
            // // 添加标注
            // setMarker(coord) {
            //     let self = this;
            //     // 添加标注
            //     let marker = new qq.maps.Marker({
            //         map: self.map,
            //         position: coord
            //     });
            //     //获取标记的点击事件
            //     qq.maps.event.addListener(marker, 'click', function (event) {
            //         self.longitude = event.latLng.getLng().toFixed(6);
            //         self.latitude = event.latLng.getLat().toFixed(6);
            //         self.lat_long = self.latitude + ',' + self.longitude;

            //         self.getAddressBylatLong();
            //     });

            //     return marker;
            // },
            // // 清除地址坐标
            // clearOverLays() {
            //     //清除地图上的marker
            //     let overlay;
            //     while (overlay = this.markers.pop()) {
            //         overlay.setMap(null);
            //     }
            // },
            // initSearch() {
            //     let self = this;
            //     let latlngBounds = new qq.maps.LatLngBounds();
            //     //设置Poi检索服务，用于本地检索、周边检索
            //     self.searchService = new qq.maps.SearchService({
            //         //设置搜索范围为
            //         location: self.city,
            //         //设置动扩大检索区域。默认值true，会自动检索指定城市以外区域。
            //         autoExtend: true,
            //         //检索成功的回调函数
            //         complete: function (results) {
            //             //设置回调函数参数
            //             let pois = results.detail.pois;
            //             if (!pois) {
            //                 alert("输入详细地址搜索更准确");
            //                 return false;
            //             }
            //             for (let i = 0, l = pois.length; i < l; i++) {
            //                 let poi = pois[i];
            //                 //扩展边界范围，用来包含搜索到的Poi点
            //                 latlngBounds.extend(poi.latLng);

            //                 let marker = self.setMarker(poi.latLng)
            //                 marker.setTitle(i + 1);
            //                 self.markers.push(marker);
            //             }
            //             //调整地图视野
            //             self.map.fitBounds(latlngBounds);
            //         },
            //         //若服务请求失败，则运行以下函数
            //         error: function () {
            //             alert("出错了。");
            //         }
            //     });
            // },
            // // 地址搜索
            // mapSearch() {
            //     this.clearOverLays();
            //     this.searchService.search(this.mapKeyword);
            // },
            confirm() {
                this.$emit('map-submit', {
                    lat: this.latitude,
                    long: this.longitude,
                    address: this.newAddress
                });
                this.dialogVisible = false;
            },
        }
    })
</script>
