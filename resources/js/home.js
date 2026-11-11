import './site-navigation';
import { initializeIllustrationHover } from './illustration-hover';
import { initializeReportSlider } from './report-slider';

function initializeScrollReveals() {
    const journeySteps = document.querySelectorAll('.journey-step, .found-cta');
    const journeyMotion = window.matchMedia('(prefers-reduced-motion: reduce)');

    if (!('IntersectionObserver' in window) || journeyMotion.matches) return;

    const journeyObserver = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) return;
                entry.target.classList.add('is-visible');
                journeyObserver.unobserve(entry.target);
            });
        },
        { threshold: 0.2 },
    );
    journeySteps.forEach((step) => journeyObserver.observe(step));
}

initializeReportSlider();
initializeScrollReveals();
initializeIllustrationHover();
