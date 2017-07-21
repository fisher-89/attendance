<template>
	<div class="recored">
		<ul class="recored_list" v-infinite-scroll="loadMore"  infinite-scroll-disabled="loading" infinite-scroll-immediate-check="true">
			<li class="o-hide where relative" v-for="item in recordList">
				<div class="status">
					{{item.status}}
				</div>
				<div class="">
					<p>{{item.out_shop_name}}</p>
					<p>2017-08-01</p>
				</div>
				<div class="cf22 mb5 mt5">
					调至
				</div>
				<div class="">
					<p>{{item.go_shop_name}}</p>
					<p>2017-08-03</p>
				</div>
			</li>
		</ul>
	</div>
</template>
<script>
	import { Toast,Indicator,InfiniteScroll  } from 'mint-ui';

	export default{
		data(){
			return {
				recordList : [],
				loading:false,
				skip:1,
			}
		},
		mounted(){
			// log('recored');
			this.gettransferrecord();
		},
		methods:{
			gettransferrecord(){
				let _this = this;
				axios.post('/api/transfer/record',{take:10}).then(function(response){
					if(response.data.status){
						response.data.data.forEach(function(val){
							_this.recordList.push(val);
						})
					}
					log(response.data);
				})
			},
			loadMore(){
				Indicator.open('加载更多...');
				let _this = this;
				this.skip++;
				axios.post('/api/transfer/record',{take:10,skip:this.skip}).then(function(response){
					if(response.data.status){
						response.data.data.forEach(function(val){
							_this.recordList.push(val);
						})
					}
					setTimeout(function(){
						Indicator.close();
					},1500)
				})			
			}
		}
	}
</script>

<style scoped>
	.where{
		padding: 10px;
		border-bottom: 1px solid #eee;
	}
	.status{
		position: absolute;
		right: 20px;
		top: 40px;
		color:red;
	}
</style>