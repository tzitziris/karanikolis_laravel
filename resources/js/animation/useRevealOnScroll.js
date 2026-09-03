import { usePageAnimation } from './pageAnimation';

export function useRevealOnScroll(scopeRef) {
    usePageAnimation(scopeRef, ({ gsap, hideForAnimation, root }) => {
        const items = root.querySelectorAll('[data-reveal-item]');

        if (!items.length) {
            return;
        }

        hideForAnimation(items, {
            y: 32,
        });

        gsap.to(items, {
            autoAlpha: 1,
            duration: 0.65,
            ease: 'power3.out',
            scrollTrigger: {
                end: 'bottom 15%',
                once: true,
                start: 'top 82%',
                trigger: root,
            },
            stagger: 0.08,
            y: 0,
        });
    }, []);
}
