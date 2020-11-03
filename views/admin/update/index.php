<?php

/**
 * Created by IntelliJ IDEA.
 * User: luwei
 * Date: 2019/3/1
 * Time: 17:05
 */

/* @var $this \yii\web\View */
?>
<style>
    .row-item {
        height: 105px;
        width: 30%;
        padding-left: 30px;
        padding-top: 15px;
        color: #353535;
        font-size: 26px;
    }

    .row-item:first-of-type {
        border-right: 1px dashed #e6e6e6;
    }

    .row-label {
        color: #9c9fa4;
        font-size: 16px;
        margin-bottom: 10px;
    }

    .version-item, .next-version {
        line-height: 1.5;
    }

    .next-version p,
    .version-item p {
        margin-top: 0;
        margin-bottom: 0;
    }

    .version-item::after {
        content: " ";
        display: block;
        height: 0;
        border-bottom: 1px dashed #c9c9c9;
        margin: 10px 0;
        width: 500px;
        max-width: 100%;
    }

    .update-tab .el-tabs__header {
        margin-right: 20px !important;
        background: #fff;
        border-radius: 5px;
        border: 1px solid #eee;
    }

    .update-tab .el-tabs__content {
        background: #fff;
        padding: 20px;
        border-radius: 5px;
        border: 1px solid #eee;
    }

    .update-tab.el-tabs--left .el-tabs__item.is-left {
        text-align: left;
        min-width: 150px;
    }

    .update-tab.el-tabs--left .el-tabs__active-bar.is-left {
        left: 0;
        right: auto;
        height: 30px !important;
        top: 15px;
    }

    .update-tab.el-tabs--left .el-tabs__nav-wrap.is-left::after {
        background: transparent;
    }

    .update-tab .el-tabs__item {
        height: 60px;
        line-height: 60px;
    }

    .update-num-icon {
        display: inline-block;
        width: 18px;
        height: 18px;
        text-align: center;
        line-height: 18px;
        background: #ff4544;
        color: #fff;
        border-radius: 999px;
        font-size: 10px;
        position: relative;
        top: -3px;
        right: -3px;
    }

    .plugin-item {
        width: 235px;
        border: 1px solid #e2e2e2;
        background: #fff;
        margin: 20px 0 0 20px;
        padding: 15px;
    }

    .plugin-icon {
        width: 50px;
        height: 50px;
        background-size: cover;
        background-position: center;
        background-color: #e2e2e2;
    }

    .plugin-update-btn {
        font-size: 16px;
        padding: 4px !important;
    }

    .plugin-display-name {
        line-height: 1.75;
    }

    .text-ellipsis {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
</style>
<div id="app" v-cloak>
    <el-tabs class="update-tab" tab-position="left">
        <el-tab-pane>
            <div class="update-tab-title" slot="label">系统更新</div>
            <div v-loading="loading">
                <template v-if="result">
                    <div flex="dir-left" style="margin-bottom: 20px;">
                        <div class="row-item">
                            <div class="row-label">当前版本</div>
                            <div>{{result.host_version}}</div>
                        </div>
                        <div class="row-item">
                            <div class="row-label">下一版本</div>
                            <div>
                                <div class="next-version" v-if="result.next_version">
                                    <div flex="dir-left" style="margin-bottom: 10px;align-items: center">
                                        <div style="margin-right: 8px">v{{result.next_version.version}}</div>
                                        <el-button size="small" type="primary"
                                                   style="padding: 9px 25px;margin-bottom: 10px"
                                                   @click="updateConfirm" :loading="updateLoading">更新
                                        </el-button>
                                    </div>
                                    <!-- <div v-html="result.next_version.content"></div> -->
                                </div>
                                <div v-else>暂无新版本</div>
                            </div>
                        </div>
                    </div>
                    <div style="border-bottom: 1px solid #e2e2e2;margin-bottom: 20px;"></div>
                    <div>
                        <div class="row-label" style="margin-bottom: 20px">历史版本记录</div>
                        <div style="padding-left: 50px;" v-if="result.list && result.list.length">
                            <div v-for="item in result.list" class="version-item">
                                <div style="margin-bottom: 10px">版本号: {{item.version}}</div>
                                <div v-html="item.content"></div>
                            </div>
                        </div>
                        <div style="padding-left: 50px;" v-else>暂无记录</div>
                    </div>
                </template>
            </div>
        </el-tab-pane>
        <el-tab-pane>
            <span class="update-tab-title" slot="label">插件更新</span>
            <div v-loading="pluginLoading">
                <template v-if="pluginList && pluginList.length">
                    <el-button v-if="pluginHasUpdateCount>0" @click="updateAll" style="margin-bottom: 20px;"
                               type="warning"
                               size="small" :loading="allPluginUpdating">更新全部
                    </el-button>
                    <div flex style="flex-wrap: wrap;margin-left: -20px;margin-top: -20px;">
                        <div v-for="(item,index) in pluginList"
                             class="plugin-item"
                             flex="dir:left box:first">
                            <div style="padding-right: 15px;">
                                <div class="plugin-icon" :style="'background-image: url('+item.icon_url+');'"></div>
                            </div>
                            <div>
                                <div class="plugin-display-name text-ellipsis" :title="item.display_name">
                                    {{item.display_name}}
                                </div>
                                <div flex="box:last">
                                    <div style="color: #909399;" class="text-ellipsis" :title="item.name">{{item.name}}
                                    </div>
                                    <div v-if="item.plugin">
                                        <el-popover v-if="item.plugin.new_version"
                                                    placement="bottom"
                                                    trigger="hover"
                                                    :content="cPluginUpdateTip(item)">
                                            <span v-if="item.updating" slot="reference"
                                                  style="color: #909399;">更新中...</span>
                                            <el-button
                                                    v-else
                                                    @click="updateItem(index)"
                                                    slot="reference"
                                                    size="mini" plain type="warning"
                                                    class="plugin-update-btn"
                                                    icon="el-icon-upload"
                                                    circle :disabled="item.updating || allPluginUpdating"></el-button>
                                        </el-popover>
                                    </div>
                                    <div v-else style="color: #F56C6C;">错误</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
                <div v-if="pluginList && !pluginList.length"
                     style="padding: 20px;text-align: center;color: #909399;font-size: 16px;">
                    - 暂未安装任何插件 -
                </div>
            </div>
        </el-tab-pane>
    </el-tabs>
</div>
<script>
    new Vue({
        el: '#app',
        data() {
            return {
                loading: true,
                result: null,
                updateLoading: false,
                pluginLoading: true,
                pluginList: null,
                allPluginUpdating: false,
            };
        },
        created() {
            this.loadData();
            this.loadPluginUpdateData();
        },
        computed: {
            pluginHasUpdateCount() {
                if (!this.pluginList) {
                    return 0;
                }
                let count = 0
                for (let i in this.pluginList) {
                    if (this.pluginList[i].plugin && this.pluginList[i].plugin.new_version) {
                        count++;
                    }
                }
                return count;
            },
        },
        methods: {
            loadData() {
                this.loading = true;
                this.$request({
                    params: {
                        r: 'admin/update/index',
                    },
                }).then(e => {
                    this.loading = false;
                    if (e.data.code === 0) {
                        this.result = e.data.data;
                    } else {
                        this.$alert(e.data.msg, '提示', {
                            type: 'error',
                        });
                    }
                }).catch(e => {
                    this.loading = false;
                });
            },
            updateConfirm() {
                this.$confirm('确认更新到版本 ' + this.result.next_version.version + ' ?', '警告', {
                    type: 'warning',
                }).then(() => {
                    this.update();
                }).catch(() => {
                    location.reload();
                });
            },
            update() {
                this.updateLoading = true;
                this.$request({
                    params: {
                        r: 'admin/update/update'
                    },
                    data: {
                        _csrf: this._csrf,
                    },
                    method: 'post',
                }).then(e => {
                    if (e.data.code === 0) {
                        this.$alert(e.data.msg, '提示').then(() => {
                            location.reload();
                        }).catch(() => {
                            location.reload();
                        });
                    } else {
                        this.$alert(e.data.msg, '提示', {
                            type: 'error',
                        }).then(() => {
                            location.reload();
                        }).catch(() => {
                            location.reload();
                        });
                    }
                }).catch(e => {
                });
            },
            loadPluginUpdateData() {
                this.pluginLoading = true;
                this.$request({
                    params: {
                        r: 'admin/update/plugin-update-data'
                    },
                }).then(e => {
                    this.pluginLoading = false;
                    if (e.data.code === 0) {
                        for (let i in e.data.data.list) {
                            e.data.data.list[i].updating = false;
                        }
                        this.pluginList = e.data.data.list;
                    } else {
                    }
                });
            },
            cPluginUpdateTip(item) {
                return '有更新，最新版本v' + item.plugin.new_version.version + '，当前版本v' + item.plugin.version;
            },
            updateAll() {
                const count = this.pluginHasUpdateCount;
                this.$confirm(`确认更新${count}个插件？`).then(() => {
                    let updateList = [];
                    this.pluginList.forEach((item, i) => {
                        if (item.plugin && item.plugin.new_version) {
                            updateList.push({
                                index: i,
                                id: item.plugin.id,
                                name: item.plugin.name,
                            });
                        }
                    });
                    const update = (i) => {
                        if (i >= updateList.length) {
                            this.$alert('插件更新完成。').then(() => {
                                this.allPluginUpdating = false;
                                location.reload();
                            });
                            return;
                        }
                        this.pluginList[updateList[i].index].updating = true;
                        this.updatePlugin(updateList[i].id, updateList[i].name).then(() => {
                            this.pluginList[updateList[i].index].plugin.new_version = null;
                            this.pluginList[updateList[i].index].updating = false;
                            update(i + 1);
                        }).catch(msg => {
                            if (!msg) {
                                msg = '更新未完成。';
                            }
                            this.$alert(msg).then(() => {
                                this.allPluginUpdating = false;
                                location.reload();
                            });
                        });
                    };
                    this.allPluginUpdating = true;
                    update(0);

                }).catch(() => {
                });
            },
            updateItem(index) {
                const item = this.pluginList[index];
                this.$confirm(`确认更新${item.display_name}？`).then(() => {
                    this.allPluginUpdating = true;
                    item.updating = true;
                    this.updatePlugin(item.plugin.id, item.plugin.name).then(() => {
                        this.$alert('插件更新完成。').then(() => {
                            location.reload();
                        });
                    }).catch(msg => {
                        if (!msg) {
                            msg = '更新未完成。';
                        }
                        this.$alert(msg).then(() => {
                            location.reload();
                        });
                    });
                }).catch(msg => {
                });
            },
            updatePlugin(id, name) {
                const download = (id) => {
                    return new Promise((resolve, reject) => {
                        this.$request({
                            params: {
                                r: 'mall/plugin/download',
                                id: id,
                            },
                        }).then(e => {
                            if (e.data.code === 0) {
                                resolve();
                            } else {
                                reject(e.data.msg);
                            }
                        }).catch(e => {
                            reject();
                        });
                    });
                };
                const install = (name) => {
                    return new Promise((resolve, reject) => {
                        this.$request({
                            params: {
                                r: 'mall/plugin/install',
                                name: name,
                            },
                        }).then(e => {
                            if (e.data.code === 0) {
                                resolve();
                            } else {
                                reject(e.data.msg);
                            }
                        }).catch(e => {
                        });
                    });
                };
                return new Promise((resolve, reject) => {
                    download(id).then(() => {
                        install(name).then(() => {
                            resolve();
                        }).catch(msg => {
                            reject(msg);
                        });
                    }).catch(msg => {
                        reject(msg);
                    });
                });
            },
        },
    });
</script>
