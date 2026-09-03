import staticImageData from './staticImages.generated.json';

export const STATIC_IMAGE_WIDTHS = staticImageData.widths;

export const STATIC_IMAGE_BASE_PATH = staticImageData.basePath;

export const STATIC_IMAGE_SLOTS = {
    card: '(min-width: 1024px) 33vw, (min-width: 640px) 50vw, 100vw',
    full: '100vw',
    half: '(min-width: 768px) 50vw, 100vw',
    hero: '100vw',
    logo: '32px',
    portrait: '(min-width: 1024px) 40vw, 100vw',
};

export const STATIC_IMAGE_LOADING = {
    card: 'lazy',
    full: 'lazy',
    half: 'lazy',
    hero: 'eager',
    logo: 'eager',
    portrait: 'lazy',
};

export const STATIC_IMAGES = staticImageData.images;
