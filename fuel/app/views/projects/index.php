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
                    <h3 class="project-title" data-bind="text: name"></h3>
                    <div class="project-tags">
                        <span class="tag type-tag" data-bind="text: object_type"></span>
                        <!-- ko foreach: technique_names -->
                            <span class="tag tech-tag" data-bind="text: $data"></span>
                        <!-- /ko -->
                    </div>

                    <!-- ko if: yarn_name -->
                    <p class="project-status">
                        <strong>毛糸:</strong> <span data-bind="text: yarn_name"></span>
                    </p>
                    <!-- /ko -->

                    <!-- Progress bar -->
                    <p class="project-status" data-bind="text: status_text">
                        
                    </p>
                    <!-- ko if: status == 1 || status == 2 || status == 3 -->
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
            <a href="#" class="create-project-link" data-bind="click: currentPageViewModel().showModal">新しいプロジェクトを作成</a>
        </div>

    </div>

    <!-- Floating Action Button -->
    <div class="create-project-fab">
        <a href="#" class="fab-button" title="新しいプロジェクト" data-bind="click: currentPageViewModel().showModal">+</a>
    </div> 

    <div class="logout">
        <a href="#" class="logout-button" data-bind="click: currentPageViewModel().logout">ログアウト</a>
    </div>

    <div class="modal-overlay" data-bind="visible: currentPageViewModel() && currentPageViewModel().showCreateModal">
        <div class="modal-window" data-bind="with: currentPageViewModel()">
            <h2>新しいプロジェクトを作成</h2>

            <form>
                <label>
                    プロジェクト名: <span class="required">*</span>
                    <input type="text" data-bind="value: newProject.name">
                </label>

                <!-- Object Type -->
                <label>
                    プロジェクトタイプ: <span class="required">*</span>
                    <input type="text" data-bind="value: newProject.objectType" placeholder="例: セーター">
                </label>

                <!-- Techniques -->
                <label>
                    技法 (選択可能):
                    <div class="techniques-container" data-bind="foreach: suggestedTechniques">
                        <button type="button"
                                data-bind="text: $data, click: $parent.toggleTechnique,
                                        css: { 'selected-tech': $parent.newProject.techniques.indexOf($data) >= 0 }">
                        </button>
                    </div>

                    <!-- Free input for custom technique -->
                    <div class="custom-technique-input">
                        <input type="text" placeholder="カスタム技法を追加"
                            data-bind="value: newTechniqueInput, valueUpdate: 'afterkeydown', event: { keyup: function(data, event) { if(event.key === 'Enter') { addCustomTechnique(); } } }">
                        <button type="button" data-bind="click: addCustomTechnique">追加</button>
                    </div>


                    <!-- Live preview of selected techniques -->
                    <div class="tech-preview" data-bind="foreach: newProject.techniques">
                        <span class="tech-tag">
                            <span data-bind="text: $data"></span>
                            <button type="button" data-bind="click: $parent.removeTechnique">×</button>
                        </span>
                    </div>
                </label>

                <div class="completion-date-container">
                    <label>
                        開始日:
                        <input type="date" data-bind="value: newProject.startDate">
                    </label>
                </div>

                <label>
                    状態:
                    <select data-bind="value: newProject.status">
                        <!-- ko foreach: statusOptions -->
                        <option data-bind="value: value, text: label"></option>
                        <!-- /ko -->
                    </select>
                </label>

                <!-- Progress slider for 進行中 or 中断中 -->
                <div class="progress-container" data-bind="visible: showProgress">
                    <label>
                        進捗 (%):
                        <input type="range" min="0" max="100" data-bind="value: newProject.progress">
                        <span data-bind="text: newProject.progress"></span>%
                    </label>
                </div>

                <!-- Completion date picker for 完了 -->
                <div class="completion-date-container" data-bind="visible: showCompletionDate">
                    <label>
                        完了日:
                        <input type="date" data-bind="value: newProject.completionDate">
                    </label>
                </div>

                <!-- Yarn Selection -->
                <label>
                    毛糸:
                    <div class="searchable-dropdown">
                        <input type="text"
                            placeholder="毛糸を検索..."
                            data-bind="value: yarnSearch, valueUpdate: 'afterkeydown'">

                        <ul data-bind="foreach: filteredYarns, visible: dropdownOpen">
                            <!-- ko if: color -->
                            <li data-bind="text: name + ' (' + color + ')', click: $parent.selectYarn"></li>
                            <!-- /ko -->
                            <!-- ko ifnot: color -->
                            <li data-bind="text: name, click: $parent.selectYarn"></li>
                            <!-- /ko -->
                        </ul>

                        <div class="no-results" data-bind="visible: yarnSearch() && filteredYarns().length === 0">
                            使える毛糸が見つかりません。
                        </div>
                    </div>
                </label>

                <!-- Selected yarn tags -->
                <div class="selected-yarns" data-bind="foreach: selectedYarns">
                    <span class="tag">
                        <!-- ko if: color -->
                        <span data-bind="text: name + ' (' + color + ')'"></span>
                        <!-- /ko -->
                        <!-- ko ifnot: color -->
                        <span data-bind="text: name"></span>
                        <!-- /ko -->
                        <button type="button" data-bind="click: $parent.removeYarn">×</button>
                    </span>
                </div>

                <!-- Memo -->
                <label>メモ:</label>
                <textarea data-bind="value: newProject.memo" 
                        placeholder="ここに自由にメモを入力できます..."
                        rows="6"></textarea>

                <!-- Screenshot -->
                <label>
                    スクリーンショットURL:
                    <input type="text" data-bind="value: newProject.screenshotUrl" placeholder="https://example.com/image.jpg">
                </label>

                <!-- Live preview -->
                <div class="screenshot-preview" data-bind="visible: screenshotPreview">
                    <p>プレビュー:</p>
                    <img data-bind="attr: { src: screenshotPreview }" alt="スクリーンショットプレビュー">
                </div>

                <!-- Colorwork -->
                <label>
                    カラーチャートURL:
                    <input type="text" data-bind="value: newProject.colorworkUrl" placeholder="https://example.com/image.jpg">
                </label>

                <!-- Live preview -->
                <div class="screenshot-preview" data-bind="visible: colorworkScreenshotPreview">
                    <p>プレビュー:</p>
                    <img data-bind="attr: { src: colorworkScreenshotPreview }" alt="スクリーンショットプレビュー">
                </div>

                <div class="modal-actions">
                <button type="button" data-bind="click: submitNewProject, enable: isFormValid">作成</button>
                <button type="button" data-bind="click: cancelCreate">キャンセル</button>
                </div>
            </form>
        </div>
    </div>

</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/knockout/3.5.1/knockout-latest.js"></script>
<!-- Pass PHP → KO -->
<script>
window.initialData = {
    filters: <?php echo json_encode($available_filters ?? [], JSON_UNESCAPED_UNICODE); ?>,
    availableYarns: <?php echo json_encode($available_yarn ?? [], JSON_UNESCAPED_UNICODE); ?>,
    selected: <?php echo json_encode($selected_filters ?? [], JSON_UNESCAPED_UNICODE); ?>,
    searchQuery: "<?php echo Security::htmlentities($search_query ?? ''); ?>"
};
</script>

<script src="<?php echo \Uri::base(); ?>assets/js/projects.js"></script>

</body>
</html>