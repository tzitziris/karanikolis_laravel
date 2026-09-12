import {
    STATIC_IMAGE_LOADING,
    STATIC_IMAGE_SLOTS,
} from '../../images/staticImages';
import SiteImage from '../SiteImage';

function validDimension(value) {
    return Number.isInteger(value) && value > 0;
}

export default function ArticleImage({
    alt,
    className = '',
    image,
    imageName,
    loading,
    priority = false,
    slot = 'full',
    ...props
}) {
    const metadata = image ?? (imageName ? { name: imageName, source: 'static' } : null);

    if (!metadata || metadata.source !== 'upload') {
        return (
            <SiteImage
                alt={alt}
                className={className}
                image={metadata?.name}
                loading={loading}
                priority={priority}
                slot={slot}
                {...props}
            />
        );
    }

    const widths = Array.isArray(metadata.widths) ? metadata.widths : [];
    const fallbackWidth = widths[widths.length - 1];
    const sizes = STATIC_IMAGE_SLOTS[slot];
    const hasSize = validDimension(metadata.width) && validDimension(metadata.height);

    if (!fallbackWidth || !sizes || !hasSize) {
        const label = alt || 'Η εικόνα δεν είναι διαθέσιμη.';

        return (
            <span
                aria-label={label}
                className={className}
                data-missing-uploaded-image={metadata.name}
                role={alt ? 'img' : undefined}
                {...props}
            >
                {alt}
            </span>
        );
    }

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
            src={`/images/${metadata.name}-${fallbackWidth}.webp`}
            srcSet={widths
                .map((width) => `/images/${metadata.name}-${width}.webp ${width}w`)
                .join(', ')}
            width={metadata.width}
            {...props}
        />
    );
}
