<template>

	<div class="record pt20">
		<table border='1' align='center' width='100%' cellspacing='0'>
			<tr>
				<td align='center' v-for="item in week">{{item}}</td>
				 
			</tr>
			<tr v-for="(item,index) in list" class="borb_eee calendar">
				<td align='center' v-for="items in item">
					<div v-if="items < curIdx+1 && items > 0">
						<span @click="selects(items)" :class="[(items === curIdx)?'active':'',(items === curClick)?'bor':'']">{{items}}</span>
					</div>
					<div v-if="items > curIdx && items > 0">
						<span style="background:#eee">{{items}}</span>
					</div>
					
				</td>
			</tr>
		</table>
		<div class="hints mt20 li_record">
			<div class="o-hide p10">
				<h2>打卡记录</h2>
				<ul class="fl mr10">
					<li v-for="item in toworkRes">
						<span>{{item.sign_time}}</span>
					</li>
				</ul>
				<ul class="fl">
					<li v-for="item in offworkRes">
						<span>{{item.down_time}}</span>
					</li>
				</ul>
			</div>
		</div>
	</div>
</template>

<script>
	import { Toast,Indicator  } from 'mint-ui';
	export default{
		data(){
			return {
				list:null,
				curIdx:0,
				curClick:0,
				toworkRes:null,
				offworkRes:null,
				week:['日','一','二','三','四','五','六']
			}
		},
		mounted(){
			this.list = calendar();
			this.curIdx = getday();
			log(getday())
		},
		methods:{
			selects(items){
				this.curClick = items;
				let _this = this;
				let params = {days:items};
				Indicator.open('加载中...');
				axios.post('/api/sign/selects',params).then(function(response){
					_this.toworkRes = response.data.toworkRes;
					_this.offworkRes = response.data.offworkRes;
					setTimeout(function(){
						Indicator.close();
					},2000)
					// log(response.data);
				})
			}
		}
	}

	function getday(){
		var curDate=new Date();
		// var days = curDate.getDay();
		var days = curDate.getDate();
		// console.log(days);
		return days;
	}

	function calendar(){

		var curDate=new Date();
		var year=curDate.getFullYear();
		var month=curDate.getMonth();
		var newDate=new Date(year,month,1);
		// var firstday=newDate.getDay();
		var firstday=newDate.getDay();
		var monthArr=new Array(31,28+is_leap(year),31,30,31,30,31,31,30,31,30,31);
		var loop=Math.ceil((monthArr[month] + firstday)/7);

		let arr = [];
		let idx= null;
		let obj = null;

		for(let i=0 ; i<loop; i++){
			arr.push([]);
			for(let k=0; k<7; k++){
				idx=i*7+k;
				obj=idx-firstday+1;
				(obj<=0 || obj> monthArr[month] ) ? arr[i].push(null) : arr[i].push(idx-firstday+1);
			}

		}
		return arr;
	}

	function is_leap(year) {
		let res;
		return (year%100==0?res=(year%400==0?1:0):res=(year%4==0?1:0));
	}
</script>

<style scoped>
	.active{
		color:#fff;
		background: red;
	}
	.calendar span{
		display: block;
		padding: 10px 0;
		border:1px solid #fff;
	}
	.bor{
		border:1px solid #ea2110!important;
	}
	.record tr:first-child td { padding:10px 0; background:#eeeeee;  }
	.record tr td {border:1px solid #eeeeee; }
	.li_record div.p10{ padding:1px 0 0px 0; border-top:1px solid #eeeeee; }
	.li_record h2 { padding:10px; background:#eee; margin-bottom:10px;  }
	.li_record ul { padding:0 10px; }
	.li_record ul li { padding:5px 0; color:#888888;}
</style>