const illustrationMotions = [
    {
        selector: '.journey-loupe',
        duration: 2200,
        keyframes: {
            translate: ['0 0', '-20px -12px', '5px -5px', '0 0'],
            rotate: ['0deg', '-8deg', '4deg', '0deg'],
        },
    },
    {
        selector: '.journey-stamp',
        duration: 1800,
        keyframes: {
            offset: [0, 0.28, 0.48, 0.62, 0.76, 1],
            translate: ['0 0', '0 -12px', '0 2px', '0 -3px', '0 0', '0 0'],
            rotate: ['0deg', '-9deg', '3deg', '-1deg', '0deg', '0deg'],
            scale: ['1', '1.05', '0.95', '1.02', '1', '1'],
        },
    },
    {
        selector: '.journey-bag',
        duration: 2400,
        keyframes: {
            translate: ['0 0', '0 -7px', '0 -4px', '0 0'],
            rotate: ['0deg', '-5deg', '4deg', '0deg'],
        },
    },
    {
        selector: '.journey-tag',
        duration: 2400,
        keyframes: { rotate: ['0deg', '12deg', '-8deg', '0deg'] },
    },
    {
        selector: '.journey-spark',
        duration: 2400,
        keyframes: { scale: ['1', '1.16', '0.92', '1'] },
    },
    {
        selector: '.found-cta-card',
        duration: 2600,
        keyframes: {
            translate: ['0 0', '0 -7px', '0 -4px', '0 0'],
            rotate: ['0deg', '-3deg', '1.5deg', '0deg'],
        },
    },
    {
        selector: '.found-cta-key',
        duration: 2000,
        keyframes: { rotate: ['0deg', '17deg', '-12deg', '7deg', '0deg'] },
    },
];

export function initializeIllustrationHover() {
    if (!Element.prototype.animate) return;

    const motion = window.matchMedia(
        '(hover: hover) and (pointer: fine) and (prefers-reduced-motion: no-preference)',
    );
    const resetIllustrations = Array.from(
        document.querySelectorAll('.journey-art, .found-cta-art'),
        (surface) => {
            const parts = illustrationMotions.flatMap((settings) => {
                const element = surface.querySelector(settings.selector);
                return element ? [{ ...settings, element, animation: null }] : [];
            });
            let hovered = false;
            let settling = false;

            const play = () => {
                if (!hovered || settling || !motion.matches) return;

                parts.forEach((part) => {
                    // Individual transforms leave the scroll reveal's transform untouched.
                    part.animation = part.element.animate(part.keyframes, {
                        duration: part.duration,
                        iterations: Infinity,
                        easing: 'ease-in-out',
                    });
                });
            };

            const reset = (immediate = false) => {
                hovered = false;
                if (immediate) {
                    parts.forEach((part) => {
                        part.animation?.cancel();
                        part.animation = null;
                    });
                    return;
                }
                if (settling) return;

                settling = true;
                const returns = parts.map((part) => {
                    if (!part.animation) return Promise.resolve();

                    const style = getComputedStyle(part.element);
                    const current = {};
                    const rest = {};
                    Object.entries(part.keyframes).forEach(([property, values]) => {
                        if (property === 'offset') return;
                        current[property] = style[property];
                        rest[property] = values[0];
                    });
                    part.animation.cancel();
                    part.animation = part.element.animate([current, rest], {
                        duration: 280,
                        easing: 'cubic-bezier(0.2, 0.7, 0.25, 1)',
                    });
                    return part.animation.finished.catch(() => {});
                });

                // Rapid re-entry waits for the return motion instead of snapping to frame one.
                Promise.all(returns).then(() => {
                    settling = false;
                    play();
                });
            };

            surface.addEventListener('pointerenter', (event) => {
                if (event.pointerType === 'touch' || !motion.matches) return;
                hovered = true;
                play();
            });
            surface.addEventListener('pointerleave', () => reset());
            surface.addEventListener('pointercancel', () => reset());

            return () => reset(true);
        },
    );

    motion.addEventListener('change', () => {
        if (!motion.matches) resetIllustrations.forEach((reset) => reset());
    });
}
