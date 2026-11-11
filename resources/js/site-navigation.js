const navigation = document.querySelector('.mobile-navigation');

if (navigation) {
    const home = navigation.querySelector('[data-mobile-nav="home"]');
    const guide = navigation.querySelector('[data-mobile-nav="guide"]');
    const onHome = home.hasAttribute('aria-current');

    if (onHome) {
        const updateActiveSection = () => {
            const isGuide = window.location.hash === '#cara-kerja';
            home.toggleAttribute('aria-current', !isGuide);
            guide.toggleAttribute('aria-current', isGuide);
            if (isGuide) guide.setAttribute('aria-current', 'location');
            else home.setAttribute('aria-current', 'page');
        };
        window.addEventListener('hashchange', updateActiveSection);
        updateActiveSection();
    }
}
