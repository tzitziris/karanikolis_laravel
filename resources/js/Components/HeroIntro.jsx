import { useRef } from 'react';
import { Link } from '@inertiajs/react';
import { useHeroIntro } from '../animation/useHeroIntro';

export default function HeroIntro({ actions = [], eyebrow, summary, title }) {
    const rootRef = useRef(null);

    useHeroIntro(rootRef);

    return (
        <section
            ref={rootRef}
            className="border-b border-line-strong bg-ink-0 px-6 py-12 sm:px-10 sm:py-16"
            data-page-hero
        >
            <div className="mx-auto flex max-w-5xl flex-col gap-6">
                <p
                    className="font-mono text-xs font-bold uppercase text-blood"
                    data-hero-eyebrow
                >
                    {eyebrow}
                </p>
                <h1
                    className="max-w-4xl font-display text-5xl font-black leading-[0.9] text-bone sm:text-7xl"
                    data-hero-title
                >
                    {title}
                </h1>
                <p
                    className="max-w-2xl text-lg leading-8 text-bone-dim"
                    data-hero-summary
                >
                    {summary}
                </p>
                {actions.length > 0 && (
                    <nav
                        aria-label="Δοκιμαστική πλοήγηση"
                        className="flex flex-wrap gap-3"
                        data-hero-detail
                    >
                        {actions.map((action) => (
                            <Link
                                className="border border-line-strong px-4 py-3 font-mono text-xs font-bold uppercase text-bone transition hover:border-blood hover:text-blood"
                                href={action.href}
                                key={action.href}
                                prefetch={['mount', 'hover']}
                            >
                                {action.label}
                            </Link>
                        ))}
                    </nav>
                )}
            </div>
        </section>
    );
}
