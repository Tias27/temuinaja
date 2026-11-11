export function initializeReportSlider() {
    const grid = document.querySelector('.reports .report-grid');
    const controls = document.querySelector('.report-slider-controls');
    const cards = Array.from(grid?.querySelectorAll('.report-card') ?? []);

    if (!grid || !controls || !cards.length) return;

    const previous = controls.querySelector('[data-slide="prev"]');
    const next = controls.querySelector('[data-slide="next"]');
    const range = controls.querySelector('[data-report-range]');
    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');

    const updateControls = () => {
        controls.hidden = grid.scrollWidth <= grid.clientWidth + 2;
        previous.disabled = grid.scrollLeft <= 2;
        next.disabled = grid.scrollLeft + grid.clientWidth >= grid.scrollWidth - 2;

        const bounds = grid.getBoundingClientRect();
        const visible = cards.flatMap((card, index) => {
            const box = card.getBoundingClientRect();
            const overlap = Math.min(box.right, bounds.right) - Math.max(box.left, bounds.left);
            return overlap >= box.width / 2 ? [index + 1] : [];
        });
        if (!visible.length) return;

        const first = visible[0];
        const last = visible.at(-1);
        const label = `${first === last ? first : `${first}\u2013${last}`} dari ${cards.length}`;
        if (range.textContent !== label) range.textContent = label;
    };

    controls.addEventListener('click', (event) => {
        const button = event.target.closest('[data-slide]');
        if (!button || button.disabled) return;

        const gap = parseFloat(getComputedStyle(grid).columnGap) || 0;
        const step = cards[0].getBoundingClientRect().width + gap;
        const visibleCount = Math.max(1, Math.floor((grid.clientWidth + gap) / step));
        grid.scrollBy({
            left: (button.dataset.slide === 'next' ? 1 : -1) * step * visibleCount,
            behavior: reducedMotion.matches ? 'instant' : 'smooth',
        });
    });
    grid.addEventListener('scroll', updateControls, { passive: true });
    if ('ResizeObserver' in window) {
        new ResizeObserver(updateControls).observe(grid);
    } else {
        window.addEventListener('resize', updateControls);
    }
    updateControls();
}
