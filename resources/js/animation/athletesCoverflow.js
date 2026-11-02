import { ScrollToPlugin } from 'gsap/ScrollToPlugin';
import { coverflowLayout } from './coverflowLayout';
import { onOwnLayer } from './pageAnimation';

// Called inside the page's matchMedia context. Desktop pins the inner wrapper;
// handheld follows native vertical page scrolling without a pin or scroll snap.
export function setupAthletesCoverflow({ gsap, ScrollTrigger, root, context, desktop }) {
    const section = root.querySelector('[data-athletes]');
    const inner = section?.querySelector('[data-athletes-inner]');
    const track = section?.querySelector('[data-athletes-track]');
    const slots = [...(section?.querySelectorAll('[data-athlete-slot]') ?? [])];
    const cards = slots.map((slot) => slot.querySelector('[data-athlete-card]'));
    const count = cards.length;

    if (count < 2) return undefined;

    gsap.registerPlugin(ScrollToPlugin);
    const controls = section.querySelector('[data-athletes-controls]');
    const counter = section.querySelector('[data-athletes-counter]');
    const progressLine = section.querySelector('[data-athletes-progress]');
    const prev = section.querySelector('[data-athletes-prev]');
    const next = section.querySelector('[data-athletes-next]');
    const selects = [...section.querySelectorAll('[data-athlete-select]')];
    const dims = [...section.querySelectorAll('[data-athlete-dim]')];
    // quickSetter writes directly: preserve exact attributes for breakpoints
    // and Inertia teardown, including styles that GSAP doesn't track.
    const originals = [...slots, ...cards, ...dims, progressLine].map((element) => [
        element, element.getAttribute('style'),
    ]);
    let active = -1;
    let targetIndex = null;
    let trigger;
    let scrollTween;
    const listeners = [];
    const listen = (element, name, handler, options) => {
        element.addEventListener(name, handler, options);
        listeners.push(() => element.removeEventListener(name, handler, options));
    };
    const clamp = (value) => Math.min(count - 1, Math.max(0, value));
    const updateCount = () => {
        document.documentElement.dataset.scrollTriggerCount = String(ScrollTrigger.getAll().length);
    };

    section.dataset.coverflow = desktop ? 'desktop' : 'handheld';
    controls.hidden = false;
    selects.forEach((button) => { button.hidden = false; });
    onOwnLayer(gsap, cards);
    onOwnLayer(gsap, [...dims, progressLine], 'transform, opacity');
    const setters = cards.map((card, index) => ({
        // Use the style object so CSSPlugin doesn't treat the complete string
        // as one of its cached numeric transform components.
        transform: gsap.quickSetter(card.style, 'transform'),
        opacity: gsap.quickSetter(card, 'opacity'),
        dim: gsap.quickSetter(dims[index], 'opacity'),
    }));
    const setProgress = gsap.quickSetter(progressLine, 'scaleX');

    const render = (value) => {
        const progress = clamp(value);
        const rounded = Math.round(progress);
        cards.forEach((card, index) => {
            const offset = index - progress;
            const layout = coverflowLayout(offset);
            const x = layout.x;
            const rotateY = layout.rotateY * (desktop ? 1 : 28 / 38);
            const z = layout.z * (desktop ? 1 : 140 / 220);
            const scale = desktop ? layout.scale : 1 - (1 - layout.scale) * (0.1 / 0.14);
            setters[index].transform(`translate3d(${x}%, 0, ${z}px) rotateY(${rotateY}deg) scale(${scale})`);
            setters[index].opacity(layout.opacity);
            setters[index].dim(layout.overlay);
        });
        setProgress(progress / (count - 1));

        // Text, filter classes, accessibility and stacking only change when
        // crossing an index. The image's filter transition is handled by CSS.
        if (rounded !== active) {
            active = rounded;
            counter.textContent = String(active + 1).padStart(2, '0');
            prev.disabled = active === 0;
            next.disabled = active === count - 1;
            cards.forEach((card, index) => {
                card.classList.toggle('is-active', index === active);
                slots[index].style.zIndex = String(count - Math.abs(index - active));
                if (index === active) slots[index].setAttribute('aria-current', 'true');
                else slots[index].removeAttribute('aria-current');
                selects[index].tabIndex = index === active ? 0 : -1;
            });
        }
    };
    render(0);

    const proxy = { progress: 0 };
    // On handheld, traverse the roster while the entire card is between the
    // sticky header and the bottom of the viewport. Scroll remains native.
    const handheldStart = () => Math.max(104, window.innerHeight - track.offsetHeight - 24);
    const tween = gsap.to(proxy, {
        progress: count - 1,
        ease: 'none',
        onUpdate: () => render(proxy.progress),
        scrollTrigger: {
            id: 'athletes-coverflow',
            trigger: desktop ? inner : track,
            invalidateOnRefresh: true,
            ...(desktop ? {
                pin: true,
                start: 'top top+=80',
                end: () => `+=${(count - 1) * window.innerHeight * 0.85}`,
                anticipatePin: 1,
                // Measure this pin before the already-created reveals, so the
                // closing section's triggers include the pin's spacing.
                refreshPriority: 1,
                scrub: 0.6,
                snap: {
                    snapTo: 1 / (count - 1),
                    inertia: false,
                    directional: false,
                    duration: { min: 0.25, max: 0.6 },
                    delay: 0.08,
                    ease: 'power2.inOut',
                },
            } : {
                start: () => `top top+=${handheldStart()}`,
                end: () => `+=${Math.max(120, handheldStart() - 104)}`,
                scrub: 0.35,
            }),
        },
    });
    trigger = tween.scrollTrigger;

    context.add('goToAthlete', (index) => {
        const destination = clamp(index);
        scrollTween?.kill();
        targetIndex = destination;
        trigger.getTween(true)?.kill?.();
        scrollTween = gsap.to(window, {
            scrollTo: {
                y: trigger.start + destination * (trigger.end - trigger.start) / (count - 1),
                autoKill: true,
            },
            duration: 0.65,
            ease: 'power2.inOut',
            onUpdate: () => trigger.getTween(true)?.kill?.(),
            onComplete: () => { targetIndex = null; },
            onInterrupt: () => { targetIndex = null; },
        });
    });
    const move = (delta) => context.goToAthlete((targetIndex ?? active) + delta);
    listen(prev, 'click', () => move(-1));
    listen(next, 'click', () => move(1));
    selects.forEach((button, index) => listen(button, 'click', () => context.goToAthlete(index)));
    listen(section, 'keydown', (event) => {
        if (event.key !== 'ArrowLeft' && event.key !== 'ArrowRight') return;
        event.preventDefault();
        move(event.key === 'ArrowRight' ? 1 : -1);
    });
    {
        const interruptScroll = () => {
            scrollTween?.kill();
            trigger.getTween(true)?.kill?.();
            targetIndex = null;
        };
        listen(window, 'wheel', interruptScroll, { passive: true });
        listen(window, 'touchstart', interruptScroll, { passive: true });
        listen(window, 'keydown', (event) => {
            if (['PageUp', 'PageDown', 'Home', 'End', ' ', 'ArrowUp', 'ArrowDown'].includes(event.key)) {
                interruptScroll();
            }
        });
    }
    updateCount();

    return () => {
        listeners.forEach((remove) => remove());
        scrollTween?.kill();
        delete section.dataset.coverflow;
        controls.hidden = true;
        counter.textContent = '01';
        prev.disabled = false;
        next.disabled = false;
        slots.forEach((slot) => slot.removeAttribute('aria-current'));
        cards.forEach((card) => card.classList.remove('is-active'));
        selects.forEach((button) => {
            button.removeAttribute('tabindex');
            button.hidden = true;
        });
        originals.forEach(([element, style]) => {
            if (style === null) element.removeAttribute('style');
            else element.setAttribute('style', style);
        });
        updateCount();
    };
}
