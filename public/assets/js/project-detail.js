function ProjectDetailViewModel(projectId) {
    const self = this;
    const baseUrl = document.body.dataset.baseUrl;

    // Project details
    self.project = ko.observable({});
    self.rowCount = ko.observable(0);
    self.showModal = ko.observable(false);
    self.availableYarns = ko.observableArray([]);
    self.availableYarnsBackup = [];
    self.selectedYarns = ko.observableArray([]);
    self.yarnSearch = ko.observable('');

    self.dropdownOpen = ko.observable(false);

    // Row Counter
    self.incrementRow = () => {
        if (self.rowCount() < 999) self.rowCount(self.rowCount() + 1);
    }
    self.decrementRow = () => {
        if (self.rowCount() > 0) self.rowCount(self.rowCount() - 1);
    };

    // Delete project
    self.deleteProject = () => {
        if (!confirm("本当に削除しますか？")) return;

        formData = new FormData();
        formData.append('item_id', projectId);
        formData.append('item_type', 'project');

        fetch(`${baseUrl}projects/delete/`, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                window.location.href = `${baseUrl}projects`;
            }
        })
        .catch(err => console.error("Error deleting project:", err));
    };

    // Load project details
    self.loadProject = function() {
        fetch(`${baseUrl}projects/detail_data/${projectId}`)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    self.project(data.project);
                    const availableYarns = Object.values(data.available_yarn || {});
                    self.availableYarns(availableYarns);
                    self.availableYarnsBackup = availableYarns;
                    console.log(availableYarns);
                    self.selectedYarns(data.project.yarn_info || []);
                }
            })
            .catch(err => console.error("Error loading project:", err));
    };

    self.loadProject();

    // --- Edit project ---
    self.toEdit = {
        name: ko.observable(''),
        objectType: ko.observable(''),

        techniques: ko.observableArray([]),

        yarn: ko.observableArray([]),

        status: ko.observable(0),
        progress: ko.observable(0),

        startDate: ko.observable(null),
        completionDate: ko.observable(null),

        memo: ko.observable(''),
        
        screenshotUrl: ko.observable(''),
        colorworkUrl: ko.observable('')
    };

    self.isFormValid = ko.computed(() => {
        return self.toEdit.name().trim() !== '' && self.toEdit.objectType().trim() !== '';
    });

    // Yarn selection
    self.filteredYarns = ko.computed(() => {
        const term = self.yarnSearch().toLowerCase();
        return self.availableYarns().filter(y => 
            y.name.toLowerCase().includes(term) &&
            !self.selectedYarns().some(sy => sy.id === y.id)
        );
    });

    self.selectYarn = function(yarn) {
        self.selectedYarns.push(yarn);
        self.availableYarns.remove(yarn)
        self.yarnSearch('');

        self.dropdownOpen(false);
    };

    self.yarnSearch.subscribe(value => {
        if (!value || value.trim() === '') {
            self.toEdit.yarn(null);
        }
        self.dropdownOpen(value.trim().length > 0);
    });

    self.removeYarn = function(yarn) {
        self.availableYarns.push(yarn);
        self.selectedYarns.remove(yarn);
    };

    self.showProgress = ko.computed(() => {
        return self.toEdit.status() === 1 || self.toEdit.status() === 2;
    });

    self.showCompletionDate = ko.computed(() => {
        return self.toEdit.status() === 3;
    });

    self.openModal = () => {
        self.toEdit.name(self.project().name || '');
        self.toEdit.objectType(self.project().object_type || '');
        self.toEdit.techniques(self.project().technique_names || []);
        self.toEdit.yarn(self.project().yarn_name || []);
        self.toEdit.status(self.project().status || 0);
        self.toEdit.progress(self.project().progress || 0);
        self.toEdit.startDate(self.project().created_at || null);
        self.toEdit.completionDate(self.project().completed_at || null);
        self.toEdit.memo(self.project().memo || '');
        self.toEdit.screenshotUrl(self.project().screenshot_url || '');
        self.toEdit.colorworkUrl(self.project().colorwork_url || '');
        self.showModal(true);
    }

    self.closeModal = () => {
        self.availableYarns(self.availableYarnsBackup);
        self.showModal(false);
    }

    self.submitToEdit = () => {
        if (self.toEdit.status() === 3) {
            self.toEdit.progress(100);
        }

        if (self.toEdit.progress() === 0) {
            self.toEdit.status(0);
        }

        let formData = new FormData();
        formData.append('item_id', projectId);
        formData.append('item_type', 'project');

        formData.append('name', self.toEdit.name());
        formData.append('object_type', self.toEdit.objectType());
        formData.append('techniques', JSON.stringify(self.toEdit.techniques()));
        formData.append('yarn', JSON.stringify(self.selectedYarns()));
        formData.append('status', self.toEdit.status());
        formData.append('progress', self.toEdit.progress());
        formData.append('created_at', self.toEdit.startDate() || '');
        formData.append('completed_at', self.toEdit.completionDate() || '');
        formData.append('memo', self.toEdit.memo());
        formData.append('screenshot_url', self.toEdit.screenshotUrl());
        formData.append('colorwork_url', self.toEdit.colorworkUrl());

        fetch(`${baseUrl}projects/edit/`, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                self.availableYarnsBackup = self.availableYarns();
                self.showModal(false);
                self.loadProject();
            } else {
                alert("プロジェクトの更新に失敗しました。");
            }
        })
        .catch(err => console.error("Error editing project:", err));
    };

    self.statusOptions = [
        { value: 0, label: '未着手' },
        { value: 1, label: '進行中' },
        { value: 2, label: '中断中' },
        { value: 3, label: '完了' },
        { value: 4, label: '放棄' }
    ];
    
    // Custom techniques handling
    self.newTechniqueInput = ko.observable('');
    
    self.addCustomTechnique = function() {
        const val = self.newTechniqueInput().trim();
        if (val && !self.toEdit.techniques().includes(val)) {
            self.toEdit.techniques.push(val);
        }
        self.newTechniqueInput('');
    };
    
    self.removeTechnique = function(tech) {
        console.log(tech);
        self.toEdit.techniques.remove(tech);
    };

    // Screenshot preview
    self.screenshotPreview = ko.computed(() => {
        const url = self.toEdit.screenshotUrl();
        return url ? url : null;
    });

    self.colorworkScreenshotPreview = ko.computed(() => {
        const url = self.toEdit.colorworkUrl();
        return url ? url : null;
    });
}

document.addEventListener('DOMContentLoaded', function() {
    const projectId = document.body.dataset.projectId;
    ko.applyBindings(new ProjectDetailViewModel(projectId));
});
