document.addEventListener('DOMContentLoaded', () => {
    // Initialize Highlight.js
    if (typeof hljs !== 'undefined') {
        hljs.highlightAll();
    }

    // Add Copy Button and Language Label to Code Blocks
    const codeWrappers = document.querySelectorAll('.code-wrapper');

    codeWrappers.forEach(wrapper => {
        const pre = wrapper.querySelector('pre');
        const code = pre ? pre.querySelector('code') : null;

        if (!code) return;

        // 1. Get Language
        // Highlight.js usually adds class "language-xyz"
        let language = 'Code';
        code.classList.forEach(cls => {
            if (cls.startsWith('language-') || cls.startsWith('lang-')) {
                language = cls.replace(/^language-|^lang-/, '').toUpperCase();
            }
        });

        // Handle specific overrides if needed (e.g., 'SH' -> 'BASH')
        if (language === 'SH') language = 'BASH';
        if (language === 'ZSH') language = 'BASH';

        // 2. Insert Language Label
        const langTag = document.createElement('span');
        langTag.className = 'code-lang-tag';
        langTag.textContent = language;
        wrapper.appendChild(langTag);

        // 3. Insert Copy Button
        const copyBtn = document.createElement('button');
        copyBtn.className = 'code-copy-btn';
        copyBtn.textContent = 'Copy';
        copyBtn.title = 'Copy code to clipboard';
        wrapper.appendChild(copyBtn);

        // 4. Copy Functionality
        copyBtn.addEventListener('click', async () => {
            try {
                // Get code text
                const text = code.innerText;

                // Write to clipboard
                await navigator.clipboard.writeText(text);

                // Feedback
                const originalText = copyBtn.textContent;
                copyBtn.textContent = 'Copied!';
                copyBtn.classList.add('copied');

                setTimeout(() => {
                    copyBtn.textContent = originalText;
                    copyBtn.classList.remove('copied');
                }, 2000);

            } catch (err) {
                console.error('Failed to copy class:', err);
                copyBtn.textContent = 'Failed';
                setTimeout(() => {
                    copyBtn.textContent = 'Copy';
                }, 2000);
            }
        });
    });
    // Initialize Table of Contents
    initTOC();

    // Initialize Featured Carousel
    initFeaturedCarousel();
<<<<<<< HEAD
});

/**
=======

    // Initialize Mobile Toolbar
    initMobileToolbar();
});

/**
 * Handles Mobile Bottom Toolbar Interactions
 */
