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
	<Card :shadow="true" style="margin-top:10px;" v-show="staff.staff_sn">
		<Row>
			<i-col span="4">
				<Avatar icon="person" size="large"/>
			</i-col>
			<i-col span="13">
				<h3>{{staff.realname}}</h3>
				<small>{{staff.staff_sn}}</small>
			</i-col>
		</Row>
		<Row style="border-bottom:1px solid #e9eaec;">
			<i-col span="16" v-if="staff.shop_sn">
				<h4 style="white-space:nowrap;over-flow:hidden;text-overflow:ellipsis;">
					{{staff.shop.name}}
				</h4>
				<small>店铺编码:{{staff.shop_sn}}</small>
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
			<Clock :current-user="staff" :refresh.sync="clockRefresh" :date.sync="date"
			       :assist="false" :clock-permission="false" @onLoad.once="getStatus"></Clock>
		</template>
	</Card>
</template>


<script>
    import Flatpickr from 'flatpickr';
    import timeLineComponent from '../clock/timeLine.vue';
    import {Loadmore, Actionsheet, Popup, Search, Cell} from 'mint-ui';

    var components = {};
    components['Clock'] = timeLineComponent;
    components[Loadmore.name] = Loadmore;

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
            };
        },
        props: ['staff'],
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
        },
        methods: {
            getStatus(child) {
                let status;
                if (child.isLeaving) {
                    status = 32;
                } else if (child.isTransferring) {
                    status = 22;
                } else if (child.hasClockOut) {
                    status = 12;
                } else if (child.hasClockIn) {
                    status = 11;
                } else {
                    status = 0;
                }
                this.$emit('onLoad', status);
            }
        }
    }
</script>
