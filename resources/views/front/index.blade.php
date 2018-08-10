<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="renderer" content="webkit" />
    <meta http-equiv="Cache-Control" content="no-siteapp" />
    <meta name="x5-fullscreen" content="true">
    <meta name="full-screen" content="yes">
    <title>
        考勤系统
    </title>
</head>
<body style="background-color:#eaeaea;">
<div id="app">
</div>
@inject('currentUser','CurrentUser')
<script src="{{source('/js/dingtalk.js')}}"></script>
<script>
  sessionStorage.setItem('staff', '{!! json_encode($currentUser->getInfo()) !!}');
  dd.ready(function () {
    dd.ui.webViewBounce.disable();
  });
  //@TODO 使用一段时间后恢复店助全开
  window.assistantActive = ['go0001', 'lsw5125','lsw5219'];
</script>
<script src="{{source('/js/front.js')}}"></script>
</body>
</html>
