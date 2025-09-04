/**
 * Create Project Page JavaScript
 * FINAL UNIFIED VERSION
 */
document.addEventListener('DOMContentLoaded', function() {
    // --- Configuration ---
    const commonTechniques = [
        '交差編み', '配色編み', 'ビーズ', 'レース編み',
        'ケーブル編み', 'インターシャ', 'フェアアイル', 'ゴム編み'
    ];

    // --- DOM Element Cache ---
    const elements = {
        techniqueInput: document.getElementById('currentTechnique'),
        addTechniqueBtn: document.getElementById('addTechnique'),
        quickTechniqueContainer: document.querySelector('.quick-technique-buttons'),
        tagsContainer: document.getElementById('techniqueTagsContainer'),
        hiddenInputsContainer: document.getElementById('selectedTechniquesInputs'),
        selectedTechniquesDisplay: document.getElementById('selectedTechniquesDisplay'),
        quickAddSection: document.querySelector('.quick-add-techniques'),
        statusSelect: document.getElementById('projectStatus'),
        progressSection: document.getElementById('progressSection'),
        progressSlider: document.getElementById('projectProgress'),
        progressValue: document.getElementById('progressValue'),
        completionDateSection: document.getElementById('completionDateSection')
    };

    let selectedTechniques = [];

    // --- Core Functions ---

    function addTechnique(technique) {
        const trimmedTechnique = technique.trim();
        if (trimmedTechnique && !selectedTechniques.includes(trimmedTechnique)) {
            selectedTechniques.push(trimmedTechnique);
            renderTechniqueTags();
            generateQuickAddButtons();
        }
    }

    function removeTechnique(technique) {
        selectedTechniques = selectedTechniques.filter(t => t !== technique);
        renderTechniqueTags();
        generateQuickAddButtons();
    }

    function renderTechniqueTags() {
        elements.selectedTechniquesDisplay.style.display = selectedTechniques.length > 0 ? 'block' : 'none';
        elements.tagsContainer.innerHTML = '';
        elements.hiddenInputsContainer.innerHTML = '';
        selectedTechniques.forEach(technique => {
            const tagElement = document.createElement('div');
            tagElement.className = 'technique-tag';
            tagElement.textContent = technique;
            const removeBtn = document.createElement('button');
            removeBtn.type = 'button';
            removeBtn.className = 'remove-technique-btn';
            removeBtn.textContent = '✕';
            removeBtn.dataset.technique = technique;
            tagElement.appendChild(removeBtn);
            elements.tagsContainer.appendChild(tagElement);
            const hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.name = 'techniques[]';
            hiddenInput.value = technique;
            elements.hiddenInputsContainer.appendChild(hiddenInput);
        });
    }

    function generateQuickAddButtons() {
        if (!elements.quickTechniqueContainer || !elements.quickAddSection) return;
        const availableQuickAdds = commonTechniques.filter(t => !selectedTechniques.includes(t));
        if (availableQuickAdds.length === 0) {
            elements.quickAddSection.style.display = 'none';
            return;
        }
        elements.quickAddSection.style.display = 'block';
        elements.quickTechniqueContainer.innerHTML = '';
        availableQuickAdds.forEach(technique => {
            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'quick-technique-button';
            button.dataset.technique = technique;
            button.textContent = `+ ${technique}`;
            elements.quickTechniqueContainer.appendChild(button);
        });
    }

    function initializeSelectedTechniques() {
        const initialInputs = elements.hiddenInputsContainer.querySelectorAll('input[name="techniques[]"]');
        initialInputs.forEach(input => {
            if (input.value && !selectedTechniques.includes(input.value)) {
                selectedTechniques.push(input.value);
            }
        });
        renderTechniqueTags();
    }
    
    function updateFormFieldsVisibility() {
        if (!elements.statusSelect) return;
        const selectedStatus = elements.statusSelect.value;

        // Progress Slider: Show for "In Progress" (1) or "On Hold" (2)
        if (elements.progressSection) {
            elements.progressSection.style.display = (selectedStatus === '1' || selectedStatus === '2') ? 'block' : 'none';
        }

        // Completion Date: Show for "Completed" (3)
        if (elements.completionDateSection) {
            elements.completionDateSection.style.display = (selectedStatus === '3') ? 'block' : 'none';
        }
    }

    // --- Event Listeners ---

    if (elements.addTechniqueBtn) {
        elements.addTechniqueBtn.addEventListener('click', () => {
            addTechnique(elements.techniqueInput.value);
            elements.techniqueInput.value = '';
        });
    }
    
    if (elements.techniqueInput) {
        elements.techniqueInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                addTechnique(elements.techniqueInput.value);
                elements.techniqueInput.value = '';
            }
        });
    }

    if (elements.quickTechniqueContainer) {
        elements.quickTechniqueContainer.addEventListener('click', (e) => {
            if (e.target.classList.contains('quick-technique-button')) {
                addTechnique(e.target.dataset.technique);
            }
        });
    }
    
    if (elements.tagsContainer) {
        elements.tagsContainer.addEventListener('click', (e) => {
            if (e.target.classList.contains('remove-technique-btn')) {
                removeTechnique(e.target.dataset.technique);
            }
        });
    }

    if (elements.statusSelect) {
        elements.statusSelect.addEventListener('change', updateFormFieldsVisibility);
    }
    
    if (elements.progressSlider && elements.progressValue) {
        elements.progressSlider.addEventListener('input', () => {
            elements.progressValue.textContent = elements.progressSlider.value;
        });
        elements.progressValue.textContent = elements.progressSlider.value;
    }

    // --- Initial Setup ---
    initializeSelectedTechniques();
    generateQuickAddButtons();
    updateFormFieldsVisibility();
});