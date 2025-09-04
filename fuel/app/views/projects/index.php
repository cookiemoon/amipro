<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo isset($title) ? Security::htmlentities($title) : 'プロジェクト - あみぷろ'; ?></title>
<?php echo Asset::css('projects.css'); ?>
</head>
<body data-base-url="<?php echo \Uri::base(); ?>" class="projects-page">

<div class="container">

    <!-- Header -->
    <div class="header">
        <div class="header-content">
            <h1>あみぷろ</h1>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="navigation-tabs">
        <a href="<?php echo Uri::create('projects'); ?>"
        class="tab project-tab active">
            <div class="tab-background"></div>
            <div class="tab-content"><p>プロジェクト</p></div>
        </a>

        <a href="<?php echo Uri::create('projects/yarn'); ?>"
           class="tab yarn-tab">
            <div class="tab-background"></div>
            <div class="tab-content"><p>毛糸</p></div>
        </a>
    </div>

    <!-- Main Content -->
    <div class="main-content-area">

        <!-- Controls: Search + Filter -->
        <div class="sticky-wrapper">
            <div class="controls-section">
                <div class="search-container">
                    <input type="text" class="search-input" placeholder="探索..."
                        data-bind="value: currentPageViewModel().searchQuery, valueUpdate: 'input'">
                </div>
                <div class="filter-container">
                    <button class="filter-toggle"
                            data-bind="click: currentPageViewModel().toggleFilterPanel">
                        フィルター
                    </button>
                </div>
            </div>

            <!-- Filter Panel -->
            <div class="filter-panel" data-bind="visible: currentPageViewModel().filterPanelVisible">
                <h3>プロジェクトタイプ</h3>
                <div class="filter-group" data-bind="foreach: currentPageViewModel().availableTypes">
                    <label>
                        <input type="radio" data-bind="checked: $parent.currentPageViewModel().selectedTypes, value: name">
                        <span data-bind="text: name"></span>
                    </label>
                </div>

                <h3>編み技法</h3>
                <div class="filter-group" data-bind="foreach: currentPageViewModel().availableTechniques">
                    <label>
                        <input type="checkbox" data-bind="checked: $parent.currentPageViewModel().selectedTechniques, value: name">
                        <span data-bind="text: name"></span>
                    </label>
                </div>
            </div>
        </div>

        <!-- Projects List -->
        <div class="projects-container" data-bind="foreach: currentPageViewModel().filteredProjects">
            <div class="project-card">
                <div class="project-image">
                    <img data-bind="attr: { src: screenshot_url, alt: name }, visible: screenshot_url">
                    <div class="image-placeholder" data-bind="visible: !screenshot_url"></div>
                </div>

                <div class="project-details">
                    <div class="project-tags">
                        <span class="tag type-tag" data-bind="text: object_type"></span>
                        <!-- ko foreach: technique_names -->
                            <span class="tag tech-tag" data-bind="text: $data"></span>
                        <!-- /ko -->
                    </div>

                    <h3 class="project-title" data-bind="text: name"></h3>
                    <p class="project-status" data-bind="text: status_text"></p>

                    <!-- Progress bar -->
                    <!-- ko if: status == 1 || status == 2 -->
                    <div class="progress-bar-container">
                        <div class="progress-bar" data-bind="style: { width: progress + '%' }"></div>
                    </div>
                    <!-- /ko -->
                </div>

                <a data-bind="attr: { href: detail_url }" class="detail-link">詳細</a>
            </div>
        </div>

        <!-- Empty state -->
        <div class="empty-state" data-bind="visible: currentPageViewModel().filteredProjects().length === 0">
            <p>プロジェクトがありません。</p>
            <a href="<?php echo Uri::create('projects/create'); ?>" class="create-project-link">新しいプロジェクトを作成</a>
        </div>

    </div>

    <!-- Floating Action Button -->
    <div class="create-project-fab">
        <a href="#" class="fab-button" title="新しいプロジェクト" data-bind="click: function(){ currentPage('create') }">+</a>
    </div>

</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/knockout/3.5.1/knockout-latest.js"></script>
<!-- Pass PHP → KO -->
<script>
window.initialData = {
    filters: <?php echo json_encode($available_filters ?? [], JSON_UNESCAPED_UNICODE); ?>,
    selected: <?php echo json_encode($selected_filters ?? [], JSON_UNESCAPED_UNICODE); ?>,
    searchQuery: "<?php echo Security::htmlentities($search_query ?? ''); ?>"
};
</script>

<script src="<?php echo \Uri::base(); ?>assets/js/projects.js"></script>

</body>
</html>