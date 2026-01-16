// მობილური მენიუ
document.addEventListener('DOMContentLoaded', function() {
    const mobileMenuToggle = document.getElementById('mobileMenuToggle');
    const mainNav = document.querySelector('.main-nav');
    
    if (mobileMenuToggle) {
        mobileMenuToggle.addEventListener('click', function() {
            mainNav.style.display = mainNav.style.display === 'block' ? 'none' : 'block';
        });
    }
    
    // Smooth scroll with offset
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            const href = this.getAttribute('href');
            if (href !== '#' && href.includes('#')) {
                e.preventDefault();
                const targetId = href.split('#')[1];
                const target = document.getElementById(targetId);
                if (target) {
                    const headerHeight = 100;
                    const targetPosition = target.offsetTop - headerHeight;
                    window.scrollTo({
                        top: targetPosition,
                        behavior: 'smooth'
                    });
                }
            }
        });
    });
    
    // შეტყობინებების ავტომატური დახურვა
    const messages = document.querySelectorAll('.success-message, .error-message');
    messages.forEach(msg => {
        setTimeout(() => {
            msg.style.animation = 'fadeOut 0.5s';
            setTimeout(() => msg.remove(), 500);
        }, 5000);
    });
    
    // სურათის წინასწარი ხილვა
    const imageInputs = document.querySelectorAll('input[type="file"][accept*="image"]');
    imageInputs.forEach(input => {
        input.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                const preview = document.getElementById(this.dataset.preview);
                
                reader.onload = function(e) {
                    if (preview) {
                        preview.src = e.target.result;
                        preview.style.display = 'block';
                    }
                };
                
                reader.readAsDataURL(file);
            }
        });
    });
    
    // ფორმის ვალიდაცია
    const forms = document.querySelectorAll('form[data-validate]');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const requiredFields = form.querySelectorAll('[required]');
            let isValid = true;
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    isValid = false;
                    field.style.borderColor = 'red';
                } else {
                    field.style.borderColor = '';
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                alert('გთხოვთ შეავსოთ ყველა სავალდებულო ველი');
            }
        });
    });
    
    // რეიტინგის სისტემა
    const ratingStars = document.querySelectorAll('.rating-star');
    ratingStars.forEach((star, index) => {
        star.addEventListener('click', function() {
            const rating = index + 1;
            const articleId = this.dataset.articleId;
            const newsId = this.dataset.newsId;
            
            submitRating(rating, articleId, newsId);
        });
        
        star.addEventListener('mouseenter', function() {
            highlightStars(index);
        });
    });
    
    const ratingContainer = document.querySelector('.rating-stars');
    if (ratingContainer) {
        ratingContainer.addEventListener('mouseleave', function() {
            const currentRating = parseInt(this.dataset.currentRating) || 0;
            highlightStars(currentRating - 1);
        });
    }
    
    // Smooth scroll
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            const href = this.getAttribute('href');
            if (href !== '#') {
                e.preventDefault();
                const target = document.querySelector(href);
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth'
                    });
                }
            }
        });
    });
    
    // წაკითხული მონიშვნა
    markAsRead();
});

// რეიტინგის გაგზავნა
function submitRating(rating, articleId, newsId) {
    const formData = new FormData();
    formData.append('rating', rating);
    
    if (articleId) {
        formData.append('article_id', articleId);
    }
    if (newsId) {
        formData.append('news_id', newsId);
    }
    
    fetch('rate.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('გმადლობთ შეფასებისთვის!', 'success');
            updateRatingDisplay(data.average, data.count);
        } else {
            showNotification(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('შეცდომა მოხდა', 'error');
    });
}

// ვარსკვლავების მონიშვნა
function highlightStars(index) {
    const stars = document.querySelectorAll('.rating-star');
    stars.forEach((star, i) => {
        if (i <= index) {
            star.classList.add('active');
        } else {
            star.classList.remove('active');
        }
    });
}

