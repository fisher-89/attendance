<template>
	<div :style="{padding:'5px',color:color || defaultColor}">
		<div :style="{width:'100%',paddingTop:'100%',position:'relative',backgroundColor:'#fff',borderRadius:'5%'}">
			<Row :style="{'text-align':'center',position:'absolute',width:'100%',bottom:'40%'}">
				<Icon :type="icon" size="30"/>
			</Row>
			<Row style="text-align:center;position:absolute;width:100%;top:60%;">
				{{text}}
			</Row>
		</div>
	</div>
</template>
<script>
    export default {
        data() {
            return {
                defaultColor: '#777'
            };
        },
        props: ['text', 'icon', 'color'],
    }
</script>
