import {
    DESKTOP_QUERY,
    HANDHELD_QUERY,
    driftInFrame,
    openFrame,
} from './imageMotion';
import { usePageAnimation } from './pageAnimation';

export function useAboutPageAnimation(scopeRef) {
    usePageAnimation(scopeRef, ({ gsap, root }) => {
        const revealItems = root.querySelectorAll('[data-about-reveal]');
        const heroImage = root.querySelector('[data-about-hero-image]');
        const heroContent = root.querySelector('[data-about-hero-content]');
        const storyImage = root.querySelector('[data-about-story-image]');
        const statementImage = root.querySelector('[data-about-statement-image]');
        const media = gsap.matchMedia();

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

        media.add(DESKTOP_QUERY, () => {
            if (storyImage) {
                gsap.fromTo(
                    storyImage,
                    { scale: 1.04 },
                    {
                        ease: 'none',
                        scale: 1,
                        scrollTrigger: {
                            end: '35% center',
                            scrub: 0.8,
                            start: 'top bottom',
                            trigger: storyImage.closest('[data-about-story]'),
                        },
                    },
                );
            }

            if (statementImage) {
                gsap.fromTo(
                    statementImage,
                    { scale: 1.06 },
                    {
                        ease: 'none',
                        scale: 1,
                        scrollTrigger: {
                            end: 'bottom top',
                            scrub: 1,
                            start: 'top bottom',
                            trigger: statementImage.closest('section'),
                        },
                    },
                );
            }
        });

        media.add(HANDHELD_QUERY, () => {
            openFrame(gsap, storyImage?.parentElement);
            driftInFrame(gsap, storyImage, storyImage?.parentElement);
            driftInFrame(gsap, statementImage, statementImage?.closest('section'));
        });

        return () => media.revert();
    }, []);
}
