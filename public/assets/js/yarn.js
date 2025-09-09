function AppViewModel(initialData) {
    const self = this;
    const baseUrl = document.body.dataset.baseUrl || '/';
    // const csrfTokenKey = document.querySelector('meta[name="csrf-token-key"]').getAttribute('content');
    
    // --- Page State ---
    self.currentPage = ko.observable('list');
    self.currentPageViewModel = ko.observable();

    // --- Yarn List ViewModel ---
    function YarnListViewModel(data) {
        const self = this;
        // const csrfTokenKey = data.csrfTokenKey || 'csrf_token';
        // Yarns

        self.yarns = ko.observableArray([]);

        const availableProjects = Object.values(data.availableProjects || {});
        self.availableProjects = ko.observableArray(availableProjects || []);

        // Filters & search
        self.searchQuery = ko.observable(data.searchQuery || '');
        self.filterPanelVisible = ko.observable(false);

        self.selectedWeights = ko.observableArray(data.selected.weight || []);
        self.selectedFibers = ko.observableArray(data.selected.fiber || []);

        self.availableWeights = Object.entries(data.filters.weight || {}).map(([key, name]) => ({ key, name }));
        self.availableFibers = Object.entries(data.filters.fiber || {}).map(([key, name]) => ({ key, name }));

        self.weightSelection = self.availableWeights.filter(w => w.key !== '全件');

        self.newYarn = {
            id: null,  // for editing existing yarns

            name: ko.observable(''),
            brand: ko.observable(''),

            project: ko.observable(''),

            color: ko.observable(''),
            weight: ko.observable(''),

            fibers: ko.observableArray([]),
            fiberDesc: ko.observable(''),  

            project: ko.observable(null),
        };

        // Modal visibility
        self.showCreateModal = ko.observable(false);
        self.dropdownOpen = ko.observable(false);
        
        self.resetYarn = function() {
            self.newYarn.id = null;
            self.newYarn.name('');
            self.newYarn.brand('');
            self.newYarn.color('');
            self.newYarn.weight('');
            self.newYarn.fibers([]);
            self.newYarn.fiberDesc('');
            self.newYarn.project(null);
            self.projectSearch('');
            self.currentEditYarn(null);
        }

        self.showModal = function() {
            self.resetYarn();
            self.showCreateModal(true);
        }

        self.hideModal = function() {
            self.resetYarn();
            self.showCreateModal(false);
        }

        // --- New Yarn Form ---
        self.isFormValid = ko.computed(() => {
            return self.newYarn.name().trim() !== '';
        });

        // Yarn selection
        self.projectSearch = ko.observable('');

        self.projectSearch.subscribe(value => {
            if (!value || value.trim() === '') {
                self.newYarn.project(null);
            }
            self.dropdownOpen(value.trim().length > 0); 
        });
        
        self.filteredProjects = ko.computed(() => {
            const term = self.projectSearch().toLowerCase();
            return self.availableProjects().filter(y => y.name.toLowerCase().includes(term));
        });

        self.selectProject = function(project) {
            self.newYarn.project(project.id);
            self.projectSearch(project.name);
            self.dropdownOpen(false);
        };
    
        // Form submission
        self.submitNewYarn = function() {
            const formData = new FormData();
            // const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            const isEdit = !!self.currentEditYarn();
            if (isEdit) {
                formData.append('item_id', self.newYarn.id);
                formData.append('item_type', 'yarn');
            }

            const url = isEdit ? `${baseUrl}projects/edit` : `${baseUrl}projects/yarn`;

            formData.append('name', self.newYarn.name().trim());
            formData.append('brand', self.newYarn.brand().trim());
            formData.append('project', self.newYarn.project());
            formData.append('color', self.newYarn.color().trim());
            formData.append('weight', self.newYarn.weight() ? self.newYarn.weight() : '');
            formData.append('fiber_animal', self.newYarn.fibers().includes('動物性繊維'));
            formData.append('fiber_plant', self.newYarn.fibers().includes('植物繊維'));
            formData.append('fiber_synthetic', self.newYarn.fibers().includes('合成繊維'));
            formData.append('fiber_desc', self.newYarn.fiberDesc().trim());
            formData.append('project_id', self.newYarn.project());
            // formData.append(csrfTokenKey, csrfToken);

            fetch(url, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    self.hideModal();
                    self.loadYarns();
                } else {
                    console.error("Yarn error:",data.error);
                }
            })
            .catch(error => {
                console.error('Yarn error 2:', error);
            })
        };
    
        self.cancelCreate = function() {
            self.hideModal();
        };

        // --- Yarn loading & filtering ---
        self.loadYarns = function() {
            fetch(`${baseUrl}projects/yarns/data.json`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const yarnsArray = Object.values(data.yarn || {});
                        self.yarns(yarnsArray);
                    }
                })
                .catch(err => console.error('Error loading yarns:', err));
        };

        self.loadYarns();

        self.toggleFilterPanel = () => self.filterPanelVisible(!self.filterPanelVisible());

        self.filteredYarns = ko.computed(() => {
            return self.yarns().filter(p => {
                // Search bar
                const matchesSearch = !self.searchQuery() ||
                    p.name.toLowerCase().includes(self.searchQuery().toLowerCase()) ||
                    (p.brand && p.brand.toLowerCase().includes(self.searchQuery().toLowerCase()));
        
                // Weight filter
                const matchesWeight = self.selectedWeights().length === 0 || 
                    self.selectedWeights().includes('全件') ||
                    self.selectedWeights().includes(p.weight);
        
                // Fibers filter
                const matchesFiber = self.selectedFibers().length === 0 ||
                    (p.fiber_types || []).some(t => self.selectedFibers().includes(t));
        
                return matchesSearch && matchesWeight && matchesFiber;
            });
        });

        // --- Delete Yarn ---
        self.deleteYarn = function(yarn) {
            if (!confirm(`「${yarn.name}」を削除しますか？`)) {
                return;
            }

            formData = new FormData();
            formData.append('item_id', yarn.id);
            formData.append('item_type', 'yarn');
        
            fetch(`${baseUrl}projects/delete`, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    self.yarns.remove(yarn);
                } else {
                    alert("削除に失敗しました。");
                }
            })
            .catch(err => {
                console.error('Error deleting yarn:', err);
                alert("エラーが発生しました。");
            });
        };

        // --- Edit Yarn ---
        self.currentEditYarn = ko.observable(null);

        self.showEditModal = function(yarn) {
            self.newYarn.name(yarn.name);
            self.newYarn.brand(yarn.brand || '');
            self.newYarn.color(yarn.color || '');
            self.newYarn.weight(yarn.weight || '');
            self.newYarn.id = yarn.id;
            self.newYarn.fibers(yarn.fiber_types || []);
            self.newYarn.fiberDesc(yarn.fiber_desc || '');
            self.newYarn.project(yarn.project_id || null);
            if (yarn.project_id) {
                self.projectSearch(yarn.project_name);
                self.dropdownOpen(false);
            }
            self.currentEditYarn(yarn);
        
            self.showCreateModal(true);
        };

        self.editYarn = function(yarn) {
            self.showEditModal(yarn);
        };

        // --- Logout ---        
        self.logout = function() {
            window.location.href = `${baseUrl}projects/logout`;
        };        
    }
    
    // --- Page Navigation ---
    self.changePage = function(page) {
        if (page === 'list') {
            // initialData.csrfTokenKey = csrfTokenKey;
            self.currentPageViewModel(new YarnListViewModel(initialData));
            self.currentPage('list');
        }
    };

    // --- Start on project list ---
    self.changePage('list');
}

// --- Activate Knockout ---
document.addEventListener('DOMContentLoaded', function() {
    ko.applyBindings(new AppViewModel(window.initialData));
});
