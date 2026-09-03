import { usePageAnimation } from './pageAnimation';

export function useRevealOnScroll(scopeRef) {
    usePageAnimation(scopeRef, ({ animateFromVisible, gsap, root }) => {
        const items = root.querySelectorAll('[data-reveal-item]');

        if (!items.length) {
            return;
        }

        animateFromVisible(items, {
            y: 16,
        });

        gsap.to(items, {
            duration: 0.5,
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
