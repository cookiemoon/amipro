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
    class="tab project-tab">
      <div class="tab-background"></div>
      <div class="tab-content"><p>プロジェクト</p></div>
    </a>

    <a href="<?php echo Uri::create('projects/yarn'); ?>"
       class="tab yarn-tab active">
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
        <h3>太さ</h3>
        <div class="filter-group" data-bind="foreach: currentPageViewModel().availableWeights">
          <label>
            <input type="radio" data-bind="checked: $parent.currentPageViewModel().selectedWeights, value: name">
            <span data-bind="text: name"></span>
          </label>
        </div>

        <h3>繊維</h3>
        <div class="filter-group" data-bind="foreach: currentPageViewModel().availableFibers">
          <label>
            <input type="checkbox" data-bind="checked: $parent.currentPageViewModel().selectedFibers, value: name">
            <span data-bind="text: name"></span>
          </label>
        </div>
      </div>
    </div>

    <!-- Yarns List -->
    <div class="yarn-container" data-bind="foreach: currentPageViewModel().filteredYarns">
      <div class="project-card">
        <div class="project-details">
          <!-- ko if: brand -->
            <h3 class="project-title" data-bind="text: brand + ' ' + name"></h3>
          <!-- /ko -->
          <!-- ko ifnot: brand -->
            <h3 class="project-title" data-bind="text: name"></h3>
          <!-- /ko -->
          <div class="project-tags">
            <span class="tag type-tag" data-bind="text: weight"></span>
            <!-- ko foreach: fiber_types -->
              <span class="tag tech-tag" data-bind="text: $data"></span>
            <!-- /ko -->
          </div>
          
          <!-- ko if: color -->
          <p class="project-status">
            <strong>色:</strong> <span data-bind="text: color"></span>
          </p>
          <!-- /ko -->

          <!-- ko if: fiber_desc -->
          <p class="project-status">
            <strong>繊維:</strong> <span data-bind="text: fiber_desc"></span>
          </p>
          <!-- /ko -->

          <p class="project-status">
            <strong>プロジェクト:</strong> 
            <!-- ko if: project_id -->
            <a data-bind="text: project_name, attr: { href: project_id ? ('<?php echo Uri::base(); ?>projects/detail/' + project_id) : '#' }"></a>
            <!-- /ko -->
            <!-- ko ifnot: project_id -->
            <span data-bind="text: project_name"></span>
            <!-- /ko -->
          </p>
        </div>

        <div class="project-actions">
          <a href="#" class="edit-link" data-bind="click: $parent.currentPageViewModel().editYarn">編集</a>
          <a href="#" class="delete-link" data-bind="click: $parent.currentPageViewModel().deleteYarn">削除</a>
        </div> 
      </div>
    </div>

    <!-- Empty state -->
    <div class="empty-state" data-bind="visible: currentPageViewModel().filteredYarns().length === 0">
      <p>毛糸がありません。</p>
      <a href="#" class="create-project-link" data-bind="click: currentPageViewModel().showModal">新しい毛糸を追加</a>
    </div>

  </div>

  <!-- Floating Action Button -->
  <div class="create-project-fab">
    <a href="#" class="fab-button" title="新しい毛糸" data-bind="click: currentPageViewModel().showModal">+</a>
  </div> 

  <div class="logout">
    <a href="#" class="logout-button" data-bind="click: currentPageViewModel().logout">ログアウト</a>
  </div>

  <div class="modal-overlay" data-bind="visible: currentPageViewModel() && currentPageViewModel().showCreateModal">
    <div class="modal-window" data-bind="with: currentPageViewModel()">
      <h2 data-bind="text: currentEditYarn() ? '毛糸を編集' : '新しい毛糸を追加'"></h2>

      <form>
        <label>
          毛糸名: <span class="required">*</span>
          <input type="text" data-bind="value: newYarn.name" maxlength="32">
        </label>

        <label>
          ブランド:
          <input type="text" data-bind="value: newYarn.brand" maxlength="32">
        </label>

        <!-- Project Selection -->
        <label>
          プロジェクト:
          <div class="searchable-dropdown">
            <div class="custom-technique-input">
              <input type="text" 
                placeholder="プロジェクトを検索..." 
                data-bind="value: projectSearch, valueUpdate: 'afterkeydown'">

              <button type="button" class="clear-btn" 
                data-bind="click: function() { newYarn.project(null); projectSearch(''); }">✕</button>
            </div>

            
            <ul data-bind="foreach: filteredProjects, visible: dropdownOpen">
              <li data-bind="text: name, click: $parent.selectProject"></li>
            </ul>

            <div class="no-results" data-bind="visible: projectSearch() && filteredProjects().length === 0">
              プロジェクトが見つかりません。
            </div>
          </div>
        </label>

        <label>
          色:
          <input type="text" data-bind="value: newYarn.color" maxlength="255">
        </label>

        <!-- Yarn Weight Selection -->
        <label>
          太さ:
          <select data-bind="options: weightSelection, 
              optionsValue: 'name',
              optionsText: 'name', 
              value: newYarn.weight, 
              optionsCaption: '選択してください'"></select>
        </label>

        <!-- Yarn Fiber Selection -->
        <label>
          繊維:
          <div class="checkbox-group" data-bind="foreach: availableFibers">
            <label>
              <input type="checkbox" data-bind="checked: $parent.newYarn.fibers, value: name">
              <span data-bind="text: name"></span>
            </label>
          </div>
          <input type="text" data-bind="value: newYarn.fiberDesc" placeholder="例: コットン30%、 ウール70%" maxlength="255">
        </label>

        <div class="modal-actions">
        <button type="button" data-bind="click: submitNewYarn, enable: isFormValid">
          <span data-bind="text: currentEditYarn() ? '保存' : '作成'"></span>
        </button>
        <button type="button" data-bind="click: cancelCreate">キャンセル</button>
        </div>
      </form>
    </div>
  </div>

</div>

<script>
window.initialData = {
  filters: <?php echo json_encode($available_filters ?? [], JSON_UNESCAPED_UNICODE); ?>,
  availableProjects: <?php echo json_encode($available_projects ?? [], JSON_UNESCAPED_UNICODE); ?>,
  selected: <?php echo json_encode($selected_filters ?? [], JSON_UNESCAPED_UNICODE); ?>,
  searchQuery: "<?php echo Security::htmlentities($search_query ?? ''); ?>"
};
</script>