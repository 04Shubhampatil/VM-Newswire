/**
 * Lightweight motion helpers (no animation library).
 * Everything respects prefers-reduced-motion and only touches transform/opacity via CSS classes or custom properties.
 */
const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');

/** Scroll reveals: [data-reveal] elements fade/slide in once; [data-reveal-group] staggers its children. */
function initReveals() {
    const items = document.querySelectorAll('[data-reveal]');
    if (!items.length) return;

    document.querySelectorAll('[data-reveal-group]').forEach((group) => {
        group.querySelectorAll('[data-reveal]').forEach((el, i) => {
            el.style.setProperty('--reveal-i', String(Math.min(i, 5)));
        });
    });

    const finish = (el) => {
        // Hand the element back to its normal styles (hover transitions etc.) once revealed.
        el.removeAttribute('data-reveal');
        el.classList.remove('is-visible');
        el.style.removeProperty('--reveal-i');
    };

    if (reducedMotion.matches || !('IntersectionObserver' in window)) {
        items.forEach(finish);
        return;
    }

    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) return;
                const el = entry.target;
                observer.unobserve(el);
                el.classList.add('is-visible');
                const delay = parseFloat(getComputedStyle(el).transitionDelay) || 0;
                setTimeout(() => finish(el), 950 + delay * 1000);
            });
        },
        { rootMargin: '0px 0px -8% 0px', threshold: 0.12 },
    );

    items.forEach((el) => observer.observe(el));
}

/** Header: frosted background after the page scrolls; progress bar fallback where scroll timelines are unsupported. */
function initScrollState() {
    const header = document.querySelector('.site-header');
    const progress = document.querySelector('.scroll-progress');
    const needsProgressFallback = progress && !CSS.supports('animation-timeline: scroll()');
    let ticking = false;

    const update = () => {
        ticking = false;
        const y = window.scrollY;
        header?.toggleAttribute('data-scrolled', y > 8);
        if (needsProgressFallback) {
            const max = document.documentElement.scrollHeight - window.innerHeight;
            progress.style.setProperty('--progress', max > 0 ? String(Math.min(y / max, 1)) : '0');
        }
    };

    window.addEventListener('scroll', () => {
        if (!ticking) {
            ticking = true;
            requestAnimationFrame(update);
        }
    }, { passive: true });

    update();
}

/** Hero parallax: tiny, spring-like offsets on precise pointers only. */
function initParallax() {
    const stage = document.querySelector('[data-parallax]');
    if (!stage || reducedMotion.matches || !window.matchMedia('(pointer: fine)').matches) return;

    const area = stage.closest('section') || stage;
    let target = { x: 0, y: 0 };
    let current = { x: 0, y: 0 };
    let raf = null;

    const step = () => {
        current.x += (target.x - current.x) * 0.08;
        current.y += (target.y - current.y) * 0.08;
        stage.style.setProperty('--mx', current.x.toFixed(3));
        stage.style.setProperty('--my', current.y.toFixed(3));

        if (Math.abs(target.x - current.x) > 0.001 || Math.abs(target.y - current.y) > 0.001) {
            raf = requestAnimationFrame(step);
        } else {
            raf = null;
        }
    };

    const kick = () => {
        if (!raf) raf = requestAnimationFrame(step);
    };

    area.addEventListener('pointermove', (event) => {
        const rect = stage.getBoundingClientRect();
        const x = (event.clientX - (rect.left + rect.width / 2)) / (rect.width / 2);
        const y = (event.clientY - (rect.top + rect.height / 2)) / (rect.height / 2);
        target = { x: Math.max(-1, Math.min(1, x)), y: Math.max(-1, Math.min(1, y)) };
        kick();
    });

    area.addEventListener('pointerleave', () => {
        target = { x: 0, y: 0 };
        kick();
    });
}

/** How-it-works: steps activate in sequence (01 → 02 → 03 → 04) when the timeline enters the viewport. */
function initTimelines() {
    document.querySelectorAll('[data-steps]').forEach((list) => {
        const steps = [...list.querySelectorAll('.step')];

        const activate = (instant) => {
            steps.forEach((step, i) => {
                const run = () => {
                    steps.forEach((s) => s.classList.remove('is-current'));
                    step.classList.add('is-active', 'is-current');
                };
                instant ? run() : setTimeout(run, i * 450);
            });
        };

        if (reducedMotion.matches || !('IntersectionObserver' in window)) {
            activate(true);
            return;
        }

        const observer = new IntersectionObserver((entries) => {
            if (entries.some((e) => e.isIntersecting)) {
                observer.disconnect();
                activate(false);
            }
        }, { threshold: 0.35 });

        observer.observe(list);
    });
}

export function initMotion() {
    initReveals();
    initScrollState();
    initParallax();
    initTimelines();
}
