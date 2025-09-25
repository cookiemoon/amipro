<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>amipro</title>
	<?php echo Asset::css('not-found.css'); ?>
</head>
<body>
	<header>
		<div class="header">
			<h1>amipro</h1>
		</div>
	</header>
	<div class="container">
		<main class="main-content-area">
			<h2 class="error-title">Page not found</h2>
			<p>The page you are looking for could not be found.</p>
			<a href="<?php echo \Uri::create(''); ?>" class="return-link">Homepage</a>
		</main>
	</div>
</body>
</html>
