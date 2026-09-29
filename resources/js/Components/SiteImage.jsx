import { useLayoutEffect, useRef } from 'react';
import {
    STATIC_IMAGE_BASE_PATH,
    STATIC_IMAGE_LOADING,
    STATIC_IMAGE_SLOTS,
    STATIC_IMAGES,
} from '../images/staticImages';

// Native lazy loading waits until a photograph is almost on screen, and on a
// phone that is late enough to watch each box sit black and then fill in.
// Asking two screens ahead gives the file time to arrive.
const LOAD_AHEAD_MARGIN = '200% 0px';

const BACKGROUND_SIZE_FOR_FIT = {
    contain: 'contain',
    cover: 'cover',
    fill: '100% 100%',
};

let loadAheadObserver;

function sharedLoadAheadObserver() {
    if (loadAheadObserver !== undefined) {
        return loadAheadObserver;
    }

    loadAheadObserver =
        typeof IntersectionObserver === 'function'
            ? new IntersectionObserver(
                  (entries, observer) => {
                      entries.forEach((entry) => {
                          if (entry.isIntersecting) {
                              entry.target.loading = 'eager';
                              observer.unobserve(entry.target);
                          }
                      });
                  },
                  { rootMargin: LOAD_AHEAD_MARGIN },
              )
            : null;

    return loadAheadObserver;
}

// The manifest carries each photograph as a webp 40 pixels on its longer
// side, blurred when it was built (config/images.php), so all the browser
// does is stretch it. A blur filter drawn here instead would be computed at
// the full size of the box, on a phone's processor, every time it is painted.
function placeholderBackground(metadata) {
    return metadata.placeholder ? `url("${metadata.placeholder}")` : null;
}

function widthsFor(image) {
    return image.widths;
}

export default function SiteImage({
    alt,
    className = '',
    image,
    loading,
    priority = false,
    slot = 'full',
    ...props
}) {
    const imageRef = useRef(null);
    const metadata = STATIC_IMAGES[image];
    const sizes = STATIC_IMAGE_SLOTS[slot];
    const loadingMode = priority
        ? 'eager'
        : (loading ?? STATIC_IMAGE_LOADING[slot] ?? 'lazy');
    const placeholder = metadata ? placeholderBackground(metadata) : null;

    // Nothing here hides the photograph. The placeholder is painted behind it
    // and the real file covers it the moment it is decoded; if this never
    // runs, the page is exactly what it was without it.
    useLayoutEffect(() => {
        const element = imageRef.current;

        if (!element) {
            return undefined;
        }

        const cleanups = [];

        if (placeholder && !(element.complete && element.naturalWidth > 0)) {
            const computed = window.getComputedStyle(element);

            element.style.backgroundImage = placeholder;
            element.style.backgroundPosition = computed.objectPosition;
            element.style.backgroundRepeat = 'no-repeat';
            element.style.backgroundSize =
                BACKGROUND_SIZE_FOR_FIT[computed.objectFit] ?? 'cover';

            // Kept until the photograph is ready to paint, so there is never
            // a frame of black between the two. Dropped after that, so the
            // browser is not asked to redraw a blur nobody can see.
            const clear = () => {
                element.style.backgroundImage = '';
            };
            const onLoad = () => {
                if (typeof element.decode === 'function') {
                    element.decode().then(clear, clear);
                } else {
                    clear();
                }
            };

            element.addEventListener('load', onLoad, { once: true });
            cleanups.push(() => element.removeEventListener('load', onLoad));
        }

        if (loadingMode === 'lazy') {
            const observer = sharedLoadAheadObserver();

            if (observer) {
                observer.observe(element);
                cleanups.push(() => observer.unobserve(element));
            }
        }

        return () => cleanups.forEach((cleanup) => cleanup());
    }, [image, loadingMode, placeholder]);

    if (!metadata || !sizes) {
        const label = alt || 'Η εικόνα δεν είναι διαθέσιμη.';

        return (
            <span
                aria-label={label}
                className={className}
                data-missing-static-image={image}
                role={alt ? 'img' : undefined}
                {...props}
            >
                {alt}
            </span>
        );
    }

    const widths = widthsFor(metadata);
    const fallbackWidth = widths[widths.length - 1];
    const fetchPriority = priority ? 'high' : (props.fetchPriority ?? 'auto');

    return (
        <img
            alt={alt}
            className={className}
            decoding="async"
            fetchPriority={fetchPriority}
            height={metadata.height}
            loading={loadingMode}
            ref={imageRef}
            sizes={sizes}
            src={`${STATIC_IMAGE_BASE_PATH}/${image}-${fallbackWidth}.webp`}
            srcSet={widths
                .map(
                    (width) =>
                        `${STATIC_IMAGE_BASE_PATH}/${image}-${width}.webp ${width}w`,
                )
                .join(', ')}
            width={metadata.width}
            {...props}
        />
    );
}
