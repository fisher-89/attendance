<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8"/>
	<meta http-equiv="X-UA-Compatible" content="IE=edge"/>
	<meta name="apple-mobile-web-app-capable" content="yes">
	<meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">
	<meta name="renderer" content="webkit"/>
	<meta http-equiv="Cache-Control" content="no-siteapp"/>
	<title>
		考勤系统
	</title>
</head>
<body style="background-color:#eaeaea;">
	<div id="app">
	</div>
	<script>
		window.Laravel = {
			csrfToken : "{{csrf_token()}}"
		}
	</script>
	<script type="text/javascript" src='http://webapi.amap.com/maps?v=1.3&key=9a54ee2044c8fdd03b3d953d4ace2b4d&plugin=AMap.Geolocation'></script>
	<script src="{{source('/js/front.js')}}"></script>
</body>
</html>