function initMobileToolbar() {
    const categoriesBtn = document.getElementById('mobile-categories-btn');
    const topBtn = document.getElementById('mobile-top-btn');
    const sheet = document.getElementById('category-sheet');
    const sheetOverlay = document.getElementById('category-sheet-overlay');
    const closeSheetBtn = document.querySelector('.close-sheet-btn');
    const sheetList = document.getElementById('category-sheet-list');

    // Back to Top
    if (topBtn) {
        topBtn.addEventListener('click', () => {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    }

    // Mobile Search Logic
    const searchBtn = document.getElementById('mobile-search-btn');
    const searchSheet = document.getElementById('search-sheet');
    const searchOverlay = document.getElementById('search-sheet-overlay');
    const closeSearchBtn = document.getElementById('close-search-sheet');
    const searchInput = document.getElementById('mobile-search-input');

    function toggleSearchSheet(show) {
        if (show) {
            searchSheet.classList.add('active');
            searchOverlay.classList.add('active');
            document.body.style.overflow = 'hidden';
            setTimeout(() => {
                if (searchInput) searchInput.focus();
            }, 100);
        } else {
            searchSheet.classList.remove('active');
            searchOverlay.classList.remove('active');
            document.body.style.overflow = '';
            if (searchInput) searchInput.blur();
        }
    }

    if (searchBtn) {
        searchBtn.addEventListener('click', () => toggleSearchSheet(true));
    }

    if (closeSearchBtn) {
        closeSearchBtn.addEventListener('click', () => toggleSearchSheet(false));
    }

    if (searchOverlay) {
        searchOverlay.addEventListener('click', () => toggleSearchSheet(false));
    }

    // Toggle Categories Sheet
    function toggleSheet(show) {
        if (show) {
            sheet.classList.add('active');
            sheetOverlay.classList.add('active');
            document.body.style.overflow = 'hidden'; // Prevent background scrolling
            loadCategoriesIfNeeded();
        } else {
            sheet.classList.remove('active');
            sheetOverlay.classList.remove('active');
            document.body.style.overflow = '';
        }
    }

    if (categoriesBtn) {
        categoriesBtn.addEventListener('click', () => toggleSheet(true));
    }

    if (closeSheetBtn) {
        closeSheetBtn.addEventListener('click', () => toggleSheet(false));
    }

    if (sheetOverlay) {
        sheetOverlay.addEventListener('click', () => toggleSheet(false));
    }

    // Fetch Categories
    let categoriesLoaded = false;
    async function loadCategoriesIfNeeded() {
        if (categoriesLoaded) return;

        try {
            const response = await fetch('/api/categories');
            const data = await response.json();

            if (data.categories && data.categories.length > 0) {
                sheetList.innerHTML = data.categories.map(cat => `
                    <a href="/category/${cat.slug}" class="sheet-category-item">
                        ${cat.name}
                    </a>
                `).join('');
            } else {
                sheetList.innerHTML = '<p style="grid-column: 1/-1; text-align: center; color: var(--ink-secondary);">暂无分类</p>';
            }
            categoriesLoaded = true;
        } catch (error) {
            console.error('Failed to load categories:', error);
            sheetList.innerHTML = '<p style="grid-column: 1/-1; text-align: center; color: var(--ink-secondary);">加载失败，请重试</p>';
        }
    }
}

/**
>>>>>>> a3d11b8 (sync: update open-source release)
 * Handles Featured Post Carousel (Dots + Swipe)
 */
function initFeaturedCarousel() {
    const track = document.querySelector('.featured-track');
    const slides = document.querySelectorAll('.featured-slide');
    const dots = document.querySelectorAll('.featured-dots button');

    if (!track || slides.length === 0) return;

    let currentIndex = 0;
    let startX = 0;
    let isDragging = false;

    // Go to slide function
    function goToSlide(index) {
        if (index < 0) index = 0;
        if (index >= slides.length) index = slides.length - 1;

        currentIndex = index;

        // Update track position
        track.style.transform = `translateX(-${currentIndex * 100}%)`;

        // Update active class for fades (if any used) and Dots
        slides.forEach((slide, i) => {
            slide.classList.toggle('active', i === currentIndex);
        });

        if (dots.length > 0) {
            dots.forEach((dot, i) => {
                dot.classList.toggle('active', i === currentIndex);
            });
        }
    }

    // Dot Clicks
    if (dots.length > 0) {
        dots.forEach((dot, index) => {
            dot.addEventListener('click', () => {
                goToSlide(index);
            });
        });
    }

    // Touch / Swipe Logic
    track.addEventListener('touchstart', (e) => {
        startX = e.touches[0].clientX;
        isDragging = true;
        // Remove transition for instant drag feel? 
        // For simple swipe, keeping transition is nicer usually unless doing 1:1 follow
    }, { passive: true });

    track.addEventListener('touchmove', (e) => {
        if (!isDragging) return;
        // Optional: Implement 1:1 following
    }, { passive: true });

    track.addEventListener('touchend', (e) => {
        if (!isDragging) return;
        const endX = e.changedTouches[0].clientX;
        const diff = startX - endX;

        // Threshold for swipe
        if (Math.abs(diff) > 50) {
            if (diff > 0) {
                // Swipe Left -> Next Slide
                if (currentIndex < slides.length - 1) {
                    goToSlide(currentIndex + 1);
                }
            } else {
                // Swipe Right -> Prev Slide
                if (currentIndex > 0) {
                    goToSlide(currentIndex - 1);
                }
            }
        }
        isDragging = false;
    });
}


/**
 * Generates Table of Contents and handles Scroll Spy
 */
function initTOC() {
    const postBody = document.querySelector('.post-body');
    const tocList = document.getElementById('toc-list');

    // Only run on pages with post body and toc container
    if (!postBody || !tocList) return;

    const headings = postBody.querySelectorAll('h2, h3');
    if (headings.length === 0) {
        document.querySelector('.post-toc-container').style.display = 'none';
        return;
    }

    let lastH2Li = null;

    headings.forEach((heading, index) => {
        // Ensure ID exists
        if (!heading.id) {
            heading.id = 'heading-' + index;
        }

        const link = document.createElement('a');
        link.href = '#' + heading.id;
        link.textContent = heading.textContent;
        link.dataset.target = heading.id;

        const li = document.createElement('li');
        li.appendChild(link);

        if (heading.tagName === 'H2') {
            tocList.appendChild(li);
            lastH2Li = li;
        } else if (heading.tagName === 'H3') {
            if (!lastH2Li) {
                // Formatting fallback: H3 without preceding H2
                tocList.appendChild(li);
                lastH2Li = li;
            } else {
                let subUl = lastH2Li.querySelector('ul');
                if (!subUl) {
                    subUl = document.createElement('ul');
                    lastH2Li.appendChild(subUl);
                }
                subUl.appendChild(li);
            }
        }
    });

    // Smooth Scrolling
    tocList.querySelectorAll('a').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const targetId = this.getAttribute('href').substring(1);
            const targetElement = document.getElementById(targetId);

            if (targetElement) {
                const headerOffset = 80;
                const elementPosition = targetElement.getBoundingClientRect().top;
                const offsetPosition = elementPosition + window.pageYOffset - headerOffset;

                window.scrollTo({
                    top: offsetPosition,
                    behavior: "smooth"
                });

                // Update URL hash without jumping
                history.pushState(null, null, '#' + targetId);
            }
        });
    });

    // Scroll Spy (Intersection Observer)
    const observerOptions = {
        root: null,
        rootMargin: '-10% 0px -65% 0px', // Hit area near top of viewport
        threshold: 0
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                // Remove active from all
                tocList.querySelectorAll('a').forEach(a => a.classList.remove('active'));

                // Add active to current
                const id = entry.target.id;
                const activeLink = tocList.querySelector(`a[data-target="${id}"]`);
                if (activeLink) {
                    activeLink.classList.add('active');

                    // Also scroll TOC container if needed to keep active link in view
                    // simple check:
                    // activeLink.scrollIntoView({ block: 'nearest' }); 
                }
            }
        });
    }, observerOptions);

    headings.forEach(heading => observer.observe(heading));
}
