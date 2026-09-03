import {
    STATIC_IMAGE_BASE_PATH,
    STATIC_IMAGE_SLOTS,
    STATIC_IMAGE_WIDTHS,
    STATIC_IMAGES,
} from '../images/staticImages';

function widthsFor(image) {
    return STATIC_IMAGE_WIDTHS.filter((width) => width <= image.width);
}

export default function SiteImage({
    alt,
    className = '',
    image,
    loading = 'lazy',
    slot = 'full',
    ...props
}) {
    const metadata = STATIC_IMAGES[image];

    if (!metadata) {
        throw new Error(`Unknown static image: ${image}`);
    }

    const sizes = STATIC_IMAGE_SLOTS[slot];

    if (!sizes) {
        throw new Error(`Unknown static image slot: ${slot}`);
    }

    const widths = widthsFor(metadata);
    const fallbackWidth = widths[widths.length - 1];

    return (
        <img
            alt={alt}
            className={className}
            decoding="async"
            height={metadata.height}
            loading={loading}
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
