<template>
	<div>
		<Collapse accordion
		          v-infinite-scroll="loadMore"
		          infinite-scroll-disabled="false"
		          infinite-scroll-immediate-check="true"
		>
			<Panel v-for="(record,key) in records">
				<Tag v-if="record.status == 0" type="border" color="yellow">待出发</Tag>
				<Tag v-else-if="record.status == 1" type="border" color="blue">在途</Tag>
				<Tag v-else-if="record.status == 2" type="border" color="green">已到达</Tag>
				<Tag v-else-if="record.status == -1" type="border">已取消</Tag>
				<Tag type="border" style="max-width:200px;">
					{{record.arriving_shop_name || '无到达店铺'}}
				</Tag>
				<Row slot="content">
					<i-col span="22" offset="2">
						<Row>
							<i-col span="5"><p>预计日期</p></i-col>
							<i-col span="1">：</i-col>
							<i-col span="18"><p>{{record.leaving_date}}</p></i-col>
						</Row>
						<Row v-if="record.leaving_shop_sn">
							<i-col span="5"><p>出发店铺</p></i-col>
							<i-col span="1">：</i-col>
							<i-col span="18"><p>{{record.leaving_shop_name}}</p></i-col>
						</Row>
						<Row v-if="record.status > 0">
							<i-col span="5"><p>出发时间</p></i-col>
							<i-col span="1">：</i-col>
							<i-col span="18"><p>{{record.left_at}}</p></i-col>
						</Row>
						<Row v-if="record.arriving_shop_sn">
							<i-col span="5"><p>到达店铺</p></i-col>
							<i-col span="1">：</i-col>
							<i-col span="18"><p>{{record.arriving_shop_name}}</p></i-col>
						</Row>
						<Row v-if="record.status > 1">
							<i-col span="5"><p>到达时间</p></i-col>
							<i-col span="1">：</i-col>
							<i-col span="18"><p>{{record.arrived_at}}</p></i-col>
						</Row>
						<Row>
							<i-col span="5"><p>开单人</p></i-col>
							<i-col span="1">：</i-col>
							<i-col span="18"><p>{{record.maker_name}}({{record.maker_sn}})</p></i-col>
						</Row>
						<Row v-if="record.remark">
							<i-col span="5"><p>备注</p></i-col>
							<i-col span="1">：</i-col>
							<i-col span="18"><p>{{record.remark}}</p></i-col>
						</Row>
					</i-col>
				</Row>
			</Panel>
		</Collapse>
	</div>
</template>
<script>
    export default {
        data() {
            return {
                records: [],
                loading: false,
            }
        },
        mounted() {
            this.gettransferrecord();
        },
        methods: {
            gettransferrecord() {
                let _this = this;
                axios.post('/transfer/record', {take: 20}).then(function (response) {
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
                axios.post('/transfer/record', {take: 10, skip: this.records.length}).then(function (response) {
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