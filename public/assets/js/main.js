/**
 * Unified Main JavaScript for Projects and Yarn Management
 * FINAL VERSION
 */
document.addEventListener('DOMContentLoaded', function() {
    // --- Global Variables & Configuration ---
    const pageType = document.body.dataset.pageType || 'projects'; // 'projects' or 'yarn'
    const baseUrl = document.body.dataset.baseUrl || '/';
    const csrfTokenKey = document.querySelector('meta[name="csrf-token-key"]').getAttribute('content');
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    let searchTimeout;

    const createUrl = document.body.dataset.createUrl || '/projects/create';

    // --- DOM Element Cache ---
    const elements = {
        searchInput: document.getElementById('searchInput'),
        filterToggle: document.getElementById('filterToggle'),
        filterPanel: document.getElementById('filterPanel'),
        contentList: document.querySelector('.projects-container, .yarn-list-container'),
        activeFiltersContainer: document.querySelector('.active-filters'),
        deleteModal: document.getElementById('deleteModal'),
        deleteForm: document.getElementById('deleteForm'),
        cancelDelete: document.getElementById('cancelDelete')
    };

    // --- Event Listeners ---
    function initializeEventListeners() {
        if (elements.searchInput) {
            elements.searchInput.addEventListener('input', () => {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(performFilter, 300);
            });
        }

        if (elements.filterToggle) {
            elements.filterToggle.addEventListener('click', () => {
                elements.filterPanel.style.display = elements.filterPanel.style.display === 'none' ? 'block' : 'none';
            });
        }
        
        document.querySelectorAll('.filter-option input').forEach(checkbox => {
            checkbox.addEventListener('change', performFilter);
        });

        if (elements.activeFiltersContainer) {
            elements.activeFiltersContainer.addEventListener('click', (e) => {
                const tag = e.target.closest('.active-filter-tag');
                if (tag) {
                    const checkbox = document.querySelector(`input[value="${tag.dataset.value}"]`);
                    if (checkbox) {
                        checkbox.checked = false;
                        performFilter();
                    }
                }
            });
        }

        if (elements.contentList && pageType === 'yarn') {
            elements.contentList.addEventListener('click', (e) => {
                const deleteButton = e.target.closest('.delete-button');
                if (deleteButton) {
                    showDeleteModal(deleteButton.dataset.yarnId);
                }
            });
        }

        if (elements.cancelDelete) {
            elements.cancelDelete.addEventListener('click', () => elements.deleteModal.style.display = 'none');
        }
    }

    function performFilter() {
        const formData = new FormData();
        const csrfTokenKey = document.querySelector('meta[name="csrf-token-key"]').getAttribute('content');
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        
        formData.append(csrfTokenKey, csrfToken);
        formData.append('search', elements.searchInput.value);

        let endpoint;

        if (pageType === 'projects') {
            document.querySelectorAll('input[name="types[]"]:checked').forEach(cb => formData.append('types[]', cb.value));
            document.querySelectorAll('input[name="techniques[]"]:checked').forEach(cb => formData.append('techniques[]', cb.value));
            endpoint = 'projects/filter';
        }

        fetch(baseUrl + endpoint, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.projects) {
                updateProjectsList(data.projects);
                updateActiveFilters();
            } else {
                console.error('Filter request failed:', data.error);
            }
        })
        .catch(error => console.error('Fetch error:', error));
    }

    function updateProjectsList(projectsData) {
        if (projectsData.length === 0) {
            elements.contentList.innerHTML = `
                <div class="empty-state">
                    <p>プロジェクトが見つかりません。</p>
                    <a href="${createUrl}" class="create-project-link">新しいプロジェクトを作成</a>
                </div>
            `;
            return;
        }
        
        let html = '';

        projects = Object.entries(projectsData);

        console.log(projects);

        projects.forEach(project => {
            html += createProjectCardHTML(project[1]);
        });
        
        elements.contentList.innerHTML = html;
    }

    /**
     * Creates the HTML for a single project card.
     * @param {object} project - An object containing project data.
     * @returns {string} - The HTML string for the project card.
     */
    function createProjectCardHTML(project) {
        const imageUrl = project.screenshot_url ? `<img src="${project.screenshot_url}" alt="${project.name}">` : '<div class="image-placeholder"></div>';

        const techniqueTags = (project.technique_names || [])
            .map(technique => `<span class="tag tech-tag">${technique}</span>`)
            .join('');

        let progressBar = '';
        if (project.status == 1 || project.status == 2) { // 1: In Progress, 2: On Hold
            progressBar = `
                <div class="progress-bar-container">
                    <div class="progress-bar" style="width: ${project.progress || 0}%;"></div>
                </div>
            `;
        }

        return `
            <div class="project-card">
                <div class="project-image">
                    ${imageUrl}
                </div>
                <div class="project-details">
                    <div class="project-tags">
                        <span class="tag type-tag">${project.object_type}</span>
                        ${techniqueTags}
                    </div>
                    <h3 class="project-title">${project.name}</h3>
                    <p class="project-status">${project.status_text}</p>
                    ${progressBar}
                </div>
                <a href="${baseUrl}projects/detail/${project.id}" class="detail-link">詳細</a>
            </div>
        `;
    }

    function updateActiveFilters() {
        let html = '';
        document.querySelectorAll('.filter-option input:checked').forEach(checkbox => {
            const label = checkbox.closest('label').querySelector('span').textContent;
            html += `<div class="active-filter-tag" data-value="${checkbox.value}">${label} ✕</div>`;
        });
        if (elements.activeFiltersContainer) {
            elements.activeFiltersContainer.innerHTML = html;
        }
    }

    function showDeleteModal(yarnId) {
        if (elements.deleteForm) {
            elements.deleteForm.action = `${baseUrl}projects/delete_yarn/${yarnId}`;
            // Ensure the CSRF token is in the form for non-AJAX fallback
            const csrfInput = elements.deleteForm.querySelector(`input[name="${csrfTokenKey}"]`);
            if (csrfInput) {
                csrfInput.value = csrfToken;
            }
        }
        if (elements.deleteModal) {
            elements.deleteModal.style.display = 'flex';
        }
    }

    initializeEventListeners();
});