<template>
	<div class="transfer">
		<div class="hints">{{hints}}</div>
		<div class="area" v-show="btnArea">
			<div class="out_shop" @click="outshop">
				<span >离店</span>
			</div>
			<div class="go_shop" @click="goshop">
				<span >到店</span>
			</div>
		</div>
	</div>
</template>
<style>
	

</style>

<script>
	import { Toast,Indicator  } from 'mint-ui';
	export default{
		data(){
			return {
				hints:null,
				btnArea:null,
				recordid:null,
			}
		},
		mounted(){
			this.transferStatus();
			// log(sessionStorage.getItem('usersn'))
		},
		methods:{
			outshop(){
				if(!this.recordid) return false;
				axios.post('/api/transfer/outshop',{tid:this.recordid}).then(function(response){
					Toast(response.data.msg)
				})
				
				// log(sessionStorage.getItem('usersn'))
			},
			goshop(){
				if(!this.recordid) return false;
				let _this = this;
				axios.post('/api/transfer/goshop',{tid:this.recordid}).then(function(response){
					if(response.data.status){
						// _this.btnArea = !response.data.status;
						// setTimeout(function(){ 
						// 	_this.$router.push('/f');
						// },1000)						
					}
					Toast(response.data.msg)
					log(response.data)
				})
			},
			transferStatus(){
				let _this = this;

				Indicator.open('Loading...');

				axios.post('/api/transferstatus').then(function(response){
					if(response.data.status){
						_this.btnArea = response.data.status;
						_this.recordid = response.data.data.id;
					}else{
						_this.hints = response.data.msg;
					}
					setTimeout(function(){
						Indicator.close();
					},1000)
				})
			}
		}
	}


</script>

<style scoped>
	/*.go_shop,.out_shop{
		width: 100px;
		height: 100px;
		border-radius: 50%;
		background: #eee;
		position: relative;
	}*/
	/*.go_shop span,.out_shop span{
		display: block;
		padding-top: 30px;
		text-align: center;
	}*/
	.hints { text-align:center; padding:20px 0;}
</style>