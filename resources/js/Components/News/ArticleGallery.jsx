import { useEffect, useRef, useState } from 'react';
import { createPortal } from 'react-dom';
import Photo from './Photo';

const focusableSelector =
    'button:not([disabled]), [href], input:not([disabled]), [tabindex]:not([tabindex="-1"])';

export default function ArticleGallery({ fallbackAlt, images }) {
    const [activeIndex, setActiveIndex] = useState(null);
    const dialogRef = useRef(null);
    const closeButtonRef = useRef(null);
    const restoreFocusRef = useRef(null);
    const isOpen = activeIndex !== null;
    const activeImage = isOpen ? images[activeIndex] : null;

    function close() {
        setActiveIndex(null);
    }

    function open(index, trigger) {
        restoreFocusRef.current = trigger;
        setActiveIndex(index);
    }

    function previous() {
        setActiveIndex((current) =>
            current === null ? null : (current - 1 + images.length) % images.length,
        );
    }

    function next() {
        setActiveIndex((current) =>
            current === null ? null : (current + 1) % images.length,
        );
    }

    useEffect(() => {
        if (!isOpen) {
            return undefined;
        }

        const previousOverflow = document.body.style.overflow;
        const appRoot = document.getElementById('site-content');

        document.body.style.overflow = 'hidden';
        appRoot?.setAttribute('inert', '');
        closeButtonRef.current?.focus({ preventScroll: true });

        function onKeyDown(event) {
            if (event.key === 'Escape') {
                event.preventDefault();
                setActiveIndex(null);
                return;
            }

            if (event.key === 'ArrowLeft') {
                event.preventDefault();
                previous();
                return;
            }

            if (event.key === 'ArrowRight') {
                event.preventDefault();
                next();
                return;
            }

            if (event.key !== 'Tab' || !dialogRef.current) {
                return;
            }

            const focusable = Array.from(
                dialogRef.current.querySelectorAll(focusableSelector),
            );

            if (!focusable.length) {
                event.preventDefault();
                return;
            }

            const first = focusable[0];
            const last = focusable[focusable.length - 1];

            if (event.shiftKey && document.activeElement === first) {
                event.preventDefault();
                last.focus();
            } else if (!event.shiftKey && document.activeElement === last) {
                event.preventDefault();
                first.focus();
            }
        }

        document.addEventListener('keydown', onKeyDown);

        return () => {
            document.body.style.overflow = previousOverflow;
            appRoot?.removeAttribute('inert');
            document.removeEventListener('keydown', onKeyDown);
            restoreFocusRef.current?.focus({ preventScroll: true });
        };
    }, [isOpen, images.length]);

    const dialog =
        activeImage && typeof document !== 'undefined' ? (
            <div
                aria-label="Προβολή φωτογραφίας"
                aria-modal="true"
                className="fixed inset-0 z-[100] flex items-center justify-center bg-ink-0/95 p-3 backdrop-blur-md sm:p-6"
                onMouseDown={(event) => {
                    if (event.target === event.currentTarget) {
                        close();
                    }
                }}
                ref={dialogRef}
                role="dialog"
            >
                <div className="relative flex h-full w-full max-w-[min(96rem,100%)] items-center justify-center">
                    <Photo
                        alt={activeImage.altText ?? fallbackAlt}
                        className="max-w-full border border-line-strong"
                        height={activeImage.height}
                        imageName={activeImage.imageName}
                        mode="natural"
                        slot="full"
                        width={activeImage.width}
                    />

                    <button
                        aria-label="Κλείσιμο προβολής"
                        className="absolute right-0 top-0 min-h-11 cursor-pointer border border-line-strong bg-ink-0/85 px-4 py-3 font-mono text-xs uppercase text-bone transition-colors hover:border-blood hover:text-blood focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blood"
                        onClick={close}
                        ref={closeButtonRef}
                        type="button"
                    >
                        Κλείσιμο ×
                    </button>

                    {images.length > 1 ? (
                        <>
                            <button
                                aria-label="Προηγούμενη φωτογραφία"
                                className="absolute bottom-0 left-0 min-h-11 cursor-pointer border border-line-strong bg-ink-0/85 px-4 py-3 font-mono text-[10px] uppercase text-bone transition-colors hover:border-blood hover:text-blood focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blood sm:bottom-auto sm:top-1/2 sm:-translate-y-1/2"
                                onClick={previous}
                                type="button"
                            >
                                ← Προηγούμενη
                            </button>
                            <button
                                aria-label="Επόμενη φωτογραφία"
                                className="absolute bottom-0 right-0 min-h-11 cursor-pointer border border-line-strong bg-ink-0/85 px-4 py-3 font-mono text-[10px] uppercase text-bone transition-colors hover:border-blood hover:text-blood focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blood sm:bottom-auto sm:top-1/2 sm:-translate-y-1/2"
                                onClick={next}
                                type="button"
                            >
                                Επόμενη →
                            </button>
                        </>
                    ) : null}
                </div>
            </div>
        ) : null;

    return (
        <>
            <div className="mt-8 grid grid-flow-dense gap-px bg-line sm:grid-cols-2">
                {images.map((image, index) => (
                    <button
                        aria-label={`Άνοιγμα φωτογραφίας ${index + 1} από ${images.length}`}
                        className={`group relative aspect-[4/3] cursor-pointer overflow-hidden bg-ink-2 focus-visible:z-10 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blood ${
                            images.length % 2 === 1 && index === images.length - 1
                                ? 'sm:col-span-2 sm:aspect-[8/3]'
                                : ''
                        }`}
                        data-article-gallery-item
                        key={image.id}
                        onClick={(event) => open(index, event.currentTarget)}
                        type="button"
                    >
                        <Photo
                            alt={image.altText ?? fallbackAlt}
                            imageClassName="object-cover transition-transform duration-700 ease-out group-hover:scale-[1.02]"
                            height={image.height}
                            imageName={image.imageName}
                            slot="card"
                            width={image.width}
                        />
                        <span className="pointer-events-none absolute bottom-3 right-3 border border-line-strong bg-ink-0/75 px-3 py-2 font-mono text-[9px] uppercase text-bone backdrop-blur-sm">
                            Προβολή
                        </span>
                    </button>
                ))}
            </div>

            {dialog ? createPortal(dialog, document.body) : null}
        </>
    );
}
