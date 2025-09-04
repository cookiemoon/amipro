<?php
/**
 * Projects Index View - Home Page
 * Place this file at: fuel/app/views/projects/index.php
 * Main projects listing page with filtering and search
 */
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($title) ? Security::htmlentities($title) : 'プロジェクト - あみぷろ'; ?></title>
    <?php echo Asset::css('projects.css'); ?>
    <meta name="csrf-token-key" content="<?php echo \Config::get('security.csrf_token_key'); ?>">
    <meta name="csrf-token" content="<?php echo \Security::fetch_token(); ?>">
</head>
<body data-base-url="<?php echo \Uri::base(); ?>" data-page-type="projects" class="projects-page" data-create-url="<?php echo \Uri::create('projects/create'); ?>">>
    <div class="container" data-name="ホームページ（プロジェクト）">
        <div class="header" data-name="header">
            <div class="header-content">
                <h1>あみぷろ</h1>
            </div>
        </div>

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
                     <input type="text" 
                           id="searchInput"
                           placeholder="探索..." 
                           value="<?php echo Security::htmlentities($search_query); ?>"
                           class="search-input">
                </div>

                <div class="filter-container">
                    <button id="filterToggle" class="filter-toggle">
                        <span>フィルター</span>
                    </button>
                    
                    <div class="active-filters">
                        <?php // Loop through the selected 'types'
                        if (!empty($selected_filters['types'])):
                            foreach ($selected_filters['types'] as $filter_key):
                                if (isset($available_filters['types'][$filter_key])): ?>
                                    <div class="active-filter-tag" data-filter="type" data-value="<?php echo \Security::htmlentities($filter_key); ?>">
                                        <?php echo \Security::htmlentities($available_filters['types'][$filter_key]); ?> ✕
                                    </div>
                                <?php endif;
                            endforeach;
                        endif; ?>

                        <?php // Loop through the selected 'techniques'
                        if (!empty($selected_filters['techniques'])):
                            foreach ($selected_filters['techniques'] as $filter_key):
                                if (isset($available_filters['techniques'][$filter_key])): ?>
                                    <div class="active-filter-tag" data-filter="technique" data-value="<?php echo \Security::htmlentities($filter_key); ?>">
                                        <?php echo \Security::htmlentities($available_filters['techniques'][$filter_key]); ?> ✕
                                    </div>
                                <?php endif;
                            endforeach;
                        endif; ?>
                    </div>
                </div>
            </div>

            <div id="filterPanel" class="filter-panel" style="display: none;">
                <div class="filter-options">
                    <h3>プロジェクトタイプ</h3>
                    <div class="filter-group">
                        <?php // FIX: Loop through the clean data from the controller
                        foreach ($available_filters['types'] as $key => $name): ?>
                            <label class="filter-option">
                                <input type="checkbox" 
                                    name="types[]" 
                                    value="<?php echo \Security::htmlentities($key); ?>"
                                    <?php echo in_array($key, $selected_filters['types']) ? 'checked' : ''; ?>>
                                <span><?php echo \Security::htmlentities($name); ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>

                    <h3>編み技法</h3>
                    <div class="filter-group">
                        <?php // FIX: Loop through the clean data from the controller
                        foreach ($available_filters['techniques'] as $key => $name): ?>
                            <label class="filter-option">
                                <input type="checkbox" 
                                    name="techniques[]" 
                                    value="<?php echo \Security::htmlentities($key); ?>"
                                    <?php echo in_array($key, $selected_filters['techniques']) ? 'checked' : ''; ?>>
                                <span><?php echo \Security::htmlentities($name); ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <div class="projects-container" id="projectsContainer">
                <?php if (!empty($projects)): ?>
                    <?php foreach ($projects as $project): ?>
                        <div class="project-card">
                            <div class="project-image">
                                <?php if (!empty($project['screenshot_url'])): ?>
                                    <img src="<?php echo Security::htmlentities($project['screenshot_url']); ?>" alt="<?php echo Security::htmlentities($project['name']); ?>">
                                <?php else: ?>
                                    <div class="image-placeholder"></div>
                                <?php endif; ?>
                            </div>
                            <div class="project-details">
                                <div class="project-tags">
                                    <span class="tag type-tag"><?php echo Security::htmlentities($project['object_type']); ?></span>
                                    <?php if (!empty($project['technique_names'])): ?>
                                        <?php foreach ($project['technique_names'] as $technique): ?>
                                            <span class="tag tech-tag"><?php echo Security::htmlentities($technique); ?></span>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>

                                <h3 class="project-title"><?php echo Security::htmlentities($project['name']); ?></h3>
                                <p class="project-status"><?php echo Security::htmlentities($project['status_text']); ?></p>

                                <?php if ($project['status'] == 1 || $project['status'] == 2): ?>
                                    <div class="progress-bar-container">
                                        <div class="progress-bar" style="width: <?php echo (int)$project['progress']; ?>%;"></div>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <a href="<?php echo Uri::create('projects/detail/' . $project['id']); ?>" class="detail-link">詳細</a>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <p>プロジェクトがありません。</p>
                        <a href="<?php echo Uri::create('projects/create'); ?>" class="create-project-link">新しいプロジェクトを作成</a>
                    </div>
                <?php endif; ?>
            </div>
        </div> <div class="create-project-fab">
            <a href="<?php echo Uri::create('projects/create'); ?>" class="fab-button" title="新しいプロジェクト">+</a>
        </div>
    </div>
    </body>
</html>