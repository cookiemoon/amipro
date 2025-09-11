<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<title><?php echo Security::htmlentities($title); ?></title>
<?php echo Asset::css('detail.css'); ?>
</head>
<body data-base-url="<?php echo \Uri::base(); ?>" 
      data-project-id="<?php echo $project["id"]; ?>" 
      class="project-detail-page">

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
        class="tab active">
            <p>詳細情報</p>
        </a>

        <a href="<?php echo Uri::create('projects/color/'.$project["id"]); ?>"
           class="tab">
            <p>カラーチャート</p>
        </a>
    </div>

    <div class="main-content-area">
        <!-- Title -->
        <h1 class="project-title" data-bind="text: project().name"></h1>

        <!-- Top section: screenshot + info -->
        <div class="project-top">
            <!-- Screenshot -->
            <div class="sidebar">
                <!-- ko if : project().screenshot_url && project().screenshot_url.length > 0 -->
                <div class="screenshot-box">
                    <img data-bind="attr: { src: project().screenshot_url, alt: project().name }, visible: project().screenshot_url">
                </div>
                <!-- /ko -->

                <!-- ko if: project().screenshot_url -->
                <div class="elem-spacer"></div>
                <p data-bind="text: project().status_text"></p>
                <div class="elem-spacer"></div>
                <!-- /ko -->

                <!-- ko if: (project().status == 1 || project().status == 2 || project().status == 3) && project().screenshot_url -->
                <div class="progress-bar-container">
                    <div class="progress-bar" data-bind="style: { width: project().progress + '%' }"></div>
                </div>
                <!-- /ko -->
            </div>

            <!-- Info + tags -->
            <div class="project-info">
                <div class="tags">
                    <span class="tag type-tag" data-bind="text: project().object_type"></span>
                    <!-- ko foreach: project().technique_names -->
                        <span class="tag tech-tag" data-bind="text: $data"></span>
                    <!-- /ko -->
                </div>

                <div class="div-spacer"></div>
                <p><span data-bind="text: project().created_text + '開始'"></span></p>
                <!-- ko if: project().status == 3 && project().completed_at -->
                <div class="elem-spacer"></div>
                <p><span data-bind="text: project().completed_text + '完了'"></span></p>
                <!-- /ko -->

                <div class="div-spacer"></div>
                <!-- ko if: project().yarn_name && project().yarn_name.length > 0 -->
                <p>毛糸:</p>
                <ul data-bind="foreach: project().yarn_name">
                    <li data-bind="text: $data"></li>
                </ul>
                <!-- /ko -->

                <!-- ko ifnot: project().screenshot_url -->
                <div class="elem-spacer"></div>
                <p data-bind="text: project().status_text"></p>
                <div class="elem-spacer"></div>
                <div class="progress-bar-container">
                    <div class="progress-bar" data-bind="style: { width: project().progress + '%' }"></div>
                </div>
                <!-- /ko -->
            </div>

            <!-- Actions -->
            <div class="project-actions">
                <a href="#" class="edit-link" data-bind="click: openModal">編集</a>
                <a href="#" class="delete-link" data-bind="click: deleteProject">削除</a>
            </div>
        </div>

        <!-- Memo -->
        <div class="bottom-section">
            <!-- ko if: project().memo && project().memo.length > 0 -->
            <div class="memo-box">
                <h3>メモ:</h3>
                <p data-bind="text: project().memo"></p>
            </div>
            <!-- /ko -->

            <!--ko ifnot: project().memo-->
            <div></div>
            <!-- /ko -->

            <!-- Row counter -->
            <div class="row-counter">
                <button class="minus" data-bind="click: decrementRow">−</button>
                <span class="rows" data-bind="text: rowCount"></span>
                <button class="plus" data-bind="click: incrementRow">＋</button>
            </div>
        </div>
    </div>

    <div class="modal-overlay" data-bind="visible: showModal">
        <div class="modal-window">
            <h2>プロジェクトを編集</h2>

            <form>
                <label>
                    プロジェクト名: <span class="required">*</span>
                    <input type="text" data-bind="value: toEdit.name">
                </label>

                <!-- Object Type -->
                <label>
                    プロジェクトタイプ: <span class="required">*</span>
                    <input type="text" data-bind="value: toEdit.objectType" placeholder="例: セーター">
                </label>

                <!-- Techniques -->
                <label>
                    技法 (選択可能):

                    <!-- Free input for custom technique -->
                    <div class="custom-technique-input">
                        <input type="text" placeholder="カスタム技法を追加"
                            data-bind="value: newTechniqueInput, valueUpdate: 'afterkeydown', event: { keyup: function(data, event) { if(event.key === 'Enter') { addCustomTechnique(); } } }">
                        <button type="button" data-bind="click: addCustomTechnique">追加</button>
                    </div>


                    <!-- Live preview of selected techniques -->
                    <div class="tech-preview" data-bind="foreach: toEdit.techniques">
                        <span class="tech-tag">
                            <span data-bind="text: $data"></span>
                            <button type="button" data-bind="click: $parent.removeTechnique">×</button>
                        </span>
                    </div>
                </label>

                <div class="completion-date-container">
                    <label>
                        開始日:
                        <input type="date" data-bind="value: toEdit.startDate">
                    </label>
                </div>

                <label>
                    状態:
                    <select data-bind="value: toEdit.status">
                        <!-- ko foreach: statusOptions -->
                        <option data-bind="value: value, text: label"></option>
                        <!-- /ko -->
                    </select>
                </label>

                <!-- Progress slider for 進行中 or 中断中 -->
                <div class="progress-container" data-bind="visible: showProgress">
                    <label>
                        進捗 (%):
                        <input type="range" min="0" max="100" data-bind="value: toEdit.progress">
                        <span data-bind="text: toEdit.progress"></span>%
                    </label>
                </div>

                <!-- Completion date picker for 完了 -->
                <div class="completion-date-container" data-bind="visible: showCompletionDate">
                    <label>
                        完了日:
                        <input type="date" data-bind="value: toEdit.completionDate">
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
                <textarea data-bind="value: toEdit.memo" 
                        placeholder="ここに自由にメモを入力できます..."
                        rows="6"></textarea>

                <!-- Screenshot -->
                <label>
                    スクリーンショットURL:
                    <input type="text" data-bind="value: toEdit.screenshotUrl" placeholder="https://example.com/image.jpg">
                </label>

                <!-- Live preview -->
                <div class="screenshot-preview" data-bind="visible: screenshotPreview">
                    <p>プレビュー:</p>
                    <img data-bind="attr: { src: screenshotPreview }" alt="スクリーンショットプレビュー">
                </div>

                <!-- Colorwork -->
                <label>
                    カラーチャートURL:
                    <input type="text" data-bind="value: toEdit.colorworkUrl" placeholder="https://example.com/image.jpg">
                </label>

                <!-- Live preview -->
                <div class="screenshot-preview" data-bind="visible: colorworkScreenshotPreview">
                    <p>プレビュー:</p>
                    <img data-bind="attr: { src: colorworkScreenshotPreview }" alt="スクリーンショットプレビュー">
                </div>

                <div class="modal-actions">
                <button type="button" data-bind="click: submitToEdit, enable: isFormValid">保存</button>
                <button type="button" data-bind="click: closeModal">キャンセル</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/knockout/3.5.1/knockout-latest.js"></script>
<script>
window.initialData = {
    availableYarns: <?php echo json_encode($available_yarn ?? [], JSON_UNESCAPED_UNICODE); ?>,
};
</script>
<script src="<?php echo \Uri::base(); ?>assets/js/project-detail.js"></script>
</body>
</html>
