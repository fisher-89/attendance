<template>
	<Row>
		<i-col span="4" style="text-align:center">
			{{workingStartAt.substring(0, 5)}}
		</i-col>
		<i-col span="16">
			<div class="ivu-progress-inner" style="overflow:hidden;margin-bottom:2px;">
				<div v-for="clock in clockLogArr"
				     :style="{'width': (clock.duration*100/duration)+'%','height':'10px','float':'left','background-color': clock.color}"></div>
			</div>
		</i-col>
		<i-col span="4" style="text-align:center">
			{{workingEndAt.substring(0, 5)}}
		</i-col>
	</Row>
</template>

<script>
    export default {
        data() {
            return {
                typeColor: {w: '#19be6b', t: '#2d8cf0', l: '#ff9900'}
            };
        },
        computed: {
            clockLogArr() {
                let clockLog = this.clockLog;
                let match = clockLog.match(/\d{4}\w\d{4}/);
                let response = [];
                while (match) {
                    let clock = this.clockLogDecode(match[0]);
                    if (response.length == 0 && clock.start_at > this.workingStartAt) {
                        let startTimestamp = new Date('2000/01/01 ' + this.workingStartAt).getTime();
                        let endTimestamp = new Date('2000/01/01 ' + clock.start_at).getTime();
                        let duration = (endTimestamp - startTimestamp) / 3600 / 1000;
                        response.push({
                            'start_at': this.workingStartAt,
                            'end_at': clock.start_at,
                            'duration': duration,
                            'color': ''
                        });
                    }
                    clockLog = clockLog.replace(/^.*?\d{4}\w(\d{4}.*)$/, '$1');
                    response.push(clock);
                    match = clockLog.match(/\d{4}\w\d{4}/);
                }
                return response;
            },
            duration() {
                let startTimestamp = new Date('2000/01/01 ' + this.workingStartAt).getTime();
                let endTimestamp = new Date('2000/01/01 ' + this.workingEndAt).getTime();
                let duration = (endTimestamp - startTimestamp) / 3600 / 1000;
                return duration;
            }
        },
        props: ['clockLog', 'workingStartAt', 'workingEndAt'],
        mounted() {
        },
        methods: {
            clockLogDecode(log) {
                let start = log.replace(/^(\d{2})(\d{2}).*$/, '$1:$2:00');
                let end = log.replace(/^.*(\d{2})(\d{2})$/, '$1:$2:00');
                let type = log.replace(/^\d{4}(\w)\d{4}$/, '$1');
                let startTimestamp = new Date('2000/01/01 ' + start).getTime();
                let endTimestamp = new Date('2000/01/01 ' + end).getTime();
                let duration = (endTimestamp - startTimestamp) / 3600 / 1000;
                return {
                    'start_at': start,
                    'end_at': end,
                    'duration': duration,
                    'color': this.typeColor[type]
                };
            }
        }
    }
</script>