

/**
 * 影视视频网站主JavaScript文件
 * 包含播放器、搜索、交互等功能
 */

(function($) {
    "use strict";
    
    // ==================
    // 全局配置
    // ==================
    const CONFIG = {
        playerUrl: $('meta[name="player-url"]').attr('content') || '/play/?url=',
        baseUrl: $('meta[name="base-url"]').attr('content') || '/',
        debug: $('meta[name="debug"]').attr('content') === 'true'
    };
    
    // 工具函数
    const UTILS = {
        // 防抖函数
        debounce: function(func, wait, immediate) {
            let timeout;
            return function() {
                let context = this, args = arguments;
                let later = function() {
                    timeout = null;
                    if (!immediate) func.apply(context, args);
                };
                let callNow = immediate && !timeout;
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
                if (callNow) func.apply(context, args);
            };
        },
        
        // 复制到剪贴板
        copyToClipboard: function(text) {
            if (navigator.clipboard) {
                navigator.clipboard.writeText(text).then(function() {
                    showToast('复制成功！', 'success');
                });
            } else {
                let textArea = document.createElement('textarea');
                textArea.value = text;
                textArea.style.position = 'fixed';
                document.body.appendChild(textArea);
                textArea.focus();
                textArea.select();
                
                try {
                    document.execCommand('copy');
                    showToast('复制成功！', 'success');
                } catch (err) {
                    showToast('复制失败，请手动复制', 'error');
                }
                
                document.body.removeChild(textArea);
            }
        },
        
        // 显示提示信息
        showToast: function(message, type = 'info') {
            let bgColor = {
                'success': '#28a745',
                'error': '#dc3545',
                'warning': '#ffc107',
                'info': '#17a2b8'
            }[type] || '#17a2b8';
            
            let toast = $('<div class="position-fixed" style="top: 20px; right: 20px; z-index: 10000; max-width: 300px;">')
                .append(`
                    <div class="toast show" role="alert">
                        <div class="toast-header" style="background-color: ${bgColor}; color: white;">
                            <strong class="mr-auto">${type === 'success' ? '成功' : type === 'error' ? '错误' : type === 'warning' ? '警告' : '提示'}</strong>
                            <button type="button" class="ml-2 mb-1 close text-light" data-dismiss="toast">&times;</button>
                        </div>
                        <div class="toast-body bg-white">${message}</div>
                    </div>
                `);
            
            toast.appendTo('body');
            
            // 自动消失
            setTimeout(() => {
                toast.find('.toast').removeClass('show').addClass('hide');
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }
    };
    
    window.showToast = UTILS.showToast;
    window.copyText = UTILS.copyToClipboard;
    
    // ==================
    // 搜索功能
    // ==================
    function initSearch() {
        // 搜索表单验证
        $('form[action*="search"]').on('submit', function(e) {
            let $keyword = $(this).find('input[name="q"]');
            let keyword = $keyword.val().trim();
            
            if (keyword.length < 2) {
                e.preventDefault();
                UTILS.showToast('请输入至少2个字符的关键词', 'warning');
                $keyword.focus();
                return false;
            }
            
            if (keyword.length > 50) {
                e.preventDefault();
                UTILS.showToast('关键词不能超过50个字符', 'warning');
                return false;
            }
        });
        
        // 热词搜索
        $('.hot-keyword').on('click', function(e) {
            e.preventDefault();
            let keyword = $(this).text().trim();
            $('input[name="q"]').val(keyword).focus();
            $(this).closest('form').submit();
        });
    }
    
    // ==================
    // M3U8播放器功能
    // ==================
    function initPlayer() {
        let $iframe = $('#m3u8Player');
        let $loader = $('#playerLoader');
        let $error = $('#playerError');
        let $currentTitle = $('.current-episode-title');
        
        // 播放集数
        $(document).on('click', '.episode-btn', function() {
            let url = $(this).data('url');
            let title = $(this).data('title');
            
            if (!url) {
                UTILS.showToast('播放链接无效', 'error');
                return;
            }
            
            // 显示加载状态
            $loader.show();
            $error.hide();
            $iframe.hide();
            
            // 设置当前播放标题
            $currentTitle.text(title || '正在播放...');
            
            // 移除激活状态
            $('.episode-btn').removeClass('active');
            $(this).addClass('active');
            
            // 构建播放URL
            let playUrl = CONFIG.playerUrl + encodeURIComponent(url);
            
            // 设置iframe源
            $iframe.attr('src', playUrl);
            
            // 监听iframe加载
            $iframe.off('load').on('load', function() {
                $loader.hide();
                $iframe.show();
                UTILS.showToast('开始播放: ' + title, 'success');
                
                // 滚动到播放器
                $('html, body').animate({
                    scrollTop: $('.player-container').offset().top - 20
                }, 500);
            });
            
            // 错误处理（通过定时检查）
            setTimeout(() => {
                if ($iframe.is(':hidden') && $loader.is(':visible')) {
                    $loader.hide();
                    $error.find('#errorMessage').text('播放器加载失败，请检查网络连接');
                    $error.show();
                    UTILS.showToast('播放器加载失败', 'error');
                }
            }, 10000); // 10秒超时
        });
        
        // 播放第一集按钮
        $('#playFirstEpisode').on('click', function() {
            let $firstEpisode = $('.episode-btn:first');
            if ($firstEpisode.length) {
                $firstEpisode.click();
            } else {
                UTILS.showToast('暂无可播放的集数', 'warning');
            }
        });
        
        // 播放列表全选
        $('#selectAllEpisodes').on('click', function() {
            $('.episode-btn').addClass('active');
        });
        
        // 清除选择
        $('#clearSelection').on('click', function() {
            $('.episode-btn').removeClass('active');
        });
        
        // 播放器错误重试
        $iframe.on('error', function() {
            $loader.hide();
            $error.find('#errorMessage').text('播放器加载出错，请稍后重试');
            $error.show();
            UTILS.showToast('播放器加载失败', 'error');
        });
    }
    
    // ==================
    // UI交互功能
    // ==================
    function initUI() {
        // 返回顶部按钮
        $(window).on('scroll', UTILS.debounce(function() {
            let scrollTop = $(window).scrollTop();
            let winHeight = $(window).height();
            
            if (scrollTop > winHeight / 2) {
                $('#backToTop').fadeIn(200);
            } else {
                $('#backToTop').fadeOut(200);
            }
        }, 100));
        
        $('#backToTop').on('click', function() {
            $('html, body').animate({ scrollTop: 0 }, 600);
        });
        
        // 视图切换（网格/列表）
        function switchView(view) {
            if (view === 'list') {
                $('#listView').show();
                $('#gridView').hide();
                $('#listViewToggle').addClass('active');
                $('#gridViewToggle').removeClass('active');
                localStorage.setItem('videoViewMode', 'list');
            } else {
                $('#gridView').show();
                $('#listView').hide();
                $('#gridViewToggle').addClass('active');
                $('#listViewToggle').removeClass('active');
                localStorage.setItem('videoViewMode', 'grid');
            }
        }
        
        $('#listViewToggle, #gridViewToggle').on('click', function() {
            if ($(this).attr('id') === 'listViewToggle') {
                switchView('list');
            } else {
                switchView('grid');
            }
        });
        
        // 恢复视图模式
        let savedView = localStorage.getItem('videoViewMode');
        if (savedView === 'list' && $('#listView').length) {
            switchView('list');
        }
        
        // 模态框分享功能
        window.copyShareUrl = function() {
            let url = $('#shareUrl').val();
            UTILS.copyToClipboard(url);
        };
        
        // 图片错误处理
        $('img').on('error', function() {
            let $img = $(this);
            if (!$img.data('error-handler')) {
                $img.data('error-handler', true);
                let placeholder = '//via.placeholder.com/300x400/6c757d/ffffff?text=图片失效';
                $img.attr('src', placeholder);
            }
        });
        
        // 页面跳转功能
        window.jumpToPage = function(form) {
            let pageInput = $(form).find('input[name="pg"]');
            let page = parseInt(pageInput.val());
            let maxPage = parseInt(pageInput.attr('max'));
            
            if (page < 1) {
                UTILS.showToast('页码不能小于1', 'warning');
                pageInput.val(1);
                return false;
            }
            
            if (page > maxPage) {
                UTILS.showToast('页码不能超过' + maxPage, 'warning');
                pageInput.val(maxPage);
                return false;
            }
            
            return true;
        };
    }
    
    // ==================
    // 页面特定功能
    // ==================
    function initPageSpecific() {
        // 首页功能
        if ($('.jumbotron').length || window.location.pathname === '/' || window.location.pathname === '/index') {
            // 首页特定逻辑
            $('.video-thumb').on('mouseenter mouseleave', function() {
                $(this).closest('.card').toggleClass('shadow-lg');
            });
        }
        
        // 详情页功能
        if ($('.player-container').length) {
            initPlayer();
        }
        
        // 搜索页功能
        if ($('input[name="q"]').length) {
            // 搜索结果高亮
            let urlParams = new URLSearchParams(window.location.search);
            let keyword = urlParams.get('q');
            if (keyword) {
                $('.card-title, .text-muted').each(function() {
                    let $el = $(this);
                    let html = $el.html();
                    let regex = new RegExp('(' + keyword.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + ')', 'gi');
                    let newHtml = html.replace(regex, '<mark>$1</mark>');
                    if (newHtml !== html) {
                        $el.html(newHtml);
                    }
                });
            }
        }
    }
    
    // ==================
    // 性能优化
    // ==================
    function initPerformance() {
        // 延迟加载图片
        if ('loading' in HTMLImageElement.prototype) {
            $('img[loading="lazy"]').each(function() {
                $(this).attr('loading', 'lazy');
            });
        } else {
            // 加载懒加载库
            let script = document.createElement('script');
            script.src = '//cdn.staticfile.org/lazysizes/5.3.2/lazysizes.min.js';
            document.head.appendChild(script);
        }
        
        // 平滑滚动
        if ('scrollBehavior' in document.documentElement.style === false) {
            $('a[href^="#"]').on('click', function(e) {
                let href = $(this).attr('href');
                if (href !== '#') {
                    e.preventDefault();
                    let target = $(href);
                    if (target.length) {
                        $('html, body').animate({
                            scrollTop: target.offset().top - 60
                        }, 500);
                    }
                }
            });
        }
        
        // 清除缓存和临时数据
        setInterval(() => {
            // 清理过期的localStorage项
            let keys = Object.keys(localStorage);
            keys.forEach(key => {
                if (key.startsWith('temp_')) {
                    let timestamp = localStorage.getItem(key + '_ts');
                    if (timestamp && Date.now() - timestamp > 3600000) { // 1小时过期
                        localStorage.removeItem(key);
                        localStorage.removeItem(key + '_ts');
                    }
                }
            });
        }, 600000); // 每10分钟检查一次
    }
    
    // ==================
    // 安全功能
    // ==================
    function initSecurity() {
        // 防止点击劫持
        if (top !== self) {
            top.location = self.location;
        }
        
        // CSRF保护（如果表单中有CSRF token）
        let csrfToken = $('meta[name="csrf-token"]').attr('content');
        if (csrfToken) {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': csrfToken
                }
            });
        }
        
        // 阻止右键菜单（可选）
        // $(document).on('contextmenu', 'img', function(e) {
        //     return false;
        // });
    }
    
    // ==================
    // 文档就绪
    // ==================
    $(document).ready(function() {
        // 初始化各个模块
        initSearch();
        initUI();
        initPageSpecific();
        initPerformance();
        initSecurity();
        
        // 调试信息
        if (CONFIG.debug) {
            console.info('影视网站JavaScript已加载');
            console.log('配置:', CONFIG);
        }
    });
    
    // 页面加载完成后的额外处理
    $(window).on('load', function() {
        // 页面完全加载后的逻辑
        if (CONFIG.debug) {
            console.log('页面加载完成');
        }
    });
    
})(jQuery);