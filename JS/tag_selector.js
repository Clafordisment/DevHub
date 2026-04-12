// Модуль для выбора тегов при создании публикации

class TagSelector {
    constructor() {
        this.selectedTags = new Map();
        this.modal = document.getElementById('tags-modal');
        this.selectTagsBtn = document.getElementById('select-tags-btn');
        this.closeModalBtn = document.getElementById('close-modal-btn');
        this.applyTagsBtn = document.getElementById('apply-tags-btn');
        this.modalSelectedTagsDiv = document.getElementById('modal-selected-tags');
        this.postTagsContainer = document.getElementById('post-tags-container');
        this.selectedTagsListDiv = document.getElementById('selected-tags-list');
        this.selectedTagsInput = document.getElementById('selected-tags-input');
        
        // Загружаем существующие теги из data-атрибута
        this.loadExistingTags();
        
        this.init();
    }
    
    loadExistingTags() {
        const postData = document.getElementById('post-data');
        if (postData && postData.dataset.existingTags) {
            const existingTags = JSON.parse(postData.dataset.existingTags);
            existingTags.forEach(tagId => {
                const tagIdStr = tagId.toString();
                const tagItem = document.querySelector(`.tag-item[data-tag-id="${tagIdStr}"]`);
                if (tagItem) {
                    const tagName = tagItem.dataset.tagName;
                    const colorCode = tagItem.dataset.colorCode;
                    this.selectedTags.set(tagIdStr, { name: tagName, colorCode: colorCode });
                    tagItem.style.opacity = '1';
                    tagItem.style.borderWidth = '2px';
                }
            });
        }
    }
    
    init() {
        this.initEventListeners();
        this.initTagItems();
        this.initCategoryCollapse();
        this.updatePostTagsDisplay();
    }
    
    initEventListeners() {
        if (this.selectTagsBtn) {
            this.selectTagsBtn.addEventListener('click', () => this.openModal());
        }
        
        if (this.closeModalBtn) {
            this.closeModalBtn.addEventListener('click', () => this.closeModal());
        }
        
        if (this.applyTagsBtn) {
            this.applyTagsBtn.addEventListener('click', () => this.applyTags());
        }
        
        if (this.modal) {
            this.modal.addEventListener('click', (e) => {
                if (e.target === this.modal) {
                    this.closeModal();
                }
            });
        }
    }
    
    initTagItems() {
        document.querySelectorAll('.tag-item').forEach(tagItem => {
            const tagId = tagItem.dataset.tagId;
            if (this.selectedTags.has(tagId)) {
                tagItem.style.opacity = '1';
                tagItem.style.borderWidth = '2px';
            } else {
                tagItem.style.opacity = '0.7';
                tagItem.style.borderWidth = '1px';
            }
            
            tagItem.addEventListener('click', (e) => {
                e.stopPropagation();
                const tagId = tagItem.dataset.tagId;
                const tagName = tagItem.dataset.tagName;
                const colorCode = tagItem.dataset.colorCode || '#444444';
                
                if (this.selectedTags.has(tagId)) {
                    this.selectedTags.delete(tagId);
                    tagItem.style.opacity = '0.7';
                    tagItem.style.borderWidth = '1px';
                } else {
                    this.selectedTags.set(tagId, { name: tagName, colorCode: colorCode });
                    tagItem.style.opacity = '1';
                    tagItem.style.borderWidth = '2px';
                }
                this.updateModalSelectedTags();
            });
        });
    }
    
    initCategoryCollapse() {
        document.querySelectorAll('.tag-category-header').forEach(header => {
            header.addEventListener('click', (e) => {
                e.stopPropagation();
                const category = header.closest('.tag-category');
                category.classList.toggle('collapsed');
            });
        });
    }
    
    openModal() {
        this.updateModalSelectedTags();
        if (this.modal) {
            this.modal.style.display = 'flex';
        }
    }
    
    closeModal() {
        if (this.modal) {
            this.modal.style.display = 'none';
        }
    }
    
    updateModalSelectedTags() {
        if (!this.modalSelectedTagsDiv) return;
        
        this.modalSelectedTagsDiv.innerHTML = '<span style="color: #888888; font-size: 12px;">Выбранные теги:</span>';
        
        this.selectedTags.forEach((tagData, id) => {
            const tagSpan = document.createElement('span');
            tagSpan.className = 'selected-tag-item';
            tagSpan.style.borderColor = tagData.colorCode;
            tagSpan.style.backgroundColor = `${tagData.colorCode}20`;
            tagSpan.innerHTML = tagData.name;
            tagSpan.addEventListener('click', (e) => {
                e.stopPropagation();
                const tagIdToRemove = id;
                this.selectedTags.delete(tagIdToRemove);
                this.updateModalSelectedTags();
                
                document.querySelectorAll('.tag-item').forEach(item => {
                    if (item.dataset.tagId === tagIdToRemove) {
                        item.style.opacity = '0.7';
                        item.style.borderWidth = '1px';
                    }
                });
            });
            this.modalSelectedTagsDiv.appendChild(tagSpan);
        });
    }
    
    applyTags() {
        this.updatePostTagsDisplay();
        this.closeModal();
    }
    
    updatePostTagsDisplay() {
        if (!this.postTagsContainer || !this.selectedTagsListDiv) return;
        
        if (this.selectedTags.size > 0) {
            this.postTagsContainer.style.display = 'flex';
            this.selectedTagsListDiv.innerHTML = '';
            
            this.selectedTags.forEach((tagData, id) => {
                const tagSpan = document.createElement('span');
                tagSpan.className = 'post-tag';
                tagSpan.style.borderColor = tagData.colorCode;
                tagSpan.style.backgroundColor = `${tagData.colorCode}20`;
                tagSpan.innerHTML = tagData.name;
                tagSpan.style.cursor = 'pointer';
                
                tagSpan.addEventListener('click', () => {
                    this.selectedTags.delete(id);
                    this.updatePostTagsDisplay();
                    this.updateModalSelectedTags();
                    
                    document.querySelectorAll('.tag-item').forEach(item => {
                        if (item.dataset.tagId === id) {
                            item.style.opacity = '0.7';
                            item.style.borderWidth = '1px';
                        }
                    });
                });
                
                this.selectedTagsListDiv.appendChild(tagSpan);
            });
            
            if (this.selectedTagsInput) {
                this.selectedTagsInput.value = Array.from(this.selectedTags.keys()).join(',');
            }
        } else {
            this.postTagsContainer.style.display = 'none';
            if (this.selectedTagsInput) {
                this.selectedTagsInput.value = '';
            }
        }
    }
}

document.addEventListener('DOMContentLoaded', () => {
    if (document.getElementById('select-tags-btn')) {
        new TagSelector();
    }
});