import staticImageData from './staticImages.generated.json';

export const STATIC_IMAGE_WIDTHS = staticImageData.widths;

export const STATIC_IMAGE_BASE_PATH = staticImageData.basePath;

export const STATIC_IMAGE_SLOTS = {
    coachesClosing: '(min-width: 1024px) 100vw, max(100vw, clamp(576px, 165vw, 816px))',
    coverflow: '(prefers-reduced-motion: reduce) and (min-width: 1024px) 33vw, (prefers-reduced-motion: reduce) calc(100vw - 40px), (min-width: 1024px) min(48svh, 480px), min(74vw, 352px)',
    card: '(min-width: 1024px) 33vw, (min-width: 640px) 50vw, 100vw',
    full: '100vw',
    half: '(min-width: 768px) 50vw, 100vw',
    hero: '100vw',
    logo: '32px',
    portrait: '(min-width: 1024px) 40vw, 100vw',
    third: '(min-width: 1024px) 33vw, 100vw',
};

export const STATIC_IMAGE_LOADING = {
    coachesClosing: 'lazy',
    coverflow: 'lazy',
    card: 'lazy',
    full: 'lazy',
    half: 'lazy',
    hero: 'eager',
    logo: 'eager',
    portrait: 'lazy',
    third: 'lazy',
};

export const STATIC_IMAGES = staticImageData.images;
