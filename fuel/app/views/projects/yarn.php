<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($title) ? Security::htmlentities($title) : '毛糸管理'; ?></title>
    <?php echo Asset::css('projects.css'); ?>
    <meta name="csrf-token-key" content="<?php echo \Config::get('security.csrf_token_key'); ?>">
    <meta name="csrf-token" content="<?php echo \Security::fetch_token(); ?>">
</head>
<body>
    <body data-base-url="<?php echo \Uri::base(); ?>" data-page-type="yarn">
    <div class="container">
        <div class="header"><h1>あみぷろ</h1></div>

        <div class="navigation-tabs">
            <a href="<?php echo Uri::create('projects/yarn'); ?>" class="tab yarn-tab <?php echo ($current_tab === 'yarn') ? 'active' : ''; ?>">
                <div class="tab-background"></div>
                <div class="tab-content"><p>毛糸</p></div>
            </a>
            <a href="<?php echo Uri::create('projects'); ?>" class="tab project-tab <?php echo ($current_tab === 'projects') ? 'active' : ''; ?>">
                <div class="tab-background"></div>
                <div class="tab-content"><p>プロジェクト</p></div>
            </a>
        </div>

        <div class="main-content-area">
            <div class="controls-section">
                <div class="search-container">
                    <input type="text" id="searchInput" placeholder="探索..." value="<?php echo Security::htmlentities($search_query); ?>" class="search-input">
                </div>
                <div class="filter-container">
                    <button id="filterToggle" class="filter-toggle"><span>フィルター</span></button>
                    <div class="active-filters"></div>
                </div>
            </div>

            <div id="filterPanel" class="filter-panel" style="display: none;">
                <div class="filter-options">
                    <h3>繊維タイプ</h3>
                    <div class="filter-group">
                        <?php foreach ($filters['fiber_types'] as $key => $name): ?>
                            <label class="filter-option">
                                <input type="checkbox" name="filters[]" value="<?php echo $key; ?>" <?php echo in_array($key, $selected_filters['fiber_types']) ? 'checked' : ''; ?>>
                                <span><?php echo $name; ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                    <h3>毛糸の太さ</h3>
                    <div class="filter-group">
                        <?php foreach ($filters['weights'] as $key => $name): ?>
                            <label class="filter-option">
                                <input type="checkbox" name="filters[]" value="<?php echo $key; ?>" <?php echo in_array($key, $selected_filters['weights']) ? 'checked' : ''; ?>>
                                <span><?php echo $name; ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <div class="yarn-list-container">
                <?php if (!empty($yarn_list)): ?>
                    <?php foreach ($yarn_list as $yarn): ?>
                        <div class="yarn-card">
                            <h3><?php echo Security::htmlentities($yarn['brand'] . ' ' . $yarn['name']); ?></h3>
                            <p>色：<?php echo Security::htmlentities($yarn['color']); ?></p>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <p>毛糸がありません。</p>
                        <a href="<?php echo Uri::create('projects/add_yarn'); ?>" class="create-project-link">
                            新しい毛糸を追加
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="create-project-fab">
             <a href="<?php echo Uri::create('projects/add_yarn'); ?>" class="fab-button" title="新しい毛糸">+</a>
        </div>
    </div>
    
    </body>
</html>