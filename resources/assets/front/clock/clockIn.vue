<template>
	<div>
		<Alert banner type="error" show-icon v-if="!inShop">
			没有所属店铺
			<template slot="desc">请联系人事添加店铺归属</template>
		</Alert>
		<Alert banner type="error" show-icon v-else-if="!currentUser.shop.lng">
			店铺尚未定位
			<template slot="desc">请联系店长登录系统完成定位</template>
		</Alert>
		<Alert banner type="error" show-icon v-else-if="!aLocation" v-show="locationErr != false">
			定位失败
			<template slot="desc">请检查是否开启定位或使用店长代签打卡<br>
				<span v-if="locationErr != false">错误信息：{{locationErr}}</span>
			</template>
		</Alert>
		<Alert banner type="error" show-icon v-else-if="!clockAvailable && date == today">
			无法打卡，未知原因
			<template slot="desc">请联系IT部<br>
				<span v-if="locationErr != false">错误信息：{{locationErr}}</span>
			</template>
		</Alert>
		<div v-if="locating == true && date == today" style="margin:77px auto;text-align:center;">
			<div style="width:40px;margin:5px auto;">
				<mt-spinner type="fading-circle" :size="40"></mt-spinner>
			</div>
			<p style="color:lightgrey">定位中...	</p>
		</div>
		<div v-if="clockAvailable && !success">
			<Button :type="inTime?'success':'error'" @click="uploadClock"
			        style="width:180px;height:180px;margin:20px auto;display:block;
			        -webkit-border-radius: 50%;-moz-border-radius: 50%;border-radius: 50%;">
				<h2>上班急速打卡</h2>
				<p>{{curTime}}</p>
			</Button>
		</div>
		<div v-if="success" style="text-align:center;margin:30px 0;">
			<h2 style="color:#333">上班打卡成功</h2>
		</div>
		<div style=" text-align:center;margin:5px 0;" @click="$emit('update:close', true)">
			<a>返回正常打卡</a>
		</div>
	</div>
</template>


<script>
    import {Spinner} from 'mint-ui';

    var components = {};
    components[Spinner.name] = Spinner;

    export default {
        data() {
            return {
                locating: false,        //定位中提示
                aLocation: false,	    //定位信息
                locationErr: false,	    //定位失败信息
                curTime: '12:00:00',    //当前时间
                success: false,
            };
        },
        props: ['currentUser', 'close'],
        components: components,
        watch: {
            refresh(newValue) {
                if (newValue == true) {
                    this.getRecord();
                }
            }
        },
        computed: {
            inShop() { //员工是否有所属店铺;
                if (this.currentUser) {
                    return this.currentUser.shop_sn.length > 0;
                } else {
                    return false;
                }
            },
            clockAvailable() { //是否可以打卡
                let avaliableError = true;
                if (this.locationErr &&
                    this.locationErr.match('error message = 取消') == null &&
                    this.locationErr.match('error message = 定位错误') == null) {
                    avaliableError = false;
                }
                return this.aLocation !== false && avaliableError && this.inShop;
            },
            inTime() {
                return (this.hasClockIn && this.curTime > this.currentUser.working_end_at) || (!this.hasClockIn && this.curTime < this.currentUser.working_start_at);
            }
        },
        beforeMount() {
            this.getCurTime();
            setInterval(this.getCurTime, 1000);
        },
        mounted() {
            this.dingtalkInit();
        },
        methods: {
            getLocation() {
                dd.device.geolocation.get({
                    targetAccuracy: 200,
                    coordinate: 1,
                    withReGeocode: true,
                    useCache: false,
                    onSuccess: (result) => {
                        this.locating = false;
                        try {
                            if (result.location) {
                                result = result.location;
                            }
                            this.aLocation = {
                                position: {
                                    lng: result.longitude,
                                    lat: result.latitude
                                },
                                formattedAddress: result.address ? result.address : '获取地址信息失败，可正常打卡',
                                accuracy: result.accuracy
                            };
                            this.locationErr = false;
                        } catch (e) {
                            this.locationErr = e.message;
                        }

                    },
                    onFail: (err) => {
                        this.locationErr = err.errorMessage;
                    }
                });
            },
            getCurTime() {
                let date = new Date();
                let hour = (Array(2).join(0) + date.getHours()).slice(-2);
                let minute = (Array(2).join(0) + date.getMinutes()).slice(-2);
                let second = (Array(2).join(0) + date.getSeconds()).slice(-2);
                this.curTime = hour + ':' + minute + ':' + second;
            },
            uploadClock() {
                this.takePhoto((picPath) => {
                    Indicator.open('处理中...');
                    let url = '/clock/flash';
                    let params = {
                        lng: this.aLocation.position.lng,
                        lat: this.aLocation.position.lat,
                        address: this.aLocation.formattedAddress,
                        accuracy: this.aLocation.accuracy,
                        photo: picPath[0]
                    };
                    axios.post(url, params).then((response) => {
                        if (typeof response.data == 'string') {
                            document.write(response.data);
                        }
                        if (response.data.status) {
                            this.$Message.success(response.data.msg);
                            this.success = true;
                        } else {
                            this.$Message.error(response.data.msg);
                        }
                        Indicator.close();
                    }).catch((err) => {
                        document.write(err);
                    });
                });
            },
            dingtalkInit() {
                let url = '/js_config';
                axios.post(url, {'current_url': location.href}).then((response) => {
                    let jsConfig = response.data;
                    jsConfig['jsApiList'] = ['biz.util.uploadImageFromCamera', 'device.geolocation.get'];
                    dd.config(jsConfig);
                    dd.ready(() => {
                        this.locating = true;
                        this.getLocation();
                        setInterval(this.getLocation, 10000);
                    });
                    dd.error(function (error) {
                        let html = JSON.stringify(error);
                        html += '<h2 onClick="location.reload()" style="text-align:center;margin-top:20px;color:#333;">点此刷新</h2>';
                        document.write(html);
                    });
                });
            },
            takePhoto(callback) {
                dd.ready(() => {
                    dd.biz.util.uploadImageFromCamera({
                        onSuccess: (picPath) => {
                            callback(picPath);
                        }
                    });
                })
            }
        }
    }
</script>
