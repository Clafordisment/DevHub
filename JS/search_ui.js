
// UI для поиска с выпадающим меню

class SearchUI {
    constructor() {
        this.searchInput = document.getElementById('search-input');
        this.searchContainer = document.querySelector('.search-container');
        this.dropdown = null;
        this.selectedTags = new Map();
        this.tagSelector = null;
        this.init();
    }

    init() {
        if (!this.searchInput) return;
        this.createDropdown();

        this.searchInput.addEventListener('focus', () => this.showDropdown());
        this.searchInput.addEventListener('blur', (e) => {
            setTimeout(() => {
                if (this.dropdown && !this.dropdown.contains(document.activeElement)) {
                    this.hideDropdown();
                }
            }, 150);
        });
        this.searchInput.addEventListener('keyup', (e) => {
            if (e.key === 'Enter') {
                this.performSearch();
            }
        });
    }

    createDropdown() {
        this.dropdown = document.createElement('div');
        this.dropdown.className = 'search-dropdown';
        this.dropdown.innerHTML = `
            <div class="search-dropdown-header">
                <span>Фильтровать по:</span>
            </div>
            <div class="search-filter-group">
                <div class="search-filter-item">
                    <label>Дата от:</label>
                    <input type="date" id="filter-date-from" class="search-filter-input">
                </div>
                <div class="search-filter-item">
                    <label>Дата до:</label>
                    <input type="date" id="filter-date-to" class="search-filter-input">
                </div>
                <div class="search-filter-item">
                    <label>Рейтинг (мин):</label>
                    <select id="filter-min-rating" class="search-filter-select">
                        <option value="0">Любой</option>
                        <option value="1">1+</option>
                        <option value="2">2+</option>
                        <option value="3">3+</option>
                        <option value="4">4+</option>
                        <option value="5">5</option>
                    </select>
                </div>
                <div class="search-filter-item">
                    <label>Комментариев (мин):</label>
                    <input type="number" id="filter-min-comments" class="search-filter-input" min="0" value="0">
                </div>
            </div>
            
            <hr class="search-dropdown-divider">
            
            <div class="search-dropdown-header">
                <span>Сортировать по:</span>
            </div>
            <div class="search-sort-group">
                <div class="search-sort-buttons">
                    <button class="search-sort-btn" data-sort="date">Дате</button>
                    <button class="search-sort-btn" data-sort="rating">Рейтингу</button>
                    <button class="search-sort-btn" data-sort="comments">Комментариям</button>
                </div>
                <div class="search-order-buttons">
                    <button class="search-order-btn" data-order="desc">По убыванию</button>
                    <button class="search-order-btn" data-order="asc">По возрастанию</button>
                </div>
            </div>
            
            <hr class="search-dropdown-divider">
            
            <div class="search-tags-section">
                <button type="button" class="search-tags-btn" id="search-select-tags">🎯 Выбрать теги</button>
                <div id="search-selected-tags" class="search-selected-tags"></div>
            </div>
            
            <div class="search-dropdown-footer">
                <button type="button" class="search-reset-btn" id="search-reset-btn">Сбросить</button>
                <button type="button" class="search-submit-btn" id="search-submit-btn">Найти</button>
            </div>
        `;

        this.searchContainer.appendChild(this.dropdown);
        this.initDropdownHandlers();
        this.initTagSelectorForSearch();
    }

