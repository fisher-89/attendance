<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="renderer" content="webkit">
    <meta http-equiv="Cache-Control" content="no-siteapp" />
	<title>考勤系统</title>
	<link rel="stylesheet" href="/css/reset.css">
	<link rel="stylesheet" href="/css/mintui.css">
</head>
<body>
	<div id="app"></div>
	<script>
		window.Laravel = {
			csrfToken : "{{csrf_token()}}"
		}
	</script>
	
	<script src="/js/vue.js"></script>
	<script src="/js/vue-router.js"></script>
	<script src="/js/axios.js"></script>
	<script src="/js/front.js"></script>

</body>
</html>