<?php
/**
 * FuelPHP Main Template
 * Place this file at: fuel/app/views/template.php
 */
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($title) ? $title : 'あみぷろ'; ?></title>
    
    <?php if (isset($css)): ?>
        <?php foreach ((array)$css as $css_file): ?>
            <link rel="stylesheet" href="<?php echo Uri::create('assets/css/' . $css_file); ?>">
        <?php endforeach; ?>
    <?php endif; ?>
    
    <!-- Additional head content -->
    <?php if (isset($head)): ?>
        <?php echo $head; ?>
    <?php endif; ?>
</head>
<body>


    <!-- Main Content -->
    <?php echo isset($content) ? $content : ''; ?>
    
    <!-- JavaScript Files -->
    <?php if (isset($js)): ?>
        <?php foreach ((array)$js as $js_file): ?>
            <script src="<?php echo Uri::create('assets/js/' . $js_file); ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
    
    <!-- Additional scripts -->
    <?php if (isset($scripts)): ?>
        <?php echo $scripts; ?>
    <?php endif; ?>
</body>
</html>