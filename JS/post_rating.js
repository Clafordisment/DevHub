//Обработчик звездного рейтинга для постов

document.addEventListener('DOMContentLoaded', function() {
    const stars = document.querySelectorAll('.star');
    const ratingContainer = document.querySelector('.rating-container');
    const ratingCurrent = document.querySelector('.rating-current');
    const ratingVotes = document.querySelector('.rating-votes');
    let currentRating = parseFloat(ratingContainer.dataset.userRating) || 0;

    if (currentRating > 0) {
        highlightStars(currentRating);
    }

    stars.forEach(star => {
        star.addEventListener('mouseenter', function() {
            const value = parseInt(this.dataset.value);
            highlightStars(value);
        });

        star.addEventListener('mousemove', function(e) {
            const rect = this.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const width = rect.width;
            const value = parseInt(this.dataset.value);
            if (x < width / 2) {
                highlightStars(value - 0.5);
            } else {
                highlightStars(value);
            }
        });

        star.addEventListener('click', function(e) {
            const rect = this.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const width = rect.width;
            const value = parseInt(this.dataset.value);
            let rating = (x < width / 2) ? value - 0.5 : value;
            submitRating(rating);
        });

        star.addEventListener('mouseleave', function() {
            highlightStars(currentRating);
        });
    });

    function highlightStars(rating) {
        stars.forEach(star => {
            const starValue = parseInt(star.dataset.value);
            star.textContent = '☆';
            star.classList.remove('active');
            if (rating >= starValue) {
                star.textContent = '★';
                star.classList.add('active');
            } else if (rating > starValue - 1 && rating < starValue) {
                star.textContent = '★';
                star.classList.add('active');
            }
        });
    }

    async function submitRating(rating) {
        const postId = ratingContainer.dataset.postId;
        try {
            const response = await fetch('ajax/rate_post.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ post_id: postId, rating: rating })
            });
            const data = await response.json();
            if (data.success) {
                currentRating = rating;
                highlightStars(rating);
                ratingCurrent.textContent = data.avg_rating;
                ratingVotes.textContent = `(${data.votes_count} голосов)`;
                showRatingMessage('Оценка сохранена!', 'ok');
            } else {
                showRatingMessage(data.error || 'Ошибка', 'err');
            }
        } catch (error) {
            showRatingMessage('Ошибка соединения', 'err');
        }
    }

    function showRatingMessage(text, type) {
        const oldMsg = document.querySelector('.rating-message');
        if (oldMsg) oldMsg.remove();
        const messageDiv = document.createElement('div');
        messageDiv.className = `msg ${type} rating-message`;
        messageDiv.textContent = text;
        messageDiv.style.marginTop = '10px';
        ratingContainer.appendChild(messageDiv);
        setTimeout(() => messageDiv.remove(), 3000);
    }
});