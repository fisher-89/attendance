<template>
	<div class="wrap">
		<p><button @click="add()">+++++</button></p>
		<div class="int mb10" v-for="item in inArr">
			<label > <input type="text" v-model="item.uname" ></label>
		</div>
		<p>
			<button type="commit" @click="sub()">commit</button>
		</p>
		
	</div>
</template>

<script>
	
	export default{
		data(){
			return {
				inArr:[{uname:'liu'}]
			}
		},
		methods:{
			add(){
				this.inArr.push({})
			},
			sub(){
				log(this.inArr)
			}
		}
	}

</script>