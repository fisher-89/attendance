<template>
	<transition name="fade">
		<div class="searchstaff plr10" v-show="isShow">
			<div class="search-warp ">
				<input type="text" class="col-xs-8" v-model="keyword" >
				<button type="button" class="col-xs-4" @click="search">搜索</button>
			</div>
			<ul class="search-list" >
				<li @click="add(item)" v-for="item in staffList">{{item.realname}}</li>
			</ul>
		</div>
	</transition>

</template>

<script>
	import { Toast,Indicator } from 'mint-ui';

	export default{
		mounted(){
			log('search')
		},
		data(){
			return {
				keyword:'刘勇',
				staffList:[],
			}
		},
		props:['isShow'],
		methods:{
			search(){
				let _this = this;
				let url = '/api/attendance/searchstaff';
				this.staffList = [];
				Indicator.open('查询中...');
				axios.post(url,{realname:this.keyword}).then(function(response){
					if(response.data.status){
						response.data.message.forEach(function(val){
							val.achievement = '';
							_this.staffList.push(val);
							log(val)
						})
					}else{
						Toast('发生错误');
					}
					setTimeout(function(){
						Indicator.close();
					},1500)
				})
			},
			add(item){
				this.$parent.staffInfo.push(item);
				this.$parent.searchStaffStatus = false;
				this.staffList = [];
				// this.keyword = '';
			}
		}
	}
</script>

<style scoped>
	.searchstaff{
		position: fixed;
		left: 0;
		top: 0;
		background: #eee;
		width: 100%;
		height: 100%;
	}
	
	.searchstaff li{
		display: inline-block;
		padding: 6px;
		background: red;
		margin: 5px;
		cursor: pointer;
	}

	.fade-enter-active, .fade-leave-active {
		transition: opacity .5s
	}
	.fade-enter, .fade-leave-active {
		opacity: 0
	}
</style>