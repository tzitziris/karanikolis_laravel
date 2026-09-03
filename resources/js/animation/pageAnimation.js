import { useEffect } from 'react';
import gsap from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import { SplitText } from 'gsap/SplitText';

gsap.registerPlugin(ScrollTrigger, SplitText);

export const REVEAL_WATCHDOG_MS = 1800;

const REDUCED_MOTION_QUERY = '(prefers-reduced-motion: reduce)';
const HIDDEN_ATTRIBUTE = 'data-animation-hidden';

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

export function forceRevealAll(root = document) {
    if (!root?.querySelectorAll) {
        return;
    }

    const hiddenElements = root.querySelectorAll(`[${HIDDEN_ATTRIBUTE}]`);

    hiddenElements.forEach((element) => {
        gsap.set(element, {
            clearProps: 'opacity,visibility,transform,filter,clipPath',
        });
        element.removeAttribute(HIDDEN_ATTRIBUTE);
    });

    updateScrollTriggerCount();
}

export function usePageAnimation(scopeRef, setup, dependencies = []) {
    useEffect(() => {
        const root = scopeRef.current;

        if (!root || typeof window === 'undefined') {
            return undefined;
        }

        const watchdogId = window.setTimeout(
            () => forceRevealAll(root),
            REVEAL_WATCHDOG_MS,
        );

        if (getReducedMotionPreference()) {
            forceRevealAll(root);

            return () => {
                window.clearTimeout(watchdogId);
                forceRevealAll(root);
            };
        }

        const splitInstances = [];
        let context;

        const hideForAnimation = (targets, vars = {}) => {
            const elements = gsap.utils.toArray(targets, root);

            elements.forEach((element) => {
                element.setAttribute(HIDDEN_ATTRIBUTE, '');
            });

            gsap.set(elements, {
                autoAlpha: 0,
                ...vars,
            });

            return elements;
        };

        const splitText = (target, vars) => {
            const split = SplitText.create(target, vars);
            splitInstances.push(split);

            return split;
        };

        try {
            context = gsap.context(() => {
                setup({
                    gsap,
                    ScrollTrigger,
                    SplitText,
                    forceRevealAll: () => forceRevealAll(root),
                    hideForAnimation,
                    root,
                    splitText,
                });
            }, root);
            updateScrollTriggerCount();
        } catch (error) {
            forceRevealAll(root);
            throw error;
        }

        return () => {
            window.clearTimeout(watchdogId);
            splitInstances.forEach((split) => split.revert());
            context?.revert();
            forceRevealAll(root);
            updateScrollTriggerCount();
        };
    }, dependencies);
}
