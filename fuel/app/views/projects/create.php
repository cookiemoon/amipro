<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>新しいプロジェクト - あみぷろ</title>
    <?php // FIX: Use the Asset helper for consistency
    echo \Asset::css('projects.css');
    echo \Asset::css('create-project.css');
    ?>
</head>
<body>
    <div class="min-h-screen bg-gray-200">
        <div class="gradient-header">
            <a href="<?php echo \Uri::create('projects'); ?>" class="back-button">
                </a>
            <h1>新しいプロジェクト</h1>
            <div class="spacer"></div>
        </div>

        <?php // Check for and display any error flash messages
        if (\Session::get_flash('error')): ?>
            <div class="alert alert-error">
                <?php 
                    $error_msg = \Session::get_flash('error');
                    // Check if the error is an array (from validation) or a string
                    if (is_array($error_msg)) {
                        foreach($error_msg as $msg) {
                            echo $msg.'<br>';
                        }
                    } else {
                        echo $error_msg;
                    }
                ?>
            </div>
        <?php endif; ?>

        <div class="form-container">
            <div class="form-wrapper">
                <form id="createProjectForm" method="POST" action="<?php echo \Uri::create('projects/process_create'); ?>">
                    
                    <?php // FIX: CSRF token must be in a hidden input field
                    echo \Form::hidden(\Config::get('security.csrf_token_key'), \Security::fetch_token());
                    ?>
                    
                    <div class="form-section">
                        <label class="form-label" for="projectName"><span>プロジェクト名 *</span></label>
                        <input
                            type="text"
                            name="name"
                            id="projectName"
                            placeholder="例：冬のマフラー"
                            class="form-input <?php echo isset($errors['name']) ? 'error' : ''; ?>"
                            value="<?php echo isset($form_data['name']) ? \Security::htmlentities($form_data['name']) : ''; ?>"
                            required
                        />
                        <?php if (isset($errors['name'])): ?>
                            <p class="error-message"><?php echo $errors['name']; ?></p>
                        <?php endif; ?>
                    </div>

                    <div class="form-section">
                        <label class="form-label" for="projectType"><span>プロジェクトタイプ *</span></label>
                        <input
                            type="text"
                            name="project_type"
                            id="projectType"
                            placeholder="例：帽子、セーター、マフラー"
                            class="form-input <?php echo isset($errors['project_type']) ? 'error' : ''; ?>"
                            value="<?php echo isset($form_data['project_type']) ? \Security::htmlentities($form_data['project_type']) : ''; ?>"
                            required
                        />
                        <?php if (isset($errors['project_type'])): ?>
                            <p class="error-message"><?php echo $errors['project_type']; ?></p>
                        <?php endif; ?>
                    </div>

                    <div class="form-section">
                        <label class="form-label">
                            <span>編み技法</span>
                            <span class="form-subtitle">（複数追加可）</span>
                        </label>
                        
                        <div class="technique-input-container">
                            <input
                                type="text"
                                id="currentTechnique"
                                placeholder="例：交差編み、配色編み、レース編み"
                                class="technique-input"
                            />
                            <button
                                type="button"
                                id="addTechnique"
                                class="add-technique-button"
                            >
                                追加
                            </button>
                        </div>
                        
                        <div class="quick-add-techniques">
                            <p class="quick-add-label">よく使われる技法：</p>
                            <div class="quick-technique-buttons"></div>
                        </div>
                        
                        <div id="selectedTechniquesDisplay" class="selected-techniques" style="display: none;">
                            <p class="selected-techniques-label">選択された技法：</p>
                            <div id="techniqueTagsContainer" class="technique-tags"></div>
                        </div>
                        
                        <div id="selectedTechniquesInputs"></div>
                    </div>

                    <div class="form-section">
                        <label class="form-label" for="projectStartDate">
                            <span>開始日</span>
                        </label>
                        <input
                            type="date"
                            name="start_date"
                            id="projectStartDate"
                            class="form-input"
                            value="<?php echo isset($form_data['start_date']) ? \Security::htmlentities($form_data['start_date']) : ''; ?>"
                        />
                    </div>

                    <div class="form-section">
                        <label class="form-label" for="projectStatus">
                            <span>ステータス *</span>
                        </label>
                        <select name="status" id="projectStatus" class="form-select" required>
                            <?php foreach ($statuses as $key => $value): ?>
                                <option value="<?php echo $key; ?>" <?php echo (isset($form_data['status']) && $form_data['status'] == $key) ? 'selected' : ''; ?>>
                                    <?php echo $value; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-section" id="progressSection" style="display: none;">
                        <label class="form-label" for="projectProgress">
                            <span>進捗率: <span id="progressValue">0</span>%</span>
                        </label>
                        <input
                            type="range"
                            name="progress"
                            id="projectProgress"
                            class="form-range"
                            min="0"
                            max="100"
                            value="<?php echo isset($form_data['progress']) ? \Security::htmlentities($form_data['progress']) : '0'; ?>"
                        />
                    </div>

                    <div class="form-section" id="completionDateSection" style="display: none;">
                        <label class="form-label" for="projectCompletionDate">
                            <span>完了日</span>
                        </label>
                        <input
                            type="date"
                            name="completion_date"
                            id="projectCompletionDate"
                            class="form-input"
                            value="<?php echo isset($form_data['completion_date']) ? \Security::htmlentities($form_data['completion_date']) : ''; ?>"
                        />
                    </div>

                    <div class="form-section">
                        <label class="form-label" for="projectNotes"><span>メモ</span></label>
                        <textarea
                            name="notes"
                            id="projectNotes"
                            placeholder="パターンの詳細、変更点、参考情報など..."
                            rows="3"
                            class="form-textarea"
                        ><?php echo isset($form_data['notes']) ? \Security::htmlentities($form_data['notes']) : ''; ?></textarea>
                    </div>

                    <div class="form-section">
                        <label class="form-label" for="screenshotUrl">
                            <span>スクリーンショットURL</span>
                        </label>
                        <input
                            type="url"
                            name="screenshot_url"
                            id="screenshotUrl"
                            placeholder="https://example.com/image.png"
                            class="form-input"
                            value="<?php echo isset($form_data['screenshot_url']) ? \Security::htmlentities($form_data['screenshot_url']) : ''; ?>"
                        />
                    </div>

                    <div class="form-section">
                        <label class="form-label" for="colorworkUrl">
                            <span>カラーワークチャートURL</span>
                        </label>
                        <input
                            type="url"
                            name="colorwork_url"
                            id="colorworkUrl"
                            placeholder="https://example.com/chart.png"
                            class="form-input"
                            value="<?php echo isset($form_data['colorwork_url']) ? \Security::htmlentities($form_data['colorwork_url']) : ''; ?>"
                        />
                    </div>

                    <div class="action-buttons">
                        <a href="<?php echo \Uri::create('projects'); ?>" class="cancel-button">キャンセル</a>
                        <button type="submit" class="submit-button">プロジェクトを作成</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php echo \Asset::js('create-project.js'); ?>
    
    <script>
        <?php if (\Session::get_flash('error')): ?>
            showNotification('<?php echo \Security::js_encode(\Session::get_flash('error')); ?>', 'error');
        <?php endif; ?>
    </script>
</body>
</html>