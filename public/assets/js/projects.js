function AppViewModel(initialData) {
    const self = this;
    const baseUrl = document.body.dataset.baseUrl || '/';
    
    // --- Page State ---
    self.currentPage = ko.observable('list'); // "list" or "create"
    self.currentPageViewModel = ko.observable();

    // --- Project List ViewModel ---
    function ProjectListViewModel(data) {
        const listSelf = this;
        // Projects
        listSelf.projects = ko.observableArray([]);

        // Filters & search
        listSelf.searchQuery = ko.observable(data.searchQuery || '');
        listSelf.filterPanelVisible = ko.observable(false);

        listSelf.selectedTypes = ko.observableArray(data.selected.types || []);
        listSelf.selectedTechniques = ko.observableArray(data.selected.techniques || []);

        listSelf.availableTypes = Object.entries(data.filters.types || {}).map(([key, name]) => ({ key, name }));
        listSelf.availableTechniques = Object.entries(data.filters.techniques || {}).map(([key, name]) => ({ key, name }));

        listSelf.loadProjects = function() {
            fetch(`${baseUrl}projects/data.json`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const projectsArray = Object.values(data.projects || {});
                        projectsArray.forEach(p => {
                            p.detail_url = `${baseUrl}projects/detail/${p.id}`;
                        });
                        listSelf.projects(projectsArray);
                    }
                })
                .catch(err => console.error('Error loading projects:', err));
        };

        listSelf.loadProjects();

        // --- Toggle filter panel ---
        listSelf.toggleFilterPanel = () => listSelf.filterPanelVisible(!listSelf.filterPanelVisible());

        // --- Computed: filtered projects ---
        listSelf.filteredProjects = ko.computed(() => {
            return listSelf.projects().filter(p => {
                // --- Search filter ---
                const matchesSearch = !listSelf.searchQuery() ||
                    p.name.toLowerCase().includes(listSelf.searchQuery().toLowerCase());
        
                // --- Type filter ---
                const matchesType = listSelf.selectedTypes().length === 0 ||
                    listSelf.selectedTypes().includes(p.object_type);
        
                // --- Technique filter ---
                const matchesTechnique = listSelf.selectedTechniques().length === 0 ||
                    (p.technique_names || []).some(t => listSelf.selectedTechniques().includes(t));
        
                return matchesSearch && matchesType && matchesTechnique;
            });
        });
        

        // --- Navigate to create page ---
        listSelf.goToCreate = () => self.changePage('create');
    }

    // --- Project Create ViewModel ---
    function ProjectCreateViewModel() {
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
            self.currentPageViewModel(new ProjectListViewModel(initialData));
        } else if (page === 'create') {
            self.currentPageViewModel(new ProjectCreateViewModel());
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
