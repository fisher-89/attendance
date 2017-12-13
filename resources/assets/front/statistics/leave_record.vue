<template>
	<div>
		<div v-infinite-scroll="loadMore" infinite-scroll-disabled="false" infinite-scroll-immediate-check="true"
		     style="margin-bottom:-60px;">
			<Card v-for="(record,key) in records">
				<Row>
					<Tag type="border">{{leaveTypeSlots[0].values[record.type_id - 1].name}}</Tag>

					<Tag v-if="record.status == 0" type="border" color="blue">审批中</Tag>
					<Tag v-else-if="record.status == 1" type="border" color="green">已通过</Tag>
					<Tag v-else-if="record.status == -1" type="border" color="red">已驳回</Tag>
					<Tag v-else-if="record.status == -2" type="border">已取消</Tag>
				</Row>
				<Row>
					<i-col span="6">
						开始时间：
					</i-col>
					<i-col span="18">
						{{record.start_at | withoutSeconds}}
					</i-col>
				</Row>
				<Row>
					<i-col span="6">
						结束时间：
					</i-col>
					<i-col span="18">
						{{record.end_at | withoutSeconds}}
					</i-col>
				</Row>
				<Row>
					<i-col span="6">
						请假时长：
					</i-col>
					<i-col span="18">
						{{record.duration}}小时
					</i-col>
				</Row>
				<Row>
					<i-col span="6">
						审批人：
					</i-col>
					<i-col span="18">
						{{record.approver_name}}
					</i-col>
				</Row>
				<Row>
					<i-col span="6">
						请假原因：
					</i-col>
					<i-col span="18">
						{{record.reason}}
					</i-col>
				</Row>
			</Card>
			<div style="width:100%;height:60px;"></div>
		</div>
	</div>
</template>
<script>
    export default {
        data() {
            return {
                records: [],
                loading: false,
                leaveTypeSlots: []
            };
        },
        props: ['currentUser'],
        beforeMount() {
            axios.get('/leave/get_type').then((response) => {
                this.leaveTypeSlots = [{flex: 1, values: response.data}];
            });
        },
        mounted() {
            this.getLeaveRecord();
        },
        filters: {
            withoutSeconds: function (value) {
                if (value) {
                    return value.substring(0, 16);
                } else {
                    return '';
                }
            }
        },
        methods: {
            getLeaveRecord() {
                let _this = this;
                axios.post('/leave/record', {take: 10}).then(function (response) {
                    _this.records = response.data;
                })
            },
            loadMore() {
                if (this.loading) {
                    return false;
                }
                this.loading = true;
                Indicator.open('加载更多...');
                let _this = this;
                axios.post('/leave/record', {take: 10, skip: this.records.length}).then(function (response) {
                    if (response.data.length > 0) {
                        _this.records = _this.records.concat(response.data);
                    } else {
                        Toast('已经是最后了');
                    }
                    Indicator.close();
                    setTimeout(() => {
                        _this.loading = false;
                    }, 1000)
                })
            }
        }
    }
</script>
