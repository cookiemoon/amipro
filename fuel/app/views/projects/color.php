<meta name="project-id" content="<?php echo $project["id"]; ?>">
<div class="container">

  <div class="back-to-projects">
    <a href="<?php echo Uri::create('projects'); ?>"
      class="back-tab"><span class="back-arrow">←</span></a>
  </div>

  <!-- Navigation Tabs -->
  <div class="navigation-tabs">
    <a href="<?php echo Uri::create('projects/detail/'.$project["id"]); ?>"
    class="tab">
      <p>詳細情報</p>
    </a>

    <a href="<?php echo Uri::create('projects/color/'.$project["id"]); ?>"
       class="tab active">
      <p>カラーチャート</p>
    </a>
  </div>

  <div class="main-content-area">
    <h1 class="project-title" data-bind="text: project().name"></h1>

    <div class="colorwork-page">
      <div class="mode-toggle">
        <label class="switch">
          <input type="checkbox" data-bind="checked: modeToggle">
          <span class="slider"></span>
        </label>
        <span data-bind="text: modeLabel"></span>
      </div>

      <!-- Image mode -->
      <div data-bind="visible: mode() === 'screenshot'">
        <!-- ko if: project().colorwork_url -->
        <img data-bind="attr: { src: project().colorwork_url }" class="colorwork-image">
        <!-- /ko -->
        <!-- ko ifnot: project().colorwork_url -->
        <p class="no-image-text">カラーチャート画像がありません。</p>
        <!-- /ko -->
      </div>

      <!-- Custom mode -->
      <div data-bind="visible: mode() === 'custom'" class="custom-chart-area">
        <div class="controls">
          <label>幅: <input type="number" min="1" max="50" data-bind="value: width, 
                                      valueUpdate: 'afterkeydown',
                                      event: { change: updateChart }"></label>
          <label>高さ: <input type="number" min="1" max="50" data-bind="value: height,
                                      valueUpdate: 'afterkeydown',
                                      event: { change: updateChart }"></label>

          <input type="color" class="color-input" data-bind="value: currentColor">

          <div class="pixel-shape-selection">
            <label>
              <input type="radio" name="stitchShape" value="square"
                data-bind="checked: stitchShape">
              四角
            </label>
            <label>
              <input type="radio" name="stitchShape" value="knit" 
                data-bind="checked: stitchShape">
              表目
            </label>
          </div>
        </div>

        <!-- Chart -->
        <div class="chart-grid" data-bind="foreach: chart">
          <div class="chart-row" data-bind="foreach: $data">
            <span class="pixel-bg">
              <div class="pixel"
                data-bind="
                  style: { backgroundColor: $data || '#ffffff' },
                  css: $root.stitchShape(),
                  click: () => $parents[1].paintPixel($parentContext.$index(), $index())">
              </div>
            </span>
          </div>
        </div>

        <div class="controls">
          <button class="controls-btn" data-bind="click: clearChart">クリア</button>
          <button class="controls-btn" data-bind="click: saveChart">保存</button>
        </div>

      </div>
    </div>

    <div class="row-controls">
      <p>段数</p>
      <div class="row-counter">
          <button class="minus" data-bind="click: decrementRow">−</button>
          <span class="rows" data-bind="text: rowCount"></span>
          <button class="plus" data-bind="click: incrementRow">＋</button>
      </div>
      <button class="controls-btn" data-bind="click: saveRow">保存</button>
    </div>
  </div>
</div>