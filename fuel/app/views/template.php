<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($title) ? $title : 'あみぷろ'; ?></title>
    <meta name="csrf-token" content="<?php echo \Security::fetch_token(); ?>">
    
    <?php
    if (isset($css)):
        echo \Asset::css($css);
    endif;
    ?>
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/knockout/3.5.1/knockout-latest.js"></script>
</head>
<body data-base-url="<?php echo \Uri::base(); ?>">
    
    <div class="container">
        <div class="header">
            <h1>あみぷろ</h1>
        </div>
        
        <?php echo isset($content) ? $content : ''; ?>
    </div>

    <?php
    if (isset($js)):
        echo \Asset::js($js);
    endif;
    ?>
</body>
</html>