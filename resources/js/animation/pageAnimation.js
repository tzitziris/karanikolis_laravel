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
                setup({
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
            context?.revert();
            splitInstances.forEach((split) => split.revert());
            updateScrollTriggerCount();
            throw error;
        }

        return () => {
            splitInstances.forEach((split) => split.revert());
            context?.revert();
            updateScrollTriggerCount();
        };
    }, dependencies);
}
