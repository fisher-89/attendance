<template>
	<div class="wrap">
		<ul class="nav">
			<li v-show="isOwner">
				<router-link to="/f/attend">店铺考勤录入</router-link>
			</li>
			<li v-show="isOwner">
				<router-link to="/f/attendrecord">店铺考勤记录</router-link>
			</li>
			<li>
				<router-link to="/f/sign">个人考勤</router-link>
			</li>
			<li>
				<router-link to="/f/record">考勤记录</router-link>
			</li>
			<li>
				<router-link to="/f/trip">调动行程</router-link>
			</li>
			<li v-show="isOwner">
				<router-link to="/f/confirm">店长确认</router-link>
			</li>
			<li>
				<router-link to="/f/transrecord">调动记录</router-link>
			</li>
		</ul>
	</div>
</template>

<script>

	export default{
		mounted(){
			this.getuser();
			
		},
		data(){
			return {
				isOwner:false,
			}
		},
		methods:{
			getuser(){

				let _this = this;
				let cursn = sessionStorage.getItem('usersn');
				if(cursn == null){}
					axios.get( '/api/getuser' ).then(function(response){
						if(response.data.status){
							sessionStorage.setItem("usersn", response.data.data.usersn);
							sessionStorage.setItem("username", response.data.data.username);
							if(response.data.data.shopsn){
								sessionStorage.setItem("shopsn",1);
								_this.isOwner = true;
							}else{
								sessionStorage.setItem('shopsn',0)
							}
						}
					})
				if(sessionStorage.getItem('shopsn') > 0 ){
					this.isOwner = sessionStorage.getItem('shopsn');
				}
				
			}
		}
	}
</script>

<style scoped>

 .wrap{ background:#c7000b; height:100%; padding:10px; }
	 .wrap .nav { height:100%; border-radius:3px; padding:10px; border:1px solid #fff;
	 box-shadow: 0 0px 3px  #ccc inset,0 0px 3px  #ccc; 
	 }
.nav li{
	padding-top: 10px;
	padding-bottom: 10px;
	text-align: center;
	border:1px solid #ccc;
	margin:10px 0 0 0;
	border-radius:10px;
	 background:#fff;
	 letter-spacing:2px;
}
	
</style>