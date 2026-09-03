import { useEffect } from 'react';
import gsap from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import { SplitText } from 'gsap/SplitText';

gsap.registerPlugin(ScrollTrigger, SplitText);

const REDUCED_MOTION_QUERY = '(prefers-reduced-motion: reduce)';
const ANIMATED_ATTRIBUTE = 'data-animation-managed';

function getReducedMotionPreference() {
    return (
        typeof window !== 'undefined' &&
        window.matchMedia(REDUCED_MOTION_QUERY).matches
    );
}

function updateScrollTriggerCount() {
    if (typeof document === 'undefined') {
        return;
    }

    document.documentElement.dataset.scrollTriggerCount = String(
        ScrollTrigger.getAll().length,
    );
}

export function usePageAnimation(scopeRef, setup, dependencies = []) {
    useEffect(() => {
        const root = scopeRef.current;

        if (!root || typeof window === 'undefined') {
            return undefined;
        }

        if (getReducedMotionPreference()) {
            updateScrollTriggerCount();

            return () => {
                updateScrollTriggerCount();
            };
        }

        const splitInstances = [];
        let setupCleanup;
        let context;

        const animateFromVisible = (targets, vars = {}) => {
            const elements = gsap.utils.toArray(targets, root);

            elements.forEach((element) => {
                element.setAttribute(ANIMATED_ATTRIBUTE, '');
            });

            gsap.set(elements, vars);

            return elements;
        };

        const splitText = (target, vars) => {
            const split = SplitText.create(target, vars);
            splitInstances.push(split);

            return split;
        };

        try {
            context = gsap.context(() => {
                setupCleanup = setup({
                    gsap,
                    ScrollTrigger,
                    SplitText,
                    animateFromVisible,
                    root,
                    splitText,
                });
            }, root);
            updateScrollTriggerCount();
        } catch (error) {
            setupCleanup?.();
            context?.revert();
            splitInstances.forEach((split) => split.revert());
            updateScrollTriggerCount();
            throw error;
        }

        return () => {
            setupCleanup?.();
            splitInstances.forEach((split) => split.revert());
            context?.revert();
            updateScrollTriggerCount();
        };
    }, dependencies);
}

export function animateMobileMenuOpen(panel) {
    if (!panel || typeof window === 'undefined' || getReducedMotionPreference()) {
        return () => {};
    }

    let context;

    try {
        context = gsap.context(() => {
            const links = panel.querySelectorAll('[data-mobile-menu-link]');
            const footer = panel.querySelector('[data-mobile-menu-footer]');

            gsap.fromTo(
                panel,
                { y: -16 },
                {
                    duration: 0.32,
                    ease: 'power3.out',
                    y: 0,
                },
            );

            gsap.fromTo(
                [...links, footer].filter(Boolean),
                { y: 18 },
                {
                    duration: 0.42,
                    ease: 'power3.out',
                    stagger: 0.055,
                    y: 0,
                },
            );
        }, panel);
    } catch (error) {
        console.error('Η κίνηση του μενού δεν ολοκληρώθηκε.', error);

        return () => {};
    }

    return () => context?.revert();
}
