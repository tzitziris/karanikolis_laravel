import { useState } from 'react';

export default function YoutubeEmbed({ title, youtubeId }) {
    const [isLoaded, setIsLoaded] = useState(false);

    return (
        <div className="relative aspect-video overflow-hidden border border-line bg-ink-2">
            {isLoaded ? (
                <iframe
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                    allowFullScreen
                    className="h-full w-full"
                    loading="lazy"
                    src={`https://www.youtube-nocookie.com/embed/${youtubeId}?autoplay=1`}
                    title={title}
                />
            ) : (
                <button
                    aria-label={`Αναπαραγωγή βίντεο: ${title}`}
                    className="group relative flex h-full w-full cursor-pointer items-center justify-center overflow-hidden bg-[linear-gradient(135deg,var(--ink-2),var(--ink-3)_58%,rgba(212,161,66,0.18))] text-bone transition-colors duration-300 hover:text-blood focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blood"
                    onClick={() => setIsLoaded(true)}
                    type="button"
                >
                    <span
                        aria-hidden="true"
                        className="absolute inset-0 bg-[linear-gradient(180deg,rgba(5,5,5,.04),rgba(5,5,5,.64))]"
                    />
                    <span className="relative flex h-20 w-20 items-center justify-center border border-blood bg-blood text-ink-0 transition-transform duration-300 group-hover:scale-[1.04] sm:h-24 sm:w-24">
                        <span className="translate-x-0.5 font-display text-3xl">
                            ▶
                        </span>
                    </span>
                    <span className="absolute bottom-4 left-4 right-4 flex items-center gap-3 font-mono text-[10px] uppercase text-bone">
                        <span
                            aria-hidden="true"
                            className="block h-px w-6 bg-blood"
                        />
                        Βίντεο · πάτησε για αναπαραγωγή
                    </span>
                </button>
            )}
        </div>
    );
}
