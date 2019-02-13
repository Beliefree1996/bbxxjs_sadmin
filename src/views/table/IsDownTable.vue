<template>
    <div class="app-container">
        <div class="filter-container">
            <!--条件搜索-->
            <el-row :span="8" class="top_toolbar">
                <!--条件搜索-->
                <el-form :inline="true" :model="filters" style="display: inline-block;padding: 0;">
                    <el-form-item>
                        <el-input v-model="filters.name" placeholder="管理员名字" style="width: 120px"></el-input>
                    </el-form-item>
                    <el-form-item style="margin-left: 10px">
                        <el-button type="primary" v-on:click="getTable">查询</el-button>
                    </el-form-item>
                </el-form>
            </el-row>
            <el-row>
                <br/>
            </el-row>

            <!--列表-->
            <el-table :data="table_list" max-height="640" highlight-current-row v-loading="tableLoading"
                      @selection-change="selsChange" border style="width: 100%;" :header-cell-style="tableHeaderColor">
                <!--<el-table-column type="selection" width="55" label="全选"></el-table-column>-->
                <el-table-column align="center" type="index" width="66" label="序号" fixed="left"></el-table-column>
                <el-table-column align="center" prop="username" min-width="160" label="管理员名字"></el-table-column>
                <!--<el-table-column align="center" prop="mobile" min-width="160" label="手机号"></el-table-column>-->
                <el-table-column align="center"  min-width="100" label="下载权限">
                    <template slot-scope="scope">
                        <el-switch v-model="scope.row.isdown === 1" active-color="#13ce66"
                                   @change="changeSwitchAction(scope.row)">
                        </el-switch>
                    </template>
                </el-table-column>
                <el-table-column align="center"  min-width="100" label="号码隐藏">
                    <template slot-scope="scope">
                        <el-switch v-model="scope.row.notshow === 1" active-color="#13ce66"
                                   @change="changeNotShow(scope.row)">
                        </el-switch>
                    </template>
                </el-table-column>
            </el-table>
            <!--页码条-->
            <el-col :span="24" class="toolbar" style="margin-top: 10px">
                <!--<el-button type="primary" :disabled="this.sels.length===0"> 批量分配 </el-button>-->
                <!--<el-pagination layout="prev, pager, next" @current-change="handleCurrentChange" :page-size="pagesize"-->
                               <!--:total="total" style="float:right;"></el-pagination>-->
            </el-col>
        </div>
    </div>
</template>

<script>
    import { getToken, setToken, removeToken } from '@/utils/auth'
    import { userList,downChange,showChange } from '@/api/Number'


    export default {
        name: "IsDownTable",
        data() {
            return {
                add_number_table: false,
                fileList: [],
                uploadfileParams: {},
                table_list: [],
                sels: [],//列表选中列
                tableLoading: false,
                // total: 50,
                // page: 1,
                // pagesize: 10,
                filters: {name: ''},
                token:"",
                //来电弹窗
                inCallDialog:true,
            }
        },
        created(){
            this.token = getToken()
            this.getTable()
        },
        mounted() {
            this.uploadfileParams = {token: this.token};
            console.log(this.uploadfileParams);
        },
        methods:{
            // 修改table header的背景色
            tableHeaderColor({ row, column, rowIndex, columnIndex }) {
                if (rowIndex === 0) {
                    return 'background-color: #f7f7f7;color: #363636;font-weight: 500;'
                }
            },
            // 选择行
            selsChange (sels) {
                this.sels = sels;
                console.log(sels);
            },
            // 点击页码
            // handleCurrentChange(val) {
            //     this.page = val;
            //     this.getTable()
            // },
            // 请求表格数据
            getTable() {
                this.tableLoading = true;
                // TODO获取表格数据
                var _this = this;
                var params = {
                    token:_this.token,
                    // s: _this.page,
                    // p: _this.pagesize,
                    name: _this.filters.name
                };

                userList(params).then(response => {
                    console.log(response);
                    const res = response.data;
                    if (res.code == '0000') {
                        this.table_list = res.data.rows;
                        // this.total = res.data.total;
                    } else {
                        this.$message.error(res.msg);
                    }
                    this.tableLoading = false
                }).catch(err => {
                    console.log(err);
                    this.tableLoading = false;
                    this.$message.error("网络错误");
                });
            },
            changeSwitchAction(scope){
                console.log(scope);
                var _this = this;
                var tag = scope.isdown;
                downChange({id: scope.id, tag: tag === 1 ? 0 : 1}).then(response => {
                    console.log(response);
                    const res = response.data;
                    if (res.code == '0000') {
                        if (scope.isdown === 1) {
                            _this.$message.success("下载权限关闭完成！");
                            scope.isdown = 0;
                        } else {
                            _this.$message.success("下载权限启动完成！");
                            scope.isdown = 1;
                        }
                        this.getTable();
                    } else {
                        _this.$message.error(res.msg);
                    }
                }).catch(err => {
                    _this.$message.error("网络错误");
                });
            },
            changeNotShow(scope){
                console.log(scope);
                var _this = this;
                var flag = scope.notshow;
                showChange({id: scope.id, flag: flag === 1 ? 0 : 1}).then(response => {
                    console.log(response);
                    const res = response.data;
                    if (res.code == '0000') {
                        if (scope.notshow === 1) {
                            _this.$message.success("号码全显关闭完成！");
                            scope.notshow = 0;
                        } else {
                            _this.$message.success("号码全显开启完成！");
                            scope.notshow = 1;
                        }
                        this.getTable();
                    } else {
                        _this.$message.error(res.msg);
                    }
                }).catch(err => {
                    _this.$message.error("网络错误");
                });
            },
        },
    }
</script>

<style scoped>
    .upload_table {
        display: inline-block;
    }

    .el-table {
        border-radius: 5px;
    }

    .el-form-item {
        margin: 0;
    }

    .backFileListClass {
        cursor: pointer;
    }

    .backFileListClass:hover {
        color: #3a8ee6;
    }
</style>