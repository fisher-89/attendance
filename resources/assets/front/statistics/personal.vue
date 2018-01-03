<template>
	<div>
		<!--<h2>{{curYear}}年{{curMonth}}月</h2>-->
		<div>
			<span id="calendar"></span>
		</div>
		<Card :style="{fontWeight:700,marginTop:'5px'}">
			<p>
				出勤：{{(workingDays + transferringDays).toFixed(2)}} 天
				<span :style="{color:'#999',fontSize:'',fontWeight:400,fontSize:'12px'}">上班{{workingDays.toFixed(2)}}天 调动{{transferringDays.toFixed(2)}}天</span>
			</p>
			<p>请假：{{leavingDays.toFixed(2)}} 天</p>
			<p v-if="noRecordDays>0" :style="{color:'#ed3f14'}">无记录：{{noRecordDays}} 天</p>
			<p v-if="missingDays>0" :style="{color:'#ed3f14'}">漏签：{{missingDays}} 天</p>
			<p>销售业绩：{{salesPerformance.toFixed(2)}} 元</p>
		</Card>
		<Card v-if="selectedDate">
			<p slot="title">{{selectedDate}}</p>
			<Clock :current-user.sync="currentUser" :date.sync="selectedDate" :refresh.sync="clockRefresh"
			       :assist="false" :clock-permission="false"></Clock>
		</Card>
	</div>
</template>
<script>
    import Flatpickr from 'flatpickr';

    const Chinese = require('../../flatpickr/l10ns/zh.js').zh;

    import timeLineComponent from '../clock/timeLine.vue';

    var components = {};
    components['Clock'] = timeLineComponent;

    export default {
        data() {
            let newDate = new Date();
            let curYear = newDate.getFullYear();
            let curMonth = (Array(2).join(0) + (newDate.getMonth() + 1)).slice(-2);
            return {
                curYear: curYear,
                curMonth: curMonth,
                record: [],
                selectedDate: false,
                clockRefresh: false,
                salesPerformance: 0,
                workingDays: 0,
                transferringDays: 0,
                leavingDays: 0,
                missingDays: 0,
                noRecordDays: 0,
                calandar: false,
            };
        },
        props: ['currentUser'],
        components: components,
        computed: {
//            missingDays() {
//                let dateObject = new Date(this.curYear, this.curMonth - 1, 0);
//                let days = dateObject.getDate();
//                return days - this.record.length;
//            }
        },
        mounted() {
            this.calandar = Flatpickr("#calendar", {
                inline: true,
                locale: Chinese,
                dateFormat: 'Y-m-d',
                maxDate: 'today',
                minDate: '2017-11-01',
                onMonthChange: (selectedDates, dateStr, instance) => {
                    Indicator.open('加载中...');
                    this.curMonth = instance.currentMonth + 1;
                    this.curYear = instance.currentYear;
                    setTimeout(this.getReport, 500);
                },
                onYearChange: (selectedDates, dateStr, instance) => {
                    Indicator.open('加载中...');
                    this.curMonth = instance.currentMonth + 1;
                    this.curYear = instance.currentYear;
                    setTimeout(this.getReport, 500);
                },
                onChange: (selectedDates, dateStr, instance) => {
                    this.selectedDate = dateStr;
                    this.clockRefresh = true;
                    this.curMonth = instance.currentMonth + 1;
                    this.curYear = instance.currentYear;
                    this.renderCalendar();
                },
                onDayCreate: (dObj, dStr, fp, dayElem) => {
                    let year = dayElem.dateObj.getFullYear();
                    let month = (Array(2).join(0) + (dayElem.dateObj.getMonth() + 1)).slice(-2);
                    let date = (Array(2).join(0) + dayElem.dateObj.getDate()).slice(-2);
                    dayElem.id = 'calandar-' + year + '-' + month + '-' + date;
                }
            });
            Indicator.open('加载中...');
            setTimeout(this.getReport, 500);
        },
        methods: {
            getReport() {
                let params = {
                    staff_sn: this.currentUser.staff_sn,
                    month: this.curYear + '' + this.curMonth
                };
                axios.post('/statistics/personal', params).then((response) => {
                    let attendance;
                    for (let i in response.data) {
                        attendance = response.data[i];
                        this.record[attendance.attendance_date] = attendance;
                    }
                    this.renderCalendar();
                    Indicator.close();
                });
            },
            renderCalendar() {
                let cell;
                let date;
                let attendance;
                let html;
                let dayContainer = this.calandar.days.getElementsByTagName('span');

                this.salesPerformance = 0;
                this.workingDays = 0;
                this.transferringDays = 0;
                this.leavingDays = 0;
                this.missingDays = 0;
                this.noRecordDays = 0;

                for (let i = 0; i < dayContainer.length; i++) {
                    cell = dayContainer[i];
                    if (!cell.className.match(/(MonthDay|disabled)/)) {
                        date = cell.id.replace(/calandar\-(.*)/, '$1');
                        attendance = this.record[date];
                        html = '';
                        if (attendance && attendance.is_missing > 0) {
                            this.missingDays++;
                            html += '<div style="position:absolute;top:0;left:0;border-top:10px solid #ed3f14;' +
                                'border-right:10px solid transparent;"></div>';
                        } else if (attendance) {
                            this.salesPerformance += parseFloat(attendance.sales_performance);
                            this.workingDays += parseFloat(attendance.working_days);
                            this.transferringDays += parseFloat(attendance.transferring_days);
                            this.leavingDays += parseFloat(attendance.leaving_days);
                            if (attendance.status <= 0) {
                                html += '<div style="position:absolute;top:0;left:0;border-top:10px solid #2d8cf0;' +
                                    'border-right:10px solid transparent;"></div>';
                            }
                            if (attendance.late_time > 0) {
                                html += '<div style="position:absolute;bottom:2px;margin-left:-3px;left:10%;' +
                                    'background-color:#ed3f14;width:6px;height:6px;border-radius:50%;"></div>';
                            }
                            if (attendance.early_out_time > 0) {
                                html += '<div style="position:absolute;bottom:2px;margin-left:-3px;left:30%;' +
                                    'background-color:#ed3f14;width:6px;height:6px;border-radius:50%;"></div>';
                            }
                            if (attendance.working_days > 0) {
                                html += '<div style="position:absolute;bottom:2px;margin-left:-3px;left:50%;' +
                                    'background-color:#19be6b;width:6px;height:6px;border-radius:50%;"></div>';
                            }
                            if (attendance.is_leaving > 0) {
                                html += '<div style="position:absolute;bottom:2px;margin-left:-3px;left:70%;' +
                                    'background-color:orange;width:6px;height:6px;border-radius:50%;"></div>';
                            }
                            if (attendance.is_transferring > 0) {
                                html += '<div style="position:absolute;bottom:2px;margin-left:-3px;left:90%;' +
                                    'background-color:#2d8cf0;width:6px;height:6px;border-radius:50%;"></div>';
                            }
                        } else {
                            this.noRecordDays++;
                            html += '<div style="position:absolute;top:0;left:0;border-top:10px solid #999999;' +
                                'border-right:10px solid transparent;"></div>';
                        }
                        cell.innerHTML += html;
                    }
                }
            }
        }
    }
</script>
