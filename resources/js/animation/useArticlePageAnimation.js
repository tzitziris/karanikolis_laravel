import { usePageAnimation } from './pageAnimation';

export function useArticlePageAnimation(scopeRef) {
    usePageAnimation(scopeRef, ({ gsap, root }) => {
        const heroImage = root.querySelector('[data-article-hero-image]');
        const heroContent = root.querySelector('[data-article-hero-content]');
        const revealSections = root.querySelectorAll('[data-article-section]');
        const galleryItems = root.querySelectorAll('[data-article-gallery-item]');

        [...revealSections, ...galleryItems].forEach((element) => {
            gsap.fromTo(
                element,
                { y: 24 },
                {
                    duration: 0.72,
                    ease: 'power3.out',
                    scrollTrigger: {
                        once: true,
                        start: 'top 84%',
                        trigger: element,
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
                yPercent: 5,
            });
        }
    }, []);
}
