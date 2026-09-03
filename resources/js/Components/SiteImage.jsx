import {
    STATIC_IMAGE_BASE_PATH,
    STATIC_IMAGE_LOADING,
    STATIC_IMAGE_SLOTS,
    STATIC_IMAGES,
} from '../images/staticImages';

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
    const metadata = STATIC_IMAGES[image];
    const sizes = STATIC_IMAGE_SLOTS[slot];

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
    const loadingMode = priority
        ? 'eager'
        : (loading ?? STATIC_IMAGE_LOADING[slot] ?? 'lazy');
    const fetchPriority = priority ? 'high' : (props.fetchPriority ?? 'auto');

    return (
        <img
            alt={alt}
            className={className}
            decoding="async"
            fetchPriority={fetchPriority}
            height={metadata.height}
            loading={loadingMode}
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