    initTagSelectorForSearch() {
        const modal = document.createElement('div');
        modal.id = 'search-tags-modal';
        modal.className = 'modal-overlay';
        modal.innerHTML = `
            <div class="modal-content">
                <div class="modal-header">
                    <h3>Выбор тегов для поиска</h3>
                    <button class="modal-close search-modal-close">&times;</button>
                </div>
                <div class="selected-tags-area" id="search-modal-selected-tags">
                    <span style="color: #888888; font-size: 12px;">Выбранные теги:</span>
                </div>
                <div class="modal-body" id="search-modal-tags-list">
                    <!-- Сюда будут загружены теги -->
                </div>
                <div class="modal-footer">
                    <button id="search-apply-tags-btn">Применить</button>
                </div>
            </div>
        `;
        document.body.appendChild(modal);

        this.loadTagsToModal();

        const closeBtn = modal.querySelector('.search-modal-close');
        closeBtn.addEventListener('click', () => {
            modal.style.display = 'none';
        });

        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                modal.style.display = 'none';
            }
        });

        const selectTagsBtn = document.getElementById('search-select-tags');
        if (selectTagsBtn) {
            selectTagsBtn.addEventListener('click', () => {
                this.updateSearchModalSelectedTags();
                modal.style.display = 'flex';
            });
        }

        const applyBtn = document.getElementById('search-apply-tags-btn');
        if (applyBtn) {
            applyBtn.addEventListener('click', () => {
                this.updateSelectedTagsFromModal();
                modal.style.display = 'none';
                this.updateSearchSelectedTagsDisplay();
            });
        }
    }

    loadTagsToModal() {
        fetch('ajax/get_tags.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.renderTagsInModal(data.tags);
                }
            })
            .catch(error => console.error('Ошибка загрузки тегов:', error));
    }

    renderTagsInModal(tags) {
        const container = document.getElementById('search-modal-tags-list');
        if (!container) return;

        let html = '';
        let currentCategory = null;

        tags.forEach(tag => {
            if (currentCategory !== tag.category_name) {
                if (currentCategory !== null) {
                    html += `</div></div>`;
                }
                html += `
                    <div class="tag-category" data-category-id="${tag.id_catg}">
                        <div class="tag-category-header">
                            <h4 style="color: ${tag.color_code};">${this.escapeHtml(tag.category_name)}</h4>
                            <span class="collapse-icon">▼</span>
                        </div>
                        <div class="tag-list">
                `;
                currentCategory = tag.category_name;
            }
            const isChecked = this.selectedTags.has(tag.id_t.toString());
            html += `
                <span class="tag-item search-tag-item" 
                    data-tag-id="${tag.id_t}" 
                    data-tag-name="${this.escapeHtml(tag.name)}" 
                    data-color-code="${tag.color_code}"
                    style="border-color: ${tag.color_code}; ${isChecked ? 'opacity: 1; border-width: 2px;' : 'opacity: 0.7; border-width: 1px;'}">
                    ${this.escapeHtml(tag.name)}
                </span>
            `;
        });
        html += `</div></div>`;
        container.innerHTML = html;

        container.querySelectorAll('.search-tag-item').forEach(item => {
            item.addEventListener('click', (e) => {
                e.stopPropagation();
                const tagId = item.dataset.tagId;
                if (this.selectedTags.has(tagId)) {
                    this.selectedTags.delete(tagId);
                    item.style.opacity = '0.7';
                    item.style.borderWidth = '1px';
                } else {
                    this.selectedTags.set(tagId, {
                        name: item.dataset.tagName,
                        colorCode: item.dataset.colorCode
                    });
                    item.style.opacity = '1';
                    item.style.borderWidth = '2px';
                }
                this.updateSearchModalSelectedTags();
            });
        });

        container.querySelectorAll('.tag-category-header').forEach(header => {
            header.addEventListener('click', () => {
                const category = header.closest('.tag-category');
                category.classList.toggle('collapsed');
            });
        });
    }

    updateSearchModalSelectedTags() {
        const container = document.getElementById('search-modal-selected-tags');
        if (!container) return;

        container.innerHTML = '<span style="color: #888888; font-size: 12px;">Выбранные теги:</span>';

        this.selectedTags.forEach((tagData, id) => {
            const tagSpan = document.createElement('span');
            tagSpan.className = 'selected-tag-item';
            tagSpan.style.borderColor = tagData.colorCode;
            tagSpan.style.backgroundColor = `${tagData.colorCode}20`;
            tagSpan.innerHTML = tagData.name;
            tagSpan.addEventListener('click', () => {
                this.selectedTags.delete(id);
                this.updateSearchModalSelectedTags();
                this.updateSearchSelectedTagsDisplay();
                document.querySelectorAll('.search-tag-item').forEach(item => {
                    if (item.dataset.tagId === id) {
                        item.style.opacity = '0.7';
                        item.style.borderWidth = '1px';
                    }
                });
            });
            container.appendChild(tagSpan);
        });
    }

    updateSelectedTagsFromModal() {
        this.updateSearchSelectedTagsDisplay();
    }

    updateSearchSelectedTagsDisplay() {
        const container = document.getElementById('search-selected-tags');
        if (!container) return;

        container.innerHTML = '';
        this.selectedTags.forEach((tagData, id) => {
            const tagSpan = document.createElement('span');
            tagSpan.className = 'search-tag-item';
            tagSpan.style.borderColor = tagData.colorCode;
            tagSpan.style.backgroundColor = `${tagData.colorCode}20`;
            tagSpan.innerHTML = `${tagData.name} <span class="remove-tag" data-tag-id="${id}">×</span>`;
            tagSpan.querySelector('.remove-tag').addEventListener('click', (e) => {
                e.stopPropagation();
                this.selectedTags.delete(id);
                this.updateSearchSelectedTagsDisplay();
                this.updateSearchModalSelectedTags();

                document.querySelectorAll('.search-tag-item').forEach(item => {
                    if (item.dataset.tagId === id) {
                        item.style.opacity = '0.7';
                        item.style.borderWidth = '1px';
                    }
                });
            });
            container.appendChild(tagSpan);
        });
    }

    initDropdownHandlers() {
        // Кнопки сортировки
        document.querySelectorAll('.search-sort-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                document.querySelectorAll('.search-sort-btn').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
            });
        });

        // Кнопки порядка сортировки
        document.querySelectorAll('.search-order-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                document.querySelectorAll('.search-order-btn').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
            });
        });

        // Кнопка сброса
        const resetBtn = document.getElementById('search-reset-btn');
        if (resetBtn) {
            resetBtn.addEventListener('click', () => this.resetFilters());
        }

        // Кнопка поиска
        const submitBtn = document.getElementById('search-submit-btn');
        if (submitBtn) {
            submitBtn.addEventListener('click', () => this.performSearch());
        }
    }

    resetFilters() {
        const dateFrom = document.getElementById('filter-date-from');
        const dateTo = document.getElementById('filter-date-to');
        const minRating = document.getElementById('filter-min-rating');
        const minComments = document.getElementById('filter-min-comments');

        if (dateFrom) dateFrom.value = '';
        if (dateTo) dateTo.value = '';
        if (minRating) minRating.value = '0';
        if (minComments) minComments.value = '0';

        document.querySelectorAll('.search-sort-btn').forEach(btn => btn.classList.remove('active'));
        document.querySelectorAll('.search-order-btn').forEach(btn => btn.classList.remove('active'));

        this.selectedTags.clear();
        this.updateSearchSelectedTagsDisplay();
        this.updateSearchModalSelectedTags();

        this.performSearch();
    }

    getFilters() {
        const activeSortBtn = document.querySelector('.search-sort-btn.active');
        const activeOrderBtn = document.querySelector('.search-order-btn.active');

        return {
            date_from: document.getElementById('filter-date-from')?.value || '',
            date_to: document.getElementById('filter-date-to')?.value || '',
            min_rating: parseFloat(document.getElementById('filter-min-rating')?.value || 0),
            min_comments: parseInt(document.getElementById('filter-min-comments')?.value || 0),
            sort_by: activeSortBtn ? activeSortBtn.dataset.sort : 'date',
            sort_order: activeOrderBtn ? activeOrderBtn.dataset.order : 'desc',
            tags: Array.from(this.selectedTags.keys())
        };
    }

    getSearchQuery() {
        return this.searchInput.value.trim();
    }

    async performSearch() {
        const query = this.getSearchQuery();
        const filters = this.getFilters();

        this.showLoading();

        try {
            const response = await fetch('ajax/search_posts.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    query: query,
                    filters: filters
                })
            });
            const data = await response.json();

            if (data.success) {
                this.updateResults(data.posts);
            } else {
                console.error('Search error:', data.error);
            }
        } catch (error) {
            console.error('Search error:', error);
        } finally {
            this.hideLoading();
            this.hideDropdown();
        }
    }

    updateResults(posts) {
        const postsWrapper = document.querySelector('.posts-wrapper');
        if (!postsWrapper) return;

        if (!posts || !Array.isArray(posts) || posts.length === 0) {
            postsWrapper.innerHTML = '<p class="no-results">По вашему запросу ничего не найдено</p>';
            return;
        }

        let html = '<div class="posts-grid">';
        posts.forEach(post => {
            html += `
                <a class="post-card-link" href="post.php?id=${post.id_p}">
                    <div class="post-card ${!post.ownPrev ? 'no-image' : ''}">
                        ${post.ownPrev ? `<img class="post-card-image" src="${this.escapeHtml(post.ownPrev)}" alt="Изображение поста">` : ''}
                        <div class="post-card-content">
                            <div class="post-card-title">${this.escapeHtml(post.title)}</div>
                            <div class="post-card-author">${this.escapeHtml(post.author_name)}</div>
                            ${post.tags && post.tags.length > 0 ? `
                                <div class="post-card-tags">
                                    ${post.tags.map(tag => `<span class="post-card-tag" style="border-color: ${tag.color_code}; color: ${tag.color_code};">${this.escapeHtml(tag.name)}</span>`).join('')}
                                </div>
                            ` : ''}
                        </div>
                    </div>
                </a>
            `;
        });
        html += '</div>';

        postsWrapper.innerHTML = html;
    }

    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    showDropdown() {
        if (this.dropdown) {
            this.dropdown.classList.add('show');
        }
    }

    hideDropdown() {
        if (this.dropdown) {
            this.dropdown.classList.remove('show');
        }
    }

    showLoading() {
        const submitBtn = document.getElementById('search-submit-btn');
        if (submitBtn) {
            submitBtn.textContent = 'Поиск...';
            submitBtn.disabled = true;
        }
    }

    hideLoading() {
        const submitBtn = document.getElementById('search-submit-btn');
        if (submitBtn) {
            submitBtn.textContent = 'Найти';
            submitBtn.disabled = false;
        }
    }
}

document.addEventListener('DOMContentLoaded', () => {
    new SearchUI();
});