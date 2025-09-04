function AppViewModel(initialData) {
    const self = this;
    const baseUrl = document.body.dataset.baseUrl || '/';
    
    // --- Page State ---
    self.currentPage = ko.observable('list'); // "list" or "create"
    self.currentPageViewModel = ko.observable();

    // --- Yarn List ViewModel ---
    function YarnListViewModel(data) {
        const listSelf = this;
        // Projects
        listSelf.yarn = ko.observableArray([]);

        // Filters & search
        listSelf.searchQuery = ko.observable(data.searchQuery || '');
        listSelf.filterPanelVisible = ko.observable(false);

        listSelf.selectedWeights = ko.observableArray(data.selected.weight || []);
        listSelf.selectedFibers = ko.observableArray(data.selected.fiber || []);

        listSelf.availableWeights = Object.entries(data.filters.weight || {}).map(([key, name]) => ({ key, name }));
        listSelf.availableFibers = Object.entries(data.filters.fiber || {}).map(([key, name]) => ({ key, name }));

        listSelf.loadYarns = function() {
            fetch(`${baseUrl}projects/yarn/data.json`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const yarnArray = Object.values(data.yarn || {});
                        listSelf.yarn(yarnArray);
                    }
                })
                .catch(err => console.error('Error loading projects:', err));
        };

        listSelf.loadYarns();

        // --- Toggle filter panel ---
        listSelf.toggleFilterPanel = () => listSelf.filterPanelVisible(!listSelf.filterPanelVisible());

        // --- Computed: filtered projects ---
        listSelf.filteredYarns = ko.computed(() => {
            return listSelf.yarn().filter(y => {
                // --- Search filter ---
                const matchesSearch = !listSelf.searchQuery() ||
                    y.name.toLowerCase().includes(listSelf.searchQuery().toLowerCase());
        
                const matchesWeight = listSelf.selectedWeights().length === 0 ||
                    listSelf.selectedWeights().includes(y.size);
        
                // --- Fiber filter ---
                const matchesFiber = listSelf.selectedFibers().length === 0 ||
                    (y.fiber_types || []).some(t => listSelf.selectedFibers().includes(t));
        
                return matchesSearch && matchesWeight && matchesFiber;
            });
        });

        // --- Navigate to create page ---
        listSelf.goToCreate = () => self.changePage('create');
    }

    // --- Project Create ViewModel ---
    function YarnCreateViewModel() {
        const createSelf = this;

        createSelf.newProject = {
            name: ko.observable(''),
            // add more project fields here if needed
        };

        createSelf.submitNewProject = function() {
            console.log("Submitting project:", ko.toJS(createSelf.newProject));
            // TODO: AJAX POST logic
        };

        createSelf.cancelCreate = () => self.changePage('list');
    }

    // --- Page Navigation ---
    self.changePage = function(page) {
        self.currentPage(page);
        if (page === 'list') {
            self.currentPageViewModel(new YarnListViewModel(initialData));
        } else if (page === 'create') {
            self.currentPageViewModel(new YarnCreateViewModel());
        }
    };

    // --- Template Selector ---
    self.currentTemplate = ko.computed(() => `project-${self.currentPage()}-template`);

    // --- Start on project list ---
    self.changePage('list');
}

// --- Activate Knockout ---
document.addEventListener('DOMContentLoaded', function() {
    ko.applyBindings(new AppViewModel(window.initialData));
});
