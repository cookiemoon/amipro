<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<title><?php echo Security::htmlentities($title); ?></title>
<?php echo Asset::css('detail.css'); ?>
</head>
<body data-base-url="<?php echo \Uri::base(); ?>" 
      data-project-id="<?php echo $project["id"]; ?>" 
      class="project-color-page">

<div class="container">
    <div class="header">
        <div class="header-content">
            <h1>あみぷろ</h1>
        </div>
    </div>

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
                <img data-bind="attr: { src: project().colorwork_url }" class="colorwork-image">
            </div>

            <!-- Custom mode -->
            <div data-bind="visible: mode() === 'custom'" class="custom-chart-area">
                <div class="controls">
                    <label>幅: <input type="number" min="1" max="50" data-bind="value: width, 
                                                                            valueUpdate: 'afterkeydown',
                                                                            event: { change: initChart }"></label>
                    <label>高さ: <input type="number" min="1" max="50" data-bind="value: height,
                                                                            valueUpdate: 'afterkeydown',
                                                                            event: { change: initChart }"></label>

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
                                    style: { backgroundColor: $data || '#fff' },
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

        <div class="row-counter">
                <button class="minus" data-bind="click: decrementRow">−</button>
                <span class="rows" data-bind="text: rowCount"></span>
                <button class="plus" data-bind="click: incrementRow">＋</button>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/knockout/3.5.1/knockout-latest.js"></script>
<script src="<?php echo \Uri::base(); ?>assets/js/color.js"></script>
</body>
</html>