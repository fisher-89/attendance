<template>
	<div class="substr" @click="showshow">
		<ul>
			<li v-for="item,index in listdata" @click="add(index)">{{item.uname}}</li>
		</ul>

		<transition name="fade">
			<p v-if="show">hello</p>
		</transition>
	</div>
</template>

<script>
	export default{
		data(){
			return {
				listdata:[
					{ uname:'liu5'},
					{ uname:'liu6'},
					{ uname:'liu7'},
				],
				show:false
			}
		},
		methods:{
			add(index){
				log('submit');
				// log(this.$parent.listdata.push(this.listdata(index)))
				// log(this.listdata)
				// this.show=!this.show
				this.$parent.subshow = false;
				// this.$parent.listdata.push(this.listdata[index])
				this.$emit('upup',this.listdata[index])
			},
			showshow(){
				this.$parent.subshow = false;
			}
		}
	}

</script>

<style>
	

</style>