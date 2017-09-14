<template>
	<div class="wrap">
		<table width="100%">
			<th v-for="item in tableData.th">
				<td >{{item}}</td>
			</th>

			<tr v-for="item,index in tableData.tr">
				<td v-for="itemSub in item">{{itemSub}}</td>
				<td>
					<a href="javascript:;" @click="edit(index)">编辑</a>
					<a href="javascript:;" @click="del(index)">删除</a>
				</td>
			</tr>
		</table>
		<ul  class="pt10">
			<!-- <li v-for="item,index in editData">
				<label >
					{{tableData.th.index}}  
					<input type="text"  v-model="editData.index" >{{item}}
				</label>
			</li>
 -->
 			<li >
 				<!-- {{item}} -->
 				<label >
 					<!-- {{index}} -->
 					<!-- <input type="text" v-model=""> -->
 					<!-- <p>{{this.tableData.tr[this.curIndex]}}</p> -->
 					<!-- <input type="text" v-model="">  -->
 				</label>
 				
 			</li>
			<button @click="sub()">submit</button>
		</ul>

		<!-- <p v-for="item in this.tableData.tr[this.curIndex]">
			{{item}}
		</p>
		<p>
			<input type="text" v-model="this.tableData.tr[this.curIndex].dd">
		</p> -->

		<!-- <label v-for="item in this.tableData.tr[this.curIndex]">{{item.dd}} <input type="text" v-model="item.dd" ></label>  -->
		<!-- <label > <input type="text" v-model="this.tableData.tr[this.curIndex].dd"></label> -->
		<p class="mt20 mb20">---------------------------------</p>

		<ul>
			<li v-for="item,index in tableData.th">
				<span v-if="item !=='操作'">{{item}}</span><input type="text" v-model="tableData.tr[curIndex][tKeyArr[index]]">
			</li>
		</ul>

		<p class="mt20 mb20">---------------------------------</p>

		<!-- <ul><li v-for="item in tKeyArr"></li></ul> -->
			
				<!-- <input type="text" > -->
				<!-- <span>{{tableData.tr[0].item}}</span> -->

			
		

		<p class="mt20 mb20">---------------------------------</p>
	</div>
</template>

<script>
	export default{
		data(){
			return {
				curIndex:0,
				tKeyArr:[]
			}
		},
		props:['tableTh','tableTr','tableData'],
		methods:{
			modi(){
				this.$parent.liu = 'sldjkflskd'
			},
			edit(index){
				// this.editData = dd;
				// this.curIndex = this.tableData.tr[index];
				this.curIndex = index;
				// console.log(this.curIndex);

				console.log(index)
			},
			del(index){
				// this.tableData.splice(index,1);
				this.tableData.tr.splice(index,1)
				log(index)
				 
			},
			sub(){
				// this.$parent.tableData.
				// console.log(this.tableData.tr[this.curIndex])
				console.log(this.tableData.tr[0])
			}
		},
		mounted(){
			let obj = this.tableData.tr[0];
			for(let items in obj){
				// log(items);
				this.tKeyArr.push(items);
			}
			log(this.tKeyArr);
		}
	}


</script>