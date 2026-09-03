import { usePageAnimation } from './pageAnimation';

export function useHeroIntro(scopeRef) {
    usePageAnimation(scopeRef, ({ animateFromVisible, gsap, root, splitText }) => {
        const eyebrow = root.querySelector('[data-hero-eyebrow]');
        const title = root.querySelector('[data-hero-title]');
        const summary = root.querySelector('[data-hero-summary]');
        const details = root.querySelectorAll('[data-hero-detail]');

        const split = title
            ? splitText(title, {
                  linesClass: 'hero-intro-line',
                  type: 'lines,words',
              })
            : null;

        const titleTargets = split?.words?.length ? split.words : title;

        animateFromVisible([eyebrow, summary, ...details].filter(Boolean), {
            y: 10,
        });
        animateFromVisible(titleTargets, {
            y: 12,
        });

        gsap.timeline({
            defaults: {
                duration: 0.5,
                ease: 'power3.out',
            },
        })
            .to(eyebrow, {
                y: 0,
            })
            .to(
                titleTargets,
                {
                    stagger: 0.018,
                    y: 0,
                },
                '-=0.35',
            )
            .to(
                [summary, ...details].filter(Boolean),
                {
                    stagger: 0.08,
                    y: 0,
                },
                '-=0.35',
            );
    }, []);
}
