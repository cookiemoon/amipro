function AppViewModel(initialData) {
    const self = this;
    const baseUrl = document.body.dataset.baseUrl || '/';

    self.currentPage = ko.observable('list');
    self.currentPageViewModel = ko.observable();

    function ProjectListViewModel(data) {
        const self = this;
        var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        function updateToken(newToken) {
            csrfToken = newToken;
            document.querySelector('meta[name="csrf-token"]').setAttribute('content', newToken);
        }

        // Projects
        self.projects = ko.observableArray([]);

        const availableYarns = Object.values(data.availableYarns || {});
        self.availableYarns = ko.observableArray(availableYarns || []);

        // Filters & search
        self.searchQuery = ko.observable(data.searchQuery || '');
        self.filterPanelVisible = ko.observable(false);

        self.selectedTypes = ko.observableArray(data.selected.types || []);
        self.selectedTechniques = ko.observableArray(data.selected.techniques || []);

        self.availableTypes = Object.entries(data.filters.types || {}).map(([key, name]) => ({ key, name }));
        self.availableTechniques = Object.entries(data.filters.techniques || {}).map(([key, name]) => ({ key, name }));

        // Modal visibility
        self.showCreateModal = ko.observable(false);
        self.dropdownOpen = ko.observable(false);

        self.showModal = function() {
            self.showCreateModal(true);
        }

        self.hideModal = function() {
            self.showCreateModal(false);
        }

        // --- New Project Form ---
        self.newProject = {
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
            return self.newProject.name().trim() !== '' && self.newProject.objectType().trim() !== '';
        });

        // Yarn selection
        self.selectedYarns = ko.observableArray([]);

        self.yarnSearch = ko.observable('');

        self.filteredYarns = ko.computed(() => {
            const term = self.yarnSearch().toLowerCase();
            return self.availableYarns().filter(y => 
                y.name.toLowerCase().includes(term) &&
                !self.selectedYarns().some(sy => sy.id === y.id) // exclude already selected
            );
        });

        self.selectYarn = function(yarn) {
            self.selectedYarns.push(yarn);
            self.yarnSearch('');
            self.dropdownOpen(false);
        };

        self.yarnSearch.subscribe(value => {
            if (!value || value.trim() === '') {
                self.newProject.yarn(null);
            }
            self.dropdownOpen(value.trim().length > 0);
        });

        self.removeYarn = function(yarn) {
            self.selectedYarns.remove(yarn);
        };

        // Status & progress options
        self.statusOptions = [
            { value: 0, label: '未着手' },
            { value: 1, label: '進行中' },
            { value: 2, label: '中断中' },
            { value: 3, label: '完了' },
            { value: 4, label: '放棄' }
        ];

        self.newProject.status = ko.observable(0);
        self.newProject.progress = ko.observable(0);
        self.newProject.completionDate = ko.observable(null);

        self.showProgress = ko.computed(() => {
            return self.newProject.status() === 1 || self.newProject.status() === 2;
        });

        self.showCompletionDate = ko.computed(() => {
            return self.newProject.status() === 3;
        });

        // Techniques options
        self.suggestedTechniques = ko.observableArray(data.suggestedTechniques || [
            'ビーズ', 'ケーブル編み', 'フェアアイル', '交差編み', '配色編み',
            'かぎ針編み', '引き返し編み', 'レース'
        ]);

        self.toggleTechnique = function(tech) {
            if (!self.newProject.techniques().includes(tech)) {
                self.newProject.techniques.push(tech);
            } else {
                self.newProject.techniques.remove(tech);
            }
        };
        
        // Custom techniques handling
        self.newTechniqueInput = ko.observable('');
        
        self.addCustomTechnique = function() {
            const val = self.newTechniqueInput().trim();
            if (val && !self.newProject.techniques().includes(val)) {
                self.newProject.techniques.push(val);
            }
            self.newTechniqueInput('');
        };
        
        self.removeTechnique = function(tech) {
            self.newProject.techniques.remove(tech);
        };

        // Screenshot preview
        self.screenshotPreview = ko.computed(() => {
            const url = self.newProject.screenshotUrl();
            return url ? url : null;
        });

        self.colorworkScreenshotPreview = ko.computed(() => {
            const url = self.newProject.colorworkUrl();
            return url ? url : null;
        });
    
        // Form submission
        self.submitNewProject = function() {
            if (self.newProject.status() === 3 || self.newProject.progress() > 100) {
                self.newProject.progress(100);
            }

            if (self.newProject.progress() === 0 || self.newProject.progress() < 0) {
                self.newProject.status(0);
            }

            if (self.newProject.name().length > 32) {
                self.newProject.name(self.newProject.name().substring(0, 32));
            }

            if (self.newProject.objectType().length > 10) {
                self.newProject.objectType(self.newProject.objectType().substring(0, 10));
            }

            const today = new Date().toISOString().split('T')[0];

            const formData = new FormData();
            formData.append('item_type', 'project');

            formData.append('name', self.newProject.name().trim());
            formData.append('object_type', self.newProject.objectType().trim());
            formData.append('techniques', JSON.stringify(self.newProject.techniques()));
            formData.append('yarns', JSON.stringify(self.selectedYarns()));
            formData.append('status', self.newProject.status());
            formData.append('progress', self.newProject.progress());
            formData.append('created_at', self.newProject.startDate() || today);
            formData.append('completed_at', self.newProject.completionDate() || '');
            formData.append('memo', self.newProject.memo().trim());
            formData.append('screenshot_url', self.newProject.screenshotUrl().trim());
            formData.append('colorwork_url', self.newProject.colorworkUrl().trim());

            formData.append('fuel_csrf_token', csrfToken);

            fetch(`${baseUrl}projects/create`, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.new_csrf_token) {
                    updateToken(data.new_csrf_token);
                }
                if (data.success) {
                    alert("プロジェクトを作成しました。");
                    self.hideModal();
                    window.location.href = `${baseUrl}projects/detail/${data.project_id}`;
                } else {
                    console.error("Project creation error:",data.error);
                }
            })
            .catch(error => {
                console.error('Project creation error 2:', error);
            })
        };
    
        self.cancelCreate = function() {
            self.hideModal();
        };

        // --- Project loading & filtering ---
        self.loadProjects = function() {
            fetch(`${baseUrl}projects/data.json`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const projectsArray = Object.values(data.projects || {});
                        projectsArray.forEach(p => {
                            p.detail_url = `${baseUrl}projects/detail/${p.id}`;
                        });
                        self.projects(projectsArray);
                    }
                })
                .catch(err => console.error('Error loading projects:', err));
        };

        self.loadProjects();

        self.toggleFilterPanel = () => self.filterPanelVisible(!self.filterPanelVisible());

        self.filteredProjects = ko.computed(() => {
            return self.projects().filter(p => {
                // Search bar
                const matchesSearch = !self.searchQuery() ||
                    p.name.toLowerCase().includes(self.searchQuery().toLowerCase());
        
                // Type filter
                const matchesType = self.selectedTypes().length === 0 ||
                    self.selectedTypes().includes('全件') ||
                    self.selectedTypes().includes(p.object_type);
        
                // Techniques filter
                const matchesTechnique = self.selectedTechniques().length === 0 ||
                    (p.technique_names || []).some(t => self.selectedTechniques().includes(t));
        
                return matchesSearch && matchesType && matchesTechnique;
            });
        });

        self.logout = function() {
            window.location.href = `${baseUrl}auth/logout`;
        };        
    }

    self.changePage = function(page) {
        if (page === 'list') {
            self.currentPageViewModel(new ProjectListViewModel(initialData));
            self.currentPage('list');
        }
    };

    self.changePage('list');
}

document.addEventListener('DOMContentLoaded', function() {
    ko.applyBindings(new AppViewModel(window.initialData));
});
