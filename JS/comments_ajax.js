//JS файл для AJAX-обработки комментариев

class CommentSystem {
    constructor(postId, currentUserId) {
        this.postId = postId;
        this.currentUserId = currentUserId;
        this.commentsWrapper = document.querySelector('.comments-wrapper');
        this.commentForm = document.querySelector('.comment-form');
        this.commentInput = document.querySelector('.comment-input');
        this.submitButton = document.querySelector('.comment-submit');
        
        this.init();
    }
    
    init() {
        if (this.commentForm) {
            this.commentForm.addEventListener('submit', (e) => {
                e.preventDefault();
                this.submitComment();
            });
        }
        
        this.attachDeleteHandlers();
    }
    
    attachDeleteHandlers() {
        document.querySelectorAll('.delete-comment-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                const commentId = btn.dataset.commentId;
                this.deleteComment(commentId, btn);
            });
        });
    }
    
    async submitComment() {
        const content = this.commentInput.value.trim();
        
        if (!content) {
            this.showMessage('Введите комментарий', 'err');
            return;
        }
        
        this.setLoadingState(true);
        
        try {
            const response = await fetch('ajax/add_comment.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    post_id: this.postId,
                    content: content
                })
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.addCommentToDOM(data.comment);
                this.commentInput.value = '';
                this.showMessage('Комментарий добавлен', 'ok');
            } else {
                this.showMessage(data.error || 'Ошибка при добавлении комментария', 'err');
            }
        } catch (error) {
            console.error('Error:', error);
            this.showMessage('Ошибка соединения с сервером', 'err');
        } finally {
            this.setLoadingState(false);
        }
    }
    
    async deleteComment(commentId, buttonElement) {
        if (!confirm('Вы точно хотите удалить этот комментарий?')) {
            return;
        }
        
        const commentCard = buttonElement.closest('.comment-card');
        
        try {
            const response = await fetch('ajax/delete_comment.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    comment_id: commentId
                })
            });
            
            const data = await response.json();
            
            if (data.success) {
                commentCard.style.transition = 'opacity 0.3s ease';
                commentCard.style.opacity = '0';
                
                setTimeout(() => {
                    commentCard.remove();
                    this.showMessage('Комментарий удален', 'ok');
                    
                    if (this.commentsWrapper.children.length === 0) {
                        this.commentsWrapper.innerHTML = '<p class="no-comments">Пока нет комментариев. Будьте первым!</p>';
                    }
                }, 300);
            } else {
                this.showMessage(data.error || 'Ошибка при удалении комментария', 'err');
            }
        } catch (error) {
            console.error('Error:', error);
            this.showMessage('Ошибка соединения с сервером', 'err');
        }
    }
    
    addCommentToDOM(comment) {
        const noComments = this.commentsWrapper.querySelector('.no-comments');
        if (noComments) {
            noComments.remove();
        }
        
        const commentHTML = this.createCommentHTML(comment);
        this.commentsWrapper.insertAdjacentHTML('afterbegin', commentHTML);
        
        const newComment = this.commentsWrapper.firstElementChild;
        const deleteBtn = newComment.querySelector('.delete-comment-btn');
        if (deleteBtn) {
            deleteBtn.addEventListener('click', (e) => {
                e.preventDefault();
                this.deleteComment(comment.id, deleteBtn);
            });
        }
        
        newComment.style.opacity = '0';
        newComment.style.transform = 'translateY(-20px)';
        
        setTimeout(() => {
            newComment.style.transition = 'all 0.3s ease';
            newComment.style.opacity = '1';
            newComment.style.transform = 'translateY(0)';
        }, 10);
    }
    
    createCommentHTML(comment) {
        const isAuthor = this.currentUserId == comment.author_id;
        const date = comment.date || new Date().toLocaleString('ru-RU', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
        
        return `
            <div class="comment-card" data-comment-id="${comment.id}">
                <div class="comment-header">
                    <span class="comment-author">${this.escapeHtml(comment.author)}</span>
                    <span class="comment-date">${date}</span>
                </div>
                <p class="comment-content">${this.escapeHtml(comment.content).replace(/\n/g, '<br>')}</p>
                ${isAuthor ? `
                    <div class="comment-actions">
                        <button class="comment-delete-btn delete-comment-btn" data-comment-id="${comment.id}">
                            Удалить
                        </button>
                    </div>
                ` : ''}
            </div>
        `;
    }
    
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    setLoadingState(isLoading) {
        if (!this.submitButton) return;
        
        this.submitButton.disabled = isLoading;
        this.submitButton.textContent = isLoading ? 'Отправка...' : 'Отправить';
        this.commentInput.disabled = isLoading;
    }
    
    showMessage(text, type) {
        const oldMsg = document.querySelector('.ajax-message');
        if (oldMsg) {
            oldMsg.remove();
        }
        
        const messageDiv = document.createElement('div');
        messageDiv.className = `msg ${type} ajax-message`;
        messageDiv.textContent = text;
        messageDiv.style.position = 'fixed';
        messageDiv.style.top = '20px';
        messageDiv.style.right = '20px';
        messageDiv.style.zIndex = '1000';
        messageDiv.style.minWidth = '200px';
        messageDiv.style.animation = 'slideIn 0.3s ease';
        
        document.body.appendChild(messageDiv);
        
        setTimeout(() => {
            messageDiv.style.animation = 'slideOut 0.3s ease';
            setTimeout(() => messageDiv.remove(), 300);
        }, 3000);
    }
}

const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }
`;
document.head.appendChild(style);

document.addEventListener('DOMContentLoaded', () => {
    const postElement = document.querySelector('[data-post-id]');
    if (postElement) {
        new CommentSystem(
            postElement.dataset.postId,
            postElement.dataset.currentUserId
        );
    }
});