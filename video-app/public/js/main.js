// 页面加载完成后执行
document.addEventListener('DOMContentLoaded', function() {

    // 搜索框回车提交
    const searchInput = document.querySelector('.search-box input');
    if (searchInput) {
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                this.form.submit();
            }
        });
    }

    // 视频卡片悬停效果
    const videoCards = document.querySelectorAll('.video-card');
    videoCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.classList.add('hover');
        });
        card.addEventListener('mouseleave', function() {
            this.classList.remove('hover');
        });
    });

    // 分类标签点击滚动到顶部
    const categoryTags = document.querySelectorAll('.category-tag');
    categoryTags.forEach(tag => {
        tag.addEventListener('click', function(e) {
            if (this.classList.contains('active')) {
                e.preventDefault();
            }
        });
    });

    // 自动加载下一页（可选功能）
    let isLoading = false;
    const pagination = document.querySelector('.pagination');
    const nextPageLink = document.querySelector('.pagination-link[href*="page="]');

    if (nextPageLink && pagination) {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting && !isLoading) {
                    isLoading = true;
                    // 可以在这里实现自动加载下一页
                    isLoading = false;
                }
            });
        }, { threshold: 0.1 });

        observer.observe(pagination);
    }

    // 播放历史记录管理
    function getPlayHistory() {
        try {
            return JSON.parse(localStorage.getItem('playHistory') || '{}');
        } catch (e) {
            return {};
        }
    }

    function savePlayHistory(history) {
        try {
            localStorage.setItem('playHistory', JSON.stringify(history));
        } catch (e) {
            console.error('Failed to save play history:', e);
        }
    }

    function addToPlayHistory(videoId, videoName, episode, episodeName, url) {
        const history = getPlayHistory();
        history[videoId] = {
            name: videoName,
            episode: episode,
            episodeName: episodeName,
            url: url,
            timestamp: Date.now()
        };
        savePlayHistory(history);
    }

    // 显示播放历史（如果有历史记录区域）
    function displayPlayHistory() {
        const history = getPlayHistory();
        const historyContainer = document.getElementById('play-history');

        if (historyContainer && Object.keys(history).length > 0) {
            const historyArray = Object.entries(history)
                .sort((a, b) => b[1].timestamp - a[1].timestamp)
                .slice(0, 10); // 只显示最近10条

            let html = '<h3 class="section-title">最近观看</h3><div class="video-grid">';
            historyArray.forEach(([videoId, data]) => {
                html += `
                    <article class="video-card">
                        <a href="${data.url}" class="video-card-link">
                            <h3 class="video-title">${data.name}</h3>
                            <div class="video-meta">
                                <span class="video-note">${data.episodeName}</span>
                            </div>
                        </a>
                    </article>
                `;
            });
            html += '</div>';
            historyContainer.innerHTML = html;
        }
    }

    // 如果存在历史记录区域，则显示
    displayPlayHistory();

    // 搜索关键词高亮
    function highlightSearchKeyword() {
        const urlParams = new URLSearchParams(window.location.search);
        const keyword = urlParams.get('wd');

        if (keyword) {
            const videoTitles = document.querySelectorAll('.video-title');
            const regex = new RegExp(`(${keyword})`, 'gi');

            videoTitles.forEach(title => {
                if (title.textContent.toLowerCase().includes(keyword.toLowerCase())) {
                    title.innerHTML = title.textContent.replace(regex, '<mark>$1</mark>');
                }
            });
        }
    }

    highlightSearchKeyword();

    // 返回顶部按钮
    const backToTopButton = document.createElement('button');
    backToTopButton.innerHTML = '↑';
    backToTopButton.className = 'back-to-top';
    backToTopButton.style.cssText = `
        position: fixed;
        bottom: 20px;
        right: 20px;
        width: 50px;
        height: 50px;
        background-color: #238636;
        color: white;
        border: none;
        border-radius: 50%;
        cursor: pointer;
        font-size: 20px;
        display: none;
        z-index: 999;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
    `;

    document.body.appendChild(backToTopButton);

    window.addEventListener('scroll', function() {
        if (window.scrollY > 300) {
            backToTopButton.style.display = 'block';
        } else {
            backToTopButton.style.display = 'none';
        }
    });

    backToTopButton.addEventListener('click', function() {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });

    // 播放器自适应
    function adjustPlayerSize() {
        const playerWrapper = document.querySelector('.player-wrapper');
        if (playerWrapper) {
            const maxWidth = Math.min(window.innerWidth - 40, 1280);
            const height = maxWidth * 9 / 16;
            playerWrapper.style.maxWidth = maxWidth + 'px';
            playerWrapper.style.height = height + 'px';
        }
    }

    window.addEventListener('resize', adjustPlayerSize);
    adjustPlayerSize();

    // 键盘快捷键
    document.addEventListener('keydown', function(e) {
        // 播放器页面的快捷键
        if (document.querySelector('.player-page')) {
            const episodeButtons = document.querySelectorAll('.episode-btn');
            const currentEpisode = document.querySelector('.episode-btn.active');
            const currentIndex = Array.from(episodeButtons).indexOf(currentEpisode);

            switch(e.key) {
                case 'ArrowLeft':
                    if (currentIndex > 0) {
                        episodeButtons[currentIndex - 1].click();
                    }
                    e.preventDefault();
                    break;
                case 'ArrowRight':
                    if (currentIndex < episodeButtons.length - 1) {
                        episodeButtons[currentIndex + 1].click();
                    }
                    e.preventDefault();
                    break;
                case 'ArrowUp':
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                    e.preventDefault();
                    break;
                case 'ArrowDown':
                    window.scrollTo({
                        top: document.body.scrollHeight,
                        behavior: 'smooth'
                    });
                    e.preventDefault();
                    break;
            }
        }
    });

    // 图片懒加载优化
    if ('IntersectionObserver' in window) {
        const lazyImages = document.querySelectorAll('img[loading="lazy"]');

        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src || img.src;
                    observer.unobserve(img);
                }
            });
        });

        lazyImages.forEach(img => {
            imageObserver.observe(img);
        });
    }

    console.log('Video App initialized successfully!');
});
