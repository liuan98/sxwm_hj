<?php
/**
 * @copyright ©2018 Lu Wei
 * @author Lu Wei
 * @link http://www.luweiss.com/
 * Created by IntelliJ IDEA
 * Date Time: 2018/12/17 14:48
 */
?>
<style>
    .list-title {
        font-weight: 400;
        font-size: 18px;
    }

    .plugin-list {
        margin: 0 0 0 -20px;
    }

    .plugin-item {
        border: 1px solid #ebebeb;
        background: #fff;
        padding: 20px;
        margin: 0 0 20px 20px;
        transition: 250ms;
        position: relative;
        height: 97px;
    }

    .plugin-item .display-name {
        margin-bottom: 10px;
        transition: 250ms;
    }

    .plugin-item .name {
        color: #909399;
    }

    .plugin-item:hover {
        cursor: pointer;
        border-color: #bfddff;
    }

    .plugin-item:hover .display-name {
        color: #409EFF;
    }

    .plugin-item .plugin-option-btn {
        height: 25px;
        width: 25px;
        font-size: 12px;
        padding: 0;
    }

    .plugin-item .detail-option.detail-option {
        color: #909399;
        background: #F2F6FC;
    }

    .plugin-item .detail-option:hover {
        color: #fff;
        background: #909399;
    }
</style>
<div id="app" v-cloak>
    <div flex="cross:center">
        <!-- jambalaya -->
        <!-- <h3 class="list-title">已安装</h3> -->
        <div>
            <el-button v-if="pluginHasUpdateCount>0" @click="updateAll" size="small" type="warning"
                       style="margin-left: 20px">更新全部
            </el-button>
        </div>
    </div>
    <el-row class="plugin-list" v-loading="loading">
        <template v-for="(plugin,index) in list">
            <el-col :xs="24" :sm="12" :md="8" :lg="6" :xl="4">
                <div flex="dir:left box:first" class="plugin-item" @click="entryPlugin(plugin)">
                    <div style="padding-right: 12px;">
                        <img style="width: 50px;height: 50px;display: block;" :src="plugin.pic_url">
                    </div>
                    <div>
                        <div class="display-name">{{plugin.display_name}}</div>
                        <div flex="box:last">
                            <div class="name">{{plugin.name}}</div>
                            <div>
                                <el-popover v-if="plugin.plugin && plugin.plugin.new_version"
                                            placement="bottom"
                                            trigger="hover"
                                            :content="cPluginUpdateTip(plugin)">
                                <span v-if="plugin.updating" slot="reference"
                                      style="color: #909399;" @click.stop>更新中...</span>
                                    <el-button v-else @click.stop="updateItem(index)"
                                               slot="reference"
                                               size="mini" plain type="warning"
                                               class="plugin-option-btn"
                                               icon="el-icon-upload"
                                               style="right: 55px;border: none;font-size: 14px"
                                               circle
                                               :disabled="plugin.updating || allPluginUpdating"></el-button>
                                </el-popover>
                                <el-button class="plugin-option-btn detail-option"
                                           v-if="plugin.showDetail && !plugin.updating"
                                           type="text"
                                           icon="el-icon-more"
                                           circle
                                           @click.stop="entryDetail(plugin)"></el-button>
                            </div>
                        </div>
                    </div>
                </div>
            </el-col>
        </template>
    </el-row>
    <el-row class="plugin-list" v-loading="notInstallLoading" style='display: none;'>
        <template v-if="!notInstallList || notInstallList.length > 0">
            <h3 class="list-title" style="padding-left: 20px;">未安装</h3>
            <template v-for="plugin in notInstallList">
                <el-col :xs="24" :sm="12" :md="8" :lg="6" :xl="4">
                    <div flex="dir:left" class="plugin-item" @click="entryDetail(plugin)">
                        <div style="padding-right: 12px;">
                            <img style="width: 50px;height: 50px;display: block;" :src="plugin.pic_url">
                        </div>
                        <div>
                            <div class="display-name">{{plugin.display_name}}</div>
                            <div class="name">{{plugin.name}}</div>
                        </div>
                    </div>
                </el-col>
            </template>
        </template>
    </el-row>
</div>
<script>
    new Vue({
        el: '#app',
        data() {
            return {
                loading: false,
                notInstallLoading: false,
                list: [],
                notInstallList: [],
                pluginHasUpdateCount: 0,
                allPluginUpdating: false,
            };
        },
        created() {
            this.loadData(1);
        },
        methods: {
            loadData(page) {
                this.loading = true;
                request({
                    params: {
                        r: 'mall/plugin/index',
                    },
                }).then(e => {
                    this.loadNotInstallData();
                    this.loading = false;
                    if (e.data.code === 0) {
                        e.data.data.list.forEach(item => {
                            item.updating = false;
                            item.plugin = null;
                        });
                        this.list = e.data.data.list;
                        this.checkUpdateList();
                    } else {
                        this.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    this.loadNotInstallData();
                });
            },
            loadNotInstallData() {
                if (!_isSuperAdmin) {
                    return;
                }
                this.notInstallLoading = true;
                this.$request({
                    params: {
                        r: 'mall/plugin/not-install-list',
                    },
                }).then(e => {
                    this.notInstallLoading = false;
                    if (e.data.code === 0) {
                        this.notInstallList = e.data.data.list;
                    } else {
                        this.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    this.notInstallLoading = false;
                });
            },
            entryPlugin(plugin) {
                if (!plugin.route) {
                    return;
                }
                navigateTo({r: plugin.route}, true);
            },
            entryDetail(plugin) {
                navigateTo({r: 'mall/plugin/detail', name: plugin.name});
            },
            cPluginUpdateTip(item) {
                return '有更新，最新版本v' + item.plugin.new_version.version + '，当前版本v' + item.plugin.version;
            },
            checkUpdateList() {
                if (!_isSuperAdmin) {
                    return;
                }
                this.$request({
                    params: {
                        r: 'mall/plugin/check-update-list',
                    },
                }).then(e => {
                    if (e.data.code === 0) {
                        e.data.data.list.forEach(item => {
                            this.list.forEach((plugin, i) => {
                                if (plugin.name == item.name) {
                                    this.list[i].plugin = item;
                                    if (this.list[i].plugin.new_version) {
                                        this.pluginHasUpdateCount++;
                                    }
                                }
                            });
                        });
                    }
                }).catch(() => {
                });
            },
            updateAll() {
                const count = this.pluginHasUpdateCount;
                this.$confirm(`确认更新${count}个插件？`).then(() => {
                    let updateList = [];
                    this.list.forEach((item, i) => {
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
                        this.list[updateList[i].index].updating = true;
                        this.updatePlugin(updateList[i].id, updateList[i].name).then(() => {
                            this.list[updateList[i].index].plugin.new_version = null;
                            this.list[updateList[i].index].updating = false;
                            update(i + 1);
                        }).catch(msg => {
                            if (!msg) {
                                msg = '安装未完成。';
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
                const item = this.list[index];
                this.$confirm(`确认更新${item.display_name}？`).then(() => {
                    this.allPluginUpdating = true;
                    item.updating = true;
                    this.updatePlugin(item.plugin.id, item.plugin.name).then(() => {
                        this.$alert('插件更新完成。').then(() => {
                            location.reload();
                        });
                    }).catch(msg => {
                        if (!msg) {
                            msg = '安装未完成。';
                        }
                        this.$alert(msg).then(() => {
                            location.reload();
                        });
                    });
                }).catch(() => {
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
        }
    });
</script>
