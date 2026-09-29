import { onOwnLayer } from './pageAnimation';

// From this width up, the photographs sit in sticky columns and the page
// moves around them. Below it they are plain boxes in the flow, and a 4%
// zoom on a phone-sized box is too small to see, so the motion has to come
// from the photograph itself.
export const DESKTOP_QUERY =
    '(min-width: 1024px) and (prefers-reduced-motion: no-preference)';
export const HANDHELD_QUERY =
    '(max-width: 1023.98px) and (prefers-reduced-motion: no-preference)';

// The frame opens from slightly inset to full bleed as it comes up the
// screen. It is clipped at the edges, never hidden: most of the photograph
// is there from the first pixel, and all of it well before the frame
// reaches the middle of the screen. On a layer of its own the frame is
// clipped by the compositor, so opening it never repaints the photograph and
// its filter underneath.
export function openFrame(gsap, frame) {
    if (!frame) {
        return;
    }

    onOwnLayer(gsap, frame);
    gsap.fromTo(
        frame,
        { clipPath: 'inset(8% 6%)' },
        {
            clipPath: 'inset(0% 0%)',
            ease: 'none',
            scrollTrigger: {
                end: 'top 40%',
                scrub: 0.6,
                start: 'top bottom',
                trigger: frame,
            },
        },
    );
}

// The photograph moves more slowly than the page, inside a frame that clips
// it. The zoom is the headroom: at 1.14 there is 7% to spare on each side, and
// the drift uses 5% of it, so an edge never shows.
export function driftInFrame(gsap, image, trigger) {
    if (!image || !trigger) {
        return;
    }

    onOwnLayer(gsap, image);
    gsap.fromTo(
        image,
        { scale: 1.14, yPercent: -5 },
        {
            ease: 'none',
            scale: 1.14,
            scrollTrigger: {
                end: 'bottom top',
                scrub: 0.6,
                start: 'top bottom',
                trigger,
            },
            yPercent: 5,
        },
    );
}
