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
		<mt-loadmore :top-method="reLogin" ref="loadmore" disabled style="overflow:visible;">
			<Card :shadow="true">
				<Row>
					<i-col span="4">
						<Avatar icon="person" size="large"/>
					</i-col>
					<i-col span="13">
						<h3>{{currentUser.realname}}</h3>
						<small>{{currentUser.staff_sn}}</small>
					</i-col>
					<i-col span="7">
						<p style="text-align:right;">
							&nbsp;
							<Button v-if="currentUser.is_manager" @click="staffPicker = true" type="ghost" size="small">
								店长代签
							</Button>
						</p>
					</i-col>
				</Row>
				<Row style="border-bottom:1px solid #e9eaec;">
					<i-col span="16" v-if="currentUser.shop_sn">
						<h4 style="white-space:nowrap;over-flow:hidden;text-overflow:ellipsis;">
							{{currentUser.shop.name}}
						</h4>
						<small>店铺编码:{{currentUser.shop_sn}}&nbsp;&nbsp;店长:{{currentUser.shop_manager_name}}</small>
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
				<template v-if="clockInClose">
					<Clock :current-user.sync="currentUserClock" :refresh.sync="clockRefresh" :date.sync="date"
					       :assist="false"></Clock>
				</template>
				<template v-else>
					<ClockIn :current-user="currentUserClock" :close.sync="clockInClose"></ClockIn>
				</template>
			</Card>
		</mt-loadmore>
		<mt-actionsheet v-if="currentUser.is_manager" v-model="staffPicker" :actions="selectStaff"></mt-actionsheet>
		<mt-popup position="right" v-model="assistPage" style="width:100%;top:37.5%;bottom:-12.5%;overflow:scroll;">
			<Assist :staff-sn="selectedStaffSn"></Assist>
		</mt-popup>
	</div>
</template>


<script>
    import Flatpickr from 'flatpickr';
    import timeLineComponent from './timeLine.vue';
    import clockInComponent from './clockIn.vue';
    import assistComponent from './assist.vue';
    import {Loadmore, Actionsheet, Popup} from 'mint-ui';

    var components = {};
    components['Clock'] = timeLineComponent;
    components['ClockIn'] = clockInComponent;
    components['Assist'] = assistComponent;
    components[Loadmore.name] = Loadmore;
    components[Actionsheet.name] = Actionsheet;
    components[Popup.name] = Popup;

    const Chinese = require('../../flatpickr/l10ns/zh.js').zh;
    export default {
        data() {
            let newDate = new Date();
            let curDate = newDate.getFullYear() + '-' + (newDate.getMonth() + 1) + '-' + newDate.getDate();
            let curTime = newDate.getHours() + '' + newDate.getMinutes();
            let clockInTrigger = curTime < 850 || curTime > 905;
            return {
                date: '',			    //选择的日期
                curDate: curDate,
                showCalendar: false,	//是否显示日历
                staffPicker: false,
                selectedStaffSn: false,
                assistPage: false,
                currentUserClock: this.currentUser,
                clockRefresh: false,
                clockInClose: clockInTrigger
            };
        },
        props: ['currentUser'],
        components: components,
        computed: {
            selectStaff() {
                if (this.currentUser.is_manager) {
                    let response = [];
                    this.currentUser.shop_staff.map((item) => {
                        if (item.staff_sn !== this.currentUser.staff_sn) {
                            response.push({
                                name: item.realname,
                                method: () => {
                                    this.selectedStaffSn = item.staff_sn;
                                    setTimeout(() => {
                                        this.assistPage = true;
                                    }, 800);
                                }
                            });
                        }
                    });
                    return response;
                } else {
                    return [];
                }
            }
        },
        watch: {
            currentUserClock(newValue) {
                this.$emit('update:currentUser', newValue);
            }
        },
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
            reLogin() {
                sessionStorage.clear();
                axios('/re_login').then((response) => {
                    sessionStorage.setItem('staff', JSON.stringify(response.data));
                    this.currentUserClock = response.data;
                    this.clockRefresh = true;
                    this.$refs.loadmore.onTopLoaded();
                });
            },
            toggleStaffPicker() {
                this.staffPicker = !this.staffPicker;
            }
        }
    }
</script>
