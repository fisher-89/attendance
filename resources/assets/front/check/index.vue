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
			<mt-cell title="店铺" label="点击选择店铺" @click.native="shopPicker = true">
				<span style="font-size:12px;">
					{{selectedShop.shopName}}<br>
					{{selectedShop.shopSn}}
				</span>
			</mt-cell>
			<mt-cell title="员工" label="点击选择员工" @click.native="staffPicker = true">
				<span>
					{{selectedStaff.realname}} {{selectedStaff.staff_sn}}
				</span>
			</mt-cell>

			<Card :shadow="true" style="margin-top:10px;" v-show="selectedStaff.staff_sn">
				<Row>
					<i-col span="4">
						<Avatar icon="person" size="large"/>
					</i-col>
					<i-col span="13">
						<h3>{{selectedStaff.realname}}</h3>
						<small>{{selectedStaff.staff_sn}}</small>
					</i-col>
				</Row>
				<Row style="border-bottom:1px solid #e9eaec;">
					<i-col span="16" v-if="selectedStaff.shop_sn">
						<h4 style="white-space:nowrap;over-flow:hidden;text-overflow:ellipsis;">
							{{selectedStaff.shop.name}}
						</h4>
						<small>店铺编码:{{selectedStaff.shop_sn}}</small>
					</i-col>
					<i-col span="16" v-else>
						<h4>&nbsp;</h4>
						<small>&nbsp;</small>
					</i-col>
					<i-col span="8" :class="showCalendar?'calendar-show':'calendar-hide'" id="calendar">
						<Button @click="showCalendar = !showCalendar" size="small" type="primary"
						        style="float:right;white-space:nowrap;margin-top:6px;">
							{{date == '' ? curDate : date}}
							<Icon :type="showCalendar?'arrow-up-b':'arrow-down-b'"/>
						</Button>
					</i-col>
				</Row>
				<template>
					<Clock :current-user="selectedStaff" :refresh.sync="clockRefresh" :date.sync="date"
					       :assist="false" :clock-permission="false"></Clock>
				</template>
			</Card>
		</mt-loadmore>
		<mt-popup v-model="shopPicker" position="right" style="width:90%;overflow:scroll;">
			<mt-search v-model="searchShop.keyWord" placeholder=" 搜索店铺">
				<mt-cell v-for="shop in searchShopResult" :title="shop.shop_sn" :label="shop.name"
				         @click.native="selectShop(shop.shop_sn,shop.name)"></mt-cell>
			</mt-search>
		</mt-popup>
		<mt-actionsheet v-model="staffPicker" :actions="staffList"></mt-actionsheet>
	</div>
</template>


<script>
    import Flatpickr from 'flatpickr';
    import timeLineComponent from '../clock/timeLine.vue';
    import {Loadmore, Actionsheet, Popup, Search, Cell} from 'mint-ui';

    var components = {};
    components['Clock'] = timeLineComponent;
    components[Loadmore.name] = Loadmore;
    components[Actionsheet.name] = Actionsheet;
    components[Popup.name] = Popup;
    components[Search.name] = Search;
    components[Cell.name] = Cell;

    const Chinese = require('../../flatpickr/l10ns/zh.js').zh;
    export default {
        data() {
            let newDate = new Date();
            let curDate = newDate.getFullYear() + '-' + (newDate.getMonth() + 1) + '-' + newDate.getDate();
            return {
                date: '',			    //选择的日期
                curDate: curDate,
                showCalendar: false,	//是否显示日历
                clockRefresh: false,
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
            Flatpickr("#calendar", {
                inline: true,
                locale: Chinese,
                defaultDate: 'today',
                maxDate: 'today',
                onChange: (selectedDates, dateStr) => {
                    this.date = dateStr;
                    this.clockRefresh = true;
                }
            });
            this.getShopList();
        },
        methods: {
            refresh() {
                this.clockRefresh = true;
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
                axios.post(url, {shop_sn: this.selectedShop.shopSn}).then((response) => {
                    this.staffList = [];
                    response.data.map((item) => {
                        this.staffList.push({
                            name: item.realname,
                            method: () => {
                                this.selectedStaff = item;
                                this.clockRefresh = true;
                            }
                        });
                    });
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
            }
        }
    }
</script>
