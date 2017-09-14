<template>
	<div class="component">
		<h2>component</h2>
		<!-- <ul>
			<li v-for="item,index in listdata">{{item.uname}}  <button type="button" @click="del(index)"> -- </button></li>
		</ul> -->

		<transition-group name="list" tag="div">
			<span v-for="item,index in listdata" v-bind:key="item" class="list-item">
				{{ item.uname }} 
				<button type="button" @click="del(index)"> -- </button>
			</span>
		</transition-group>
		<button @click="add">添加</button>

		<button @click="adddel">删除</button>
		<transition name="fade">
			<!-- <p v-if="show">hello</p> -->
			<sub-com @upup="upups" v-if="subshow"></sub-com> 
		</transition>
		
	</div>
</template>

<script>
	import subCom from './sub.vue';
	export default{
		data(){
			return {
				listdata:[
					{uname:'liu'},
					{uname:'liu2'},
					{uname:'liu3'},
				],
				subshow:false,
				subshows:true
			}
		},
		mounted(){
			console.log('component') 
		},
		components:{
			subCom
		},
		methods:{
			add(){
				 
				this.subshow = true;
			},
			adddel(){
				this.subshow = false;
			},
			del(index){
				this.listdata.splice(index,1);
			},
			upups(params){
				this.listdata.push(params);
				// this.listdata.splice(2,0,params)
				console.log(params)
			}
		}
	}
	


</script>


<style scoped>
.component{
	height: 3000px;
}
.substr{
	 
    position: fixed;
    top: 0;
    left: 0;
    background: #EEF;
    height: 100%;
    width: 100%;
}

.list-item {
  display: inline-block;
  margin-right: 10px;
}
.list-enter-active, .list-leave-active {
  transition: all 1s;
}
.list-enter, .list-leave-to /* .list-leave-active for <2.1.8 */ {
  opacity: 0;
  transform: translateY(30px);
}

</style>