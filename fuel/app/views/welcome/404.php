<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>あみぷろ</title>
	<?php echo Asset::css('not-found.css'); ?>
</head>
<body>
	<header>
		<div class="header">
			<h1>あみぷろ</h1>
		</div>
	</header>
	<div class="container">
		<main class="main-content-area">
			<h2 class="error-title">ページが見つかりません</h2>
			<p>お探しのページは存在しないか、移動した可能性があります。</p>
			<a href="<?php echo \Uri::create(''); ?>" class="return-link">ホームへ戻る</a>
		</main>
	</div>
</body>
</html>
