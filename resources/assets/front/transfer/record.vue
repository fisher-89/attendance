<template>
	<div>
		<Collapse accordion v-infinite-scroll="loadMore"  infinite-scroll-disabled="false" infinite-scroll-immediate-check="true">
			<Panel v-for="(record,key) in records" style="padding:5px 0;">
				<Tag v-if="record.status == 0" type="border" color="yellow">待出发</Tag>
				<Tag v-else-if="record.status == 1" type="border" color="blue">在途</Tag>
				<Tag v-else-if="record.status == 2" type="border" color="green">已到达</Tag>
				<Tag v-else-if="record.status == -1" type="border" >已取消</Tag>
				<Tag v-if="record.status == 0"><Icon type="calendar" /> {{record.leaving_date}}</Tag>
				<Tag>{{record.arriving_shop_name}}</Tag>
				<Row slot="content">
					<Col span="22" offset="2">
						<Row v-if="record.status > 0">
							<Col span="10"><h4>{{record.left_at}}</h4></Col>
							<Col span="4"> </Col>
							<Col span="10"><h4>{{record.arrived_at}}</h4></Col>
						</Row>
						<Row>
							<Col span="5"><p>出发日期</p></Col>
							<Col span="1">：</Col>
							<Col span="18"><p>{{record.leaving_date}}</p></Col>
						</Row>
						<Row>
							<Col span="5"><p>开单人</p></Col>
							<Col span="1">：</Col>
							<Col span="18"><p>{{record.maker_name}}({{record.maker_sn}})</p></Col>
						</Row>
						<Row v-if="record.remark">
							<Col span="5"><p>备注</p></Col>
							<Col span="1">：</Col>
							<Col span="18"><p>{{record.remark}}</p></Col>
						</Row>
					</Col>
				</Row>
			</Panel>
		</Collapse>
	</div>
</template>
<script>
	export default{
		data(){
			return {
				records : [],
				loading:false,
			}
		},
		mounted(){
			this.gettransferrecord();
		},
		methods:{
			gettransferrecord(){
				let _this = this;
				axios.post('/api/transfer/record',{take:20}).then(function(response){
					_this.records = response.data;
				})
			},
			loadMore(){
				if(this.loading){
					return false;
				}
				this.loading = true;
				Indicator.open('加载更多...');
				let _this = this;
				axios.post('/api/transfer/record',{take:10,skip:this.records.length}).then(function(response){
					if(response.data.length>0){
						_this.records = _this.records.concat(response.data);
					}else{
						Toast('已经是最后了');
					}
					Indicator.close();
					setTimeout(()=>{
						_this.loading = false;
					},1000)
				})
			}
		}
	}
</script>