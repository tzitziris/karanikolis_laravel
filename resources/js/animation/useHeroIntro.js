import { usePageAnimation } from './pageAnimation';

export function useHeroIntro(scopeRef) {
    usePageAnimation(scopeRef, ({ gsap, hideForAnimation, root, splitText }) => {
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

        hideForAnimation([eyebrow, summary, ...details].filter(Boolean), {
            y: 18,
        });
        hideForAnimation(titleTargets, {
            y: 28,
        });

        gsap.timeline({
            defaults: {
                duration: 0.7,
                ease: 'power3.out',
            },
        })
            .to(eyebrow, {
                autoAlpha: 1,
                y: 0,
            })
            .to(
                titleTargets,
                {
                    autoAlpha: 1,
                    stagger: 0.018,
                    y: 0,
                },
                '-=0.35',
            )
            .to(
                [summary, ...details].filter(Boolean),
                {
                    autoAlpha: 1,
                    stagger: 0.08,
                    y: 0,
                },
                '-=0.35',
            );
    }, []);
}
