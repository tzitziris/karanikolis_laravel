import ArticleImage from './ArticleImage';

function validDimension(value) {
    return Number.isInteger(value) && value > 0;
}

export default function Photo({
    alt,
    className = '',
    height,
    image,
    imageClassName = '',
    imageName,
    mode = 'tile',
    slot = 'full',
    width,
}) {
    const hasSize = validDimension(width) && validDimension(height);
    const ratio = hasSize ? width / height : 16 / 9;
    const naturalStyle =
        mode === 'natural'
            ? {
                  aspectRatio: String(ratio),
                  width: `min(100%, ${Math.min(96, ratio * 80).toFixed(2)}svh)`,
              }
            : undefined;

    return (
        <div
            className={`relative isolate overflow-hidden bg-ink-2 ${
                mode === 'natural' ? 'mx-auto max-h-[80svh]' : 'h-full w-full'
            } ${className}`}
            style={naturalStyle}
        >
            <span
                aria-hidden="true"
                className="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_center,rgba(212,161,66,.12),transparent_65%)]"
            />
            <ArticleImage
                alt={alt}
                className={`h-full w-full object-contain ${imageClassName}`}
                image={image}
                imageName={imageName}
                slot={slot}
            />
        </div>
    );
}
