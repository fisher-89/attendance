<style>
	#calendar + .flatpickr-calendar {
		width: 100%;
		max-width: 400px;
		overflow: hidden;
		transition: all .5s;
	}

	#calendar + .flatpickr-calendar:before,
	#calendar + .flatpickr-calendar:after {
		content: none;
	}

	#calendar.calendar-show + .flatpickr-calendar {
		height: 290px;
	}

	#calendar.calendar-hide + .flatpickr-calendar {
		height: 0px;
	}
</style>
<template>
	<div>
		<mt-loadmore :top-method="refresh" ref="loadmore" disabled style="overflow:visible;">
			<mt-cell title="店铺" label="点击选择店铺" @click.native="shopPicker = true" style="margin:0 1px;">
				<span style="font-size:12px;">
					{{selectedShop.shopName}}<br>
					{{selectedShop.shopSn}}
				</span>
			</mt-cell>
			<Row style="margin-right:1px;">
				<i-col span="12" v-for="staff in staffList">
					<div @click="selectedStaff = staff"
					     style="margin-top:1px;margin-left:1px;background:#fff;padding:5px 10px;"
					     :style="{border}">
						<p style="font-size:14px;">
							{{staff.realname}}
							<span v-if="staff.staff_sn == staff.shop.manager_sn"
							      style="font-size:12px;background-color:#19be6b;color:#fff;padding:2px 4px;border-radius:2px;">
								店长
							</span>
							<span :style="{float:'right',color:staff.shopStatusColor}">{{staff.shopStatusName}}</span>
						</p>
						<p style="color:#999;">{{staff.staff_sn}} {{staff.position.name}}</p>
					</div>
				</i-col>
			</Row>
			<clock-record v-for="staff in staffList" :staff="staff"
			              @onLoad="function(status){getStatus(status,staff)}"
			              v-show="selectedStaff.staff_sn == staff.staff_sn"></clock-record>

		</mt-loadmore>
		<mt-popup v-model="shopPicker" position="right" style="width:90%;overflow:scroll;">
			<mt-search v-model="searchShop.keyWord" placeholder=" 搜索店铺">
				<mt-cell v-for="shop in searchShopResult" :title="shop.shop_sn" :label="shop.name"
				         @click.native="selectShop(shop.shop_sn,shop.name)"></mt-cell>
			</mt-search>
		</mt-popup>
	</div>
</template>


<script>
    import Flatpickr from 'flatpickr';
    import clockRecordComponent from './clock_record.vue';
    import {Loadmore, Popup, Search, Cell} from 'mint-ui';

    var components = {};
    components['clock-record'] = clockRecordComponent;
    components[Loadmore.name] = Loadmore;
    components[Popup.name] = Popup;
    components[Search.name] = Search;
    components[Cell.name] = Cell;

    const Chinese = require('../../flatpickr/l10ns/zh.js').zh;
    export default {
        data() {
            let newDate = new Date();
            let curDate = newDate.getFullYear() + '-' + (newDate.getMonth() + 1) + '-' + newDate.getDate();
            return {
                shopPicker: false,
                staffPicker: false,
                searchShop: {
                    keyWord: '',
                    dataSource: [],
                },
                staffList: [],
                selectedShop: {
                    shopSn: '',
                    shopName: ''
                },
                selectedStaff: {
                    staff_sn: '',
                    realname: ''
                }
            };
        },
        components: components,
        computed: {
            searchShopResult() {
                return this.searchShop.dataSource.filter((value) => {
                    let testShopSn = new RegExp(this.searchShop.keyWord, 'i').test(value.shop_sn);
                    let testName = new RegExp(this.searchShop.keyWord, 'i').test(value.name);
                    return testShopSn || testName;
                });
            }
        },
        watch: {},
        mounted() {
            this.getShopList();
        },
        methods: {
            refresh() {
                this.getShopList();
                if (this.selectedShop.shopSn) {
                    this.getStafflist();
                }
                this.$refs.loadmore.onTopLoaded();
            },
            getShopList() {
                Indicator.open('加载中...');
                let url = '/check/get_all_shop';
                axios.post(url).then((response) => {
                    this.searchShop.dataSource = response.data;
                    Indicator.close();
                }).catch((error) => {
                    if (error.response) {
                        document.write(error.response.data);
                    } else {
                        document.write(error.message);
                    }
                });
            },
            getStafflist() {
                Indicator.open('加载中...');
                let url = '/check/get_shop_staff';
                this.staffList = [];
                axios.post(url, {shop_sn: this.selectedShop.shopSn}).then((response) => {
                    this.staffList = response.data;
                    Indicator.close();
                }).catch((error) => {
                    if (error.response) {
                        document.write(error.response.data);
                    } else {
                        document.write(error.message);
                    }
                });
            },
            selectShop(shopSn, shopName) {
                this.selectedShop = {
                    shopSn: shopSn,
                    shopName: shopName
                };
                this.shopPicker = false;
                this.searchShop.keyWord = '';
                this.getStafflist();
            },
            getStatus(status, staff) {
                let statusText;
                let statusColor;
                switch (status) {
                    case 32:
                        statusColor = 'orange';
                        statusText = '请假';
                        break;
                    case 22:
                        statusColor = '#2d8cf0';
                        statusText = '调动';
                        break;
                    case 12:
                        statusColor = '#333';
                        statusText = '下班';
                        break;
                    case 11:
                        statusColor = '#19be6b';
                        statusText = '上班';
                        break;
                    case 0:
                        statusColor = '#999';
                        statusText = '未打卡';
                        break;
                }
                staff.shopStatusColor = statusColor;
                staff.shopStatusName = statusText;
            }
        }
    }
</script>
