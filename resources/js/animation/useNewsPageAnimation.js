import { usePageAnimation } from './pageAnimation';

export function useNewsPageAnimation(scopeRef) {
    usePageAnimation(scopeRef, ({ gsap, root }) => {
        const cards = root.querySelectorAll('[data-news-card]');
        const heroImage = root.querySelector('[data-news-hero-image]');
        const heroContent = root.querySelector('[data-news-hero-content]');

        cards.forEach((card) => {
            gsap.fromTo(
                card,
                { y: 24 },
                {
                    duration: 0.72,
                    ease: 'power3.out',
                    scrollTrigger: {
                        once: true,
                        start: 'top 84%',
                        trigger: card,
                    },
                    y: 0,
                },
            );
        });

        if (heroImage) {
            gsap.to(heroImage, {
                ease: 'none',
                scale: 1.07,
                scrollTrigger: {
                    end: 'bottom top',
                    scrub: 1,
                    start: 'top top',
                    trigger: heroImage.closest('section'),
                },
                yPercent: 7,
            });
        }

        if (heroContent) {
            gsap.to(heroContent, {
                ease: 'none',
                scrollTrigger: {
                    end: 'bottom top',
                    scrub: 1,
                    start: 'top top',
                    trigger: heroContent.closest('section'),
                },
                yPercent: 6,
            });
        }
    }, []);
}
