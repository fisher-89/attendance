<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Document1</title>
</head>
<body>

	<div id="app">
		<form action="/try" method="post">
		<input type="text" value="ddasdasd" name="uname">
		<input type="text" value="Demo" name="king">
		{{csrf_field()}}
		<p>
			<button>提交</button>
		</p>
	</form>
	</div>
	
	<?php echo session('ser'); ?>
<script>
	window.Laravel = {
			csrfToken : "{{csrf_token()}}"
		}

</script>
{{-- <script src=""></script> --}}
<script src="/js/front.js"></script>
</body>
</html>