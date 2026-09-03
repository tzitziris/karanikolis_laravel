import { useRef } from 'react';
import { useRevealOnScroll } from '../animation/useRevealOnScroll';

export default function RevealOnScroll({ children, className = '' }) {
    const rootRef = useRef(null);

    useRevealOnScroll(rootRef);

    return (
        <section className={className} data-reveal-scope ref={rootRef}>
            {children}
        </section>
    );
}
