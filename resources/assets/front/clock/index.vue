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
		<Card :shadow="true">
			<Row>
				<i-col span="4">
					<Avatar icon="person" size="large"/>
				</i-col>
				<i-col span="9">
					<h3>{{currentUser.realname}}</h3>
					<small>{{currentUser.staff_sn}}</small>
				</i-col>
				<i-col span="7">
					<p style="text-align:right;">
						&nbsp;
						<!--<Button v-if="currentUser.is_manager" @click="staffPicker = true" type="ghost" size="small">-->
						<!--店长代签-->
						<!--</Button>-->
					</p>
				</i-col>
				<i-col span="4" style="text-align:right;">
					<Button type="warning" size="small" @click="reLogin" icon="loop"
					        shape="circle"></Button>
				</i-col>
			</Row>
			<Row style="border-bottom:1px solid #e9eaec;">
				<i-col span="16" v-if="currentUser.shop_sn">
					<h4 style="white-space:nowrap;over-flow:hidden;text-overflow:ellipsis;">
						{{currentUser.shop.name}}
					</h4>
					<small>店长：{{currentUser.shop_manager_name}}</small>
				</i-col>
				<i-col span="16" v-else>
					<h4>&nbsp;</h4>
					<small>&nbsp;</small>
				</i-col>
				<i-col span="8" :class="showCalendar?'calendar-show':'calendar-hide'" id="calendar">
					<Button @click="showCalendar = !showCalendar" size="small" type="primary"
					        style="float:right;white-space:nowrap;margin-top:6px;">
						{{date}}
						<Icon :type="showCalendar?'arrow-up-b':'arrow-down-b'"/>
					</Button>
				</i-col>
			</Row>
			<Clock :current-user="currentUser" :date.sync="date"></Clock>
		</Card>
		<!--<mt-actionsheet v-if="currentUser.is_manager" v-model="staffPicker" :actions="selectStaff"></mt-actionsheet>-->
		<!--<mt-popup position="right" v-model="assistPage" style="width:100%;top:37.5%;bottom:-12.5%;overflow:scroll;">-->
		<!--<Assist :staff-sn="selectedStaffSn"></Assist>-->
		<!--</mt-popup>-->
	</div>
</template>


<script>
    import Flatpickr from 'flatpickr';
    import timeLineComponent from './timeLine.vue';
    import assistComponent from './assist.vue';
    import {Actionsheet, Popup} from 'mint-ui';

    var components = {};
    components['Clock'] = timeLineComponent;
    components['Assist'] = assistComponent;
    components[Actionsheet.name] = Actionsheet;
    components[Popup.name] = Popup;

    const Chinese = require('../../flatpickr/l10ns/zh.js').zh;
    export default {
        data() {
            let selectStaff = [];
            if (this.currentUser.is_manager) {
                this.currentUser.shop_staff.map((item) => {
                    if (item.shop_duty_id != 1) {
                        selectStaff.push({
                            name: item.staff_name,
                            method: () => {
                                this.selectedStaffSn = item.staff_sn;
                                setTimeout(() => {
                                    this.assistPage = true;
                                }, 1000);
                            }
                        });
                    }
                });
            }
            return {
                date: '',			    //选择的日期
                showCalendar: false,	//是否显示日历
                staffPicker: false,
                selectStaff: selectStaff,
                selectedStaffSn: false,
                assistPage: false,
            };
        },
        props: ['currentUser'],
        components: components,
        computed: {},
        filters: {},
        beforeMount() {
        },
        mounted() {
            Flatpickr("#calendar", {
                inline: true,
                locale: Chinese,
                defaultDate: 'today',
                maxDate: 'today',
                onChange: (selectedDates, dateStr) => {
                    this.date = dateStr;
                }
            });
        },
        methods: {
            reLogin() {
                sessionStorage.clear();
                axios('/re_login').then((response) => {
                    sessionStorage.setItem('staff', JSON.stringify(response.data));
                    this.$emit('update:currentUser', response.data);
                });
            },
            toggleStaffPicker() {
                this.staffPicker = !this.staffPicker;
            }
        }
    }
</script>
