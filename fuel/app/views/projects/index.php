<div class="container">

  <!-- Header -->
  <div class="header">
    <div class="header-content">
      <h1>amipro</h1>
    </div>
  </div>

  <!-- Navigation Tabs -->
  <div class="navigation-tabs">
    <a href="<?php echo Uri::create('projects'); ?>"
    class="tab project-tab active">
      <div class="tab-background"></div>
      <div class="tab-content"><p>Projects</p></div>
    </a>

    <a href="<?php echo Uri::create('projects/yarn'); ?>"
       class="tab yarn-tab">
      <div class="tab-background"></div>
      <div class="tab-content"><p>Yarns</p></div>
    </a>
  </div>

  <!-- Main Content -->
  <div class="main-content-area">

    <!-- Controls: Search + Filter -->
    <div class="sticky-wrapper">
      <div class="controls-section">
        <div class="search-container">
          <input type="text" class="search-input" placeholder="Search..."
            data-bind="value: currentPageViewModel().searchQuery, valueUpdate: 'input'">
        </div>
        <div class="filter-container">
          <button class="filter-toggle"
              data-bind="click: currentPageViewModel().toggleFilterPanel">
            Filter
          </button>
        </div>
      </div>

      <!-- Filter Panel -->
      <div class="filter-panel" data-bind="visible: currentPageViewModel().filterPanelVisible">
        <h3>Type</h3>
        <div class="filter-group" data-bind="foreach: currentPageViewModel().availableTypes">
          <label>
            <input type="radio" data-bind="checked: $parent.currentPageViewModel().selectedTypes, value: name">
            <span data-bind="text: name"></span>
          </label>
        </div>

        <h3>Technique</h3>
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
            <strong>Yarn:</strong> <span data-bind="text: yarn_name"></span>
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

        <a data-bind="attr: { href: detail_url }" class="detail-link">Details</a>
      </div>
    </div>

    <!-- Empty state -->
    <div class="empty-state" data-bind="visible: currentPageViewModel().filteredProjects().length === 0">
      <p>No projects found.</p>
      <a href="#" class="create-project-link" data-bind="click: currentPageViewModel().showModal">Create a project</a>
    </div>

  </div>

  <!-- Floating Action Button -->
  <div class="create-project-fab">
    <a href="#" class="fab-button" title="New project" data-bind="click: currentPageViewModel().showModal">+</a>
  </div> 

  <div class="logout">
    <a href="#" class="logout-button" data-bind="click: currentPageViewModel().logout">Log Out</a>
  </div>

  <div class="modal-overlay" data-bind="visible: currentPageViewModel() && currentPageViewModel().showCreateModal">
    <div class="modal-window" data-bind="with: currentPageViewModel()">
      <h2>Create Project</h2>

      <form>
        <label>
          Name: <span class="required">*</span>
          <input type="text" data-bind="value: newProject.name" maxlength="32">
        </label>

        <!-- Object Type -->
        <label>
          Type: <span class="required">*</span>
          <input type="text" data-bind="value: newProject.objectType" placeholder="Example: Sweater, Scarf, Shawl..." maxlength="10">
        </label>

        <!-- Techniques -->
        <label>
          Techniques (Multiple selection):
          <div class="techniques-container" data-bind="click: function() { }">
            <!-- ko foreach: suggestedTechniques -->
            <button type="button"
                data-bind="text: $data, 
                    click: function(data, event) { $parent.toggleTechnique(data, event) },
                    clickBubble: false,
                    css: { 'selected-tech': $parent.newProject.techniques.indexOf($data) >= 0 }">
            </button>
            <!-- /ko -->
          </div>

          <!-- Free input for custom technique -->
          <div class="custom-technique-input">
            <input type="text" placeholder="Add custom technique..."
              data-bind="value: newTechniqueInput, valueUpdate: 'afterkeydown', event: { keyup: function(data, event) { if(event.key === 'Enter') { addCustomTechnique(); } } }"
              maxlenght="255">
            <button type="button" data-bind="click: addCustomTechnique">Add</button>
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
            Start Date:
            <input type="date" data-bind="value: newProject.startDate">
          </label>
        </div>

        <label>
          Status:
          <select data-bind="value: newProject.status">
            <!-- ko foreach: statusOptions -->
            <option data-bind="value: value, text: label"></option>
            <!-- /ko -->
          </select>
        </label>

        <!-- Progress slider for 進行中 or 中断中 -->
        <div class="progress-container" data-bind="visible: showProgress">
          <label>
            Progress (%):
            <input type="range" min="0" max="100" data-bind="value: newProject.progress">
            <span data-bind="text: newProject.progress"></span>%
          </label>
        </div>

        <!-- Completion date picker for 完了 -->
        <div class="completion-date-container" data-bind="visible: showCompletionDate">
          <label>
            Completion Date:
            <input type="date" data-bind="value: newProject.completionDate">
          </label>
        </div>

        <!-- Yarn Selection -->
        <label>
          Yarn:
          <div class="searchable-dropdown">
            <input type="text"
              placeholder="Enter name..."
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
              No yarn found.
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
        <label>Memo:</label>
        <textarea data-bind="value: newProject.memo" 
            placeholder="Please enter details about the project..."
            rows="6"></textarea>

        <!-- Screenshot -->
        <label>
          Screenshot URL:
          <input type="text" data-bind="value: newProject.screenshotUrl" placeholder="https://example.com/image.jpg">
        </label>

        <!-- Live preview -->
        <div class="screenshot-preview" data-bind="visible: screenshotPreview">
          <p>Preview:</p>
          <img data-bind="attr: { src: screenshotPreview }" alt="Image preview">
        </div>

        <!-- Colorwork -->
        <label>
          Colorwork URL:
          <input type="text" data-bind="value: newProject.colorworkUrl" placeholder="https://example.com/image.jpg">
        </label>

        <!-- Live preview -->
        <div class="colorwork-preview" data-bind="visible: colorworkScreenshotPreview">
          <p>Preview:</p>
          <img data-bind="attr: { src: colorworkScreenshotPreview }" alt="Image preview">
        </div>

        <div class="modal-actions">
        <button type="button" data-bind="click: submitNewProject, enable: isFormValid">Save</button>
        <button type="button" data-bind="click: cancelCreate">Cancel</button>
        </div>
      </form>
    </div>
  </div>

</div>

<script>
window.initialData = {
  filters: <?php echo json_encode($available_filters ?? [], JSON_UNESCAPED_UNICODE); ?>,
  availableYarns: <?php echo json_encode($available_yarn ?? [], JSON_UNESCAPED_UNICODE); ?>,
  selected: <?php echo json_encode($selected_filters ?? [], JSON_UNESCAPED_UNICODE); ?>,
  searchQuery: "<?php echo Security::htmlentities($search_query ?? ''); ?>"
};
</script>