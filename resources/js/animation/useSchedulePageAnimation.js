import { usePageAnimation } from './pageAnimation';

export function useSchedulePageAnimation(scopeRef) {
    usePageAnimation(scopeRef, ({ gsap, root }) => {
        const revealItems = root.querySelectorAll('[data-schedule-reveal]');
        const heroImage = root.querySelector('[data-schedule-hero-image]');
        const heroContent = root.querySelector('[data-schedule-hero-content]');
        const rhythmImage = root.querySelector('[data-schedule-rhythm-image]');
        const closingImage = root.querySelector('[data-schedule-closing-image]');

        revealItems.forEach((item) => {
            gsap.fromTo(
                item,
                { y: 22 },
                {
                    duration: 0.76,
                    ease: 'power3.out',
                    scrollTrigger: {
                        once: true,
                        start: 'top 84%',
                        trigger: item,
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

        [rhythmImage, closingImage].filter(Boolean).forEach((image) => {
            gsap.fromTo(
                image,
                { scale: 1.05 },
                {
                    ease: 'none',
                    scale: 1,
                    scrollTrigger: {
                        end: 'center center',
                        scrub: 0.8,
                        start: 'top bottom',
                        trigger: image.closest('section'),
                    },
                },
            );
        });
    }, []);
}