// რეიტინგის განახლება
function updateRatingDisplay(average, count) {
    const avgElement = document.getElementById('avgRating');
    const countElement = document.getElementById('ratingCount');
    
    if (avgElement) {
        avgElement.textContent = average.toFixed(1);
    }
    if (countElement) {
        countElement.textContent = count;
    }
}

// შეტყობინების ჩვენება
function showNotification(message, type = 'success') {
    const notification = document.createElement('div');
    notification.className = type === 'success' ? 'success-message' : 'error-message';
    notification.textContent = message;
    notification.style.position = 'fixed';
    notification.style.top = '20px';
    notification.style.right = '20px';
    notification.style.zIndex = '10000';
    notification.style.padding = '1rem 2rem';
    notification.style.borderRadius = '10px';
    notification.style.boxShadow = '0 5px 20px rgba(0,0,0,0.2)';
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.style.animation = 'fadeOut 0.5s';
        setTimeout(() => notification.remove(), 500);
    }, 3000);
}

// წაკითხვის აღრიცხვა
function markAsRead() {
    const articleId = document.getElementById('articleId');
    const newsId = document.getElementById('newsId');
    
    if (articleId || newsId) {
        const formData = new FormData();
        if (articleId) formData.append('article_id', articleId.value);
        if (newsId) formData.append('news_id', newsId.value);
        
        fetch('mark_read.php', {
            method: 'POST',
            body: formData
        });
    }
}

// ძიების autocomplete
const searchInput = document.querySelector('.search-input-wrapper input');
if (searchInput) {
    let searchTimeout;
    
    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        const query = this.value;
        
        if (query.length < 2) {
            hideSearchSuggestions();
            return;
        }
        
        searchTimeout = setTimeout(() => {
            fetchSearchSuggestions(query);
        }, 300);
    });
}

function fetchSearchSuggestions(query) {
    fetch(`search_suggestions.php?q=${encodeURIComponent(query)}`)
        .then(response => response.json())
        .then(data => {
            showSearchSuggestions(data);
        })
        .catch(error => console.error('Error:', error));
}

function showSearchSuggestions(suggestions) {
    let suggestionsBox = document.getElementById('searchSuggestions');
    
    if (!suggestionsBox) {
        suggestionsBox = document.createElement('div');
        suggestionsBox.id = 'searchSuggestions';
        suggestionsBox.className = 'search-suggestions';
        document.querySelector('.search-box').appendChild(suggestionsBox);
    }
    
    if (suggestions.length === 0) {
        hideSearchSuggestions();
        return;
    }
    
    suggestionsBox.innerHTML = suggestions.map(item => 
        `<div class="suggestion-item" onclick="window.location.href='${item.url}'">
            <i class="fas fa-${item.icon}"></i>
            <span>${item.title}</span>
        </div>`
    ).join('');
    
    suggestionsBox.style.display = 'block';
}

function hideSearchSuggestions() {
    const suggestionsBox = document.getElementById('searchSuggestions');
    if (suggestionsBox) {
        suggestionsBox.style.display = 'none';
    }
}

// fadeOut ანიმაცია
const style = document.createElement('style');
style.textContent = `
    @keyframes fadeOut {
        from {
            opacity: 1;
        }
        to {
            opacity: 0;
        }
    }
    
    .search-suggestions {
        position: absolute;
        background: white;
        width: 100%;
        max-height: 400px;
        overflow-y: auto;
        border-radius: 10px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        margin-top: 10px;
        z-index: 1000;
    }
    
    .suggestion-item {
        padding: 1rem;
        display: flex;
        align-items: center;
        gap: 10px;
        cursor: pointer;
        border-bottom: 1px solid #eee;
        transition: background 0.3s;
    }
    
    .suggestion-item:hover {
        background: #f8f9fa;
    }
    
    .suggestion-item i {
        color: #667eea;
    }
    
    .rating-star {
        cursor: pointer;
        color: #ddd;
        font-size: 1.5rem;
        transition: color 0.3s;
    }
    
    .rating-star:hover,
    .rating-star.active {
        color: #fbbf24;
    }
`;
document.head.appendChild(style);