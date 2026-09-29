import { DESKTOP_QUERY, HANDHELD_QUERY, driftInFrame } from './imageMotion';
import { usePageAnimation } from './pageAnimation';

// Must match the `stack` variant in app.css, which makes the cards sticky.
const JOURNEY_STACK_QUERY =
    '(max-width: 639.98px) and (prefers-reduced-motion: no-preference) and (min-height: 30rem)';

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
        const journeyCards = gsap.utils.toArray('[data-home-journey-card]', root);
        const journeyMarkers = gsap.utils.toArray('[data-home-journey-marker]', root);
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

        media.add(DESKTOP_QUERY, () => {
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
        });

        media.add(HANDHELD_QUERY, () => {
            statementImages.forEach((image) => {
                driftInFrame(gsap, image, image.closest('section'));
            });

            driftInFrame(gsap, finalImage, finalImage?.closest('section'));
        });

        // Each card recedes and darkens while the next one slides over it.
        // The stacking itself is plain CSS, so the scroll stays the phone's
        // own; this only adds depth, and only while a card is being covered.
        media.add(JOURNEY_STACK_QUERY, () => {
            journeyCards.slice(0, -1).forEach((card, index) => {
                const next = journeyCards[index + 1];
                const stuckAt = () =>
                    Number.parseFloat(window.getComputedStyle(next).top) || 0;

                gsap.timeline({
                    defaults: { ease: 'none' },
                    scrollTrigger: {
                        end: () => `top ${stuckAt()}px`,
                        invalidateOnRefresh: true,
                        scrub: true,
                        // From the moment this card sticks, not from the moment
                        // the next one shows. On an iPhone with the toolbar
                        // tucked away the screen is taller than a card, so the
                        // next card peeks in while this one is still arriving.
                        start: () => `top ${stuckAt() + card.offsetHeight}px`,
                        trigger: journeyMarkers[index + 1],
                    },
                })
                    .fromTo(
                        card.querySelector('[data-home-journey-card-inner]'),
                        { scale: 1 },
                        { scale: 0.9, transformOrigin: '50% 0%' },
                        0,
                    )
                    .fromTo(
                        card.querySelector('[data-home-journey-dim]'),
                        { opacity: 0 },
                        { opacity: 0.6 },
                        0,
                    );
            });
        });

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
