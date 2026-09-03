import { usePageAnimation } from './pageAnimation';

export function useHomePageAnimation(scopeRef) {
    usePageAnimation(scopeRef, ({ gsap, root }) => {
        const revealItems = root.querySelectorAll('[data-home-reveal]');
        const heroImage = root.querySelector('[data-home-hero-image]');
        const heroContent = root.querySelector('[data-home-hero-content]');
        const statementImages = root.querySelectorAll('[data-home-statement-image]');
        const finalImage = root.querySelector('[data-home-final-image]');
        const finalCopy = root.querySelector('[data-home-final-copy]');
        const desktopJourney = root.querySelector('[data-home-journey-desktop]');
        const journeyTrack = root.querySelector('[data-home-journey-track]');
        const journeyImages = root.querySelectorAll('[data-home-journey-image]');
        const media = gsap.matchMedia();

        revealItems.forEach((item) => {
            gsap.fromTo(
                item,
                { y: 18 },
                {
                    duration: 0.72,
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
                scale: 1.06,
                scrollTrigger: {
                    end: 'bottom top',
                    scrub: 1,
                    start: 'top top',
                    trigger: heroImage.closest('section'),
                },
                yPercent: 5,
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
                yPercent: 8,
            });
        }

        statementImages.forEach((image) => {
            gsap.fromTo(
                image,
                { scale: 1.06 },
                {
                    ease: 'none',
                    scale: 1,
                    scrollTrigger: {
                        end: 'bottom top',
                        scrub: 1,
                        start: 'top bottom',
                        trigger: image.closest('section'),
                    },
                },
            );
        });

        if (finalImage) {
            gsap.fromTo(
                finalImage,
                { scale: 1.06 },
                {
                    ease: 'none',
                    scale: 1,
                    scrollTrigger: {
                        end: 'center center',
                        scrub: 1,
                        start: 'top bottom',
                        trigger: finalImage.closest('section'),
                    },
                },
            );
        }

        if (finalCopy) {
            gsap.fromTo(
                finalCopy,
                { y: 28 },
                {
                    duration: 0.8,
                    ease: 'power3.out',
                    scrollTrigger: {
                        once: true,
                        start: 'top 70%',
                        trigger: finalCopy,
                    },
                    y: 0,
                },
            );
        }

        media.add(
            '(min-width: 640px) and (prefers-reduced-motion: no-preference)',
            () => {
                if (!desktopJourney || !journeyTrack) {
                    return undefined;
                }

                const travelDistance = () =>
                    Math.max(0, journeyTrack.scrollWidth - window.innerWidth);

                gsap.set(journeyTrack, { x: 0 });
                gsap.set(journeyImages, { scale: 1.04, xPercent: -2 });

                gsap.timeline({
                    defaults: { ease: 'none' },
                    scrollTrigger: {
                        anticipatePin: 1,
                        end: () => `+=${window.innerHeight * 3}`,
                        invalidateOnRefresh: true,
                        pin: true,
                        scrub: 1.15,
                        start: 'top top',
                        trigger: desktopJourney,
                    },
                })
                    .to(
                        journeyTrack,
                        {
                            duration: 1,
                            x: () => -travelDistance(),
                        },
                        0,
                    )
                    .to(
                        journeyImages,
                        {
                            duration: 1,
                            xPercent: 2,
                        },
                        0,
                    );

                return undefined;
            },
        );

        return () => media.revert();
    }, []);
}
