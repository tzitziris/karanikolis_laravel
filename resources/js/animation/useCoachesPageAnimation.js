import {
    DESKTOP_QUERY,
    HANDHELD_QUERY,
    driftInFrame,
    openFrame,
} from './imageMotion';
import { usePageAnimation } from './pageAnimation';

export function useCoachesPageAnimation(scopeRef) {
    usePageAnimation(scopeRef, ({ gsap, root }) => {
        const revealItems = root.querySelectorAll('[data-coaches-reveal]');
        const heroImage = root.querySelector('[data-coaches-hero-image]');
        const heroContent = root.querySelector('[data-coaches-hero-content]');
        const coachImage = root.querySelector('[data-coach-image]');
        const closingImage = root.querySelector('[data-coaches-closing-image]');
        const athleteImages = root.querySelectorAll('[data-athlete-image]');
        const galleryImages = root.querySelectorAll('[data-gallery-image]');
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
            [coachImage, closingImage, ...athleteImages, ...galleryImages]
                .filter(Boolean)
                .forEach((image) => {
                    gsap.fromTo(
                        image,
                        { scale: 1.04 },
                        {
                            ease: 'none',
                            scale: 1,
                            scrollTrigger: {
                                end: 'center center',
                                scrub: 0.8,
                                start: 'top bottom',
                                trigger: image.closest('section, article, figure'),
                            },
                        },
                    );
                });
        });

        media.add(HANDHELD_QUERY, () => {
            // The portrait is framed to the pixel (head near the top, shorts
            // near the bottom), so it opens but does not drift: the zoom that
            // drifting needs would crop the head.
            openFrame(gsap, coachImage?.parentElement);

            // The athlete photographs carry a CSS transition on transform for
            // their hover effect, which would drag any drift 700ms behind the
            // scroll. Their frames open instead.
            athleteImages.forEach((image) => openFrame(gsap, image.parentElement));

            galleryImages.forEach((image) => {
                const frame = image.closest('figure');

                openFrame(gsap, frame);
                driftInFrame(gsap, image, frame);
            });

            driftInFrame(gsap, closingImage, closingImage?.closest('section'));
        });

        return () => media.revert();
    }, []);
}
