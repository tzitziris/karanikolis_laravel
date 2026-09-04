import { Link } from '@inertiajs/react';
import SiteImage from '../SiteImage';

export default function ArticleCard({ article, index = 0, revealAttribute }) {
    const revealProps = revealAttribute ? { [revealAttribute]: '' } : {};

    return (
        <article className="h-full min-w-0 bg-ink-1" {...revealProps}>
            <Link
                className="group relative flex h-full min-w-0 cursor-pointer flex-col overflow-hidden border border-line-strong bg-ink-1 transition-colors duration-300 hover:border-blood hover:bg-ink-2 focus-visible:z-10"
                href={article.href}
                prefetch={['hover']}
            >
                <div className="relative aspect-[4/3] shrink-0 overflow-hidden bg-ink-3">
                    {article.coverImageName ? (
                        <SiteImage
                            alt={article.title}
                            className="h-full w-full object-cover grayscale contrast-125 transition-[filter,transform] duration-700 ease-out group-hover:scale-105 group-hover:grayscale-0"
                            image={article.coverImageName}
                            slot="card"
                        />
                    ) : (
                        <div className="flex h-full items-center justify-center bg-[linear-gradient(135deg,var(--ink-2),var(--ink-3)_55%,rgba(212,161,66,0.16))]">
                            <span className="font-display text-[clamp(5.5rem,12vw,9rem)] font-black leading-none text-blood/25">
                                ΝΕΑ
                            </span>
                        </div>
                    )}
                    <div className="pointer-events-none absolute inset-0 bg-[linear-gradient(180deg,transparent_45%,rgba(5,5,5,.78))]" />
                    <div className="absolute inset-x-4 bottom-4 flex items-end justify-between border-t border-white/25 pt-3 text-[9px] font-medium uppercase text-bone/75">
                        <span>Αρχείο</span>
                        <time dateTime={article.publishedAt}>
                            {article.date}
                        </time>
                    </div>
                </div>

                <div className="flex min-h-[16rem] flex-1 flex-col p-5 sm:min-h-[18rem] sm:p-6">
                    <p className="text-[9px] font-medium uppercase text-blood">
                        Νέα · {String(index + 1).padStart(2, '0')}
                    </p>
                    <h2 className="mt-5 line-clamp-2 min-h-[4.5rem] break-words font-display text-[clamp(2.35rem,7vw,3.1rem)] font-black uppercase leading-[0.9] text-bone transition-colors duration-300 group-hover:text-blood sm:min-h-[5.6rem]">
                        {article.title}
                    </h2>
                    <p className="mt-5 line-clamp-3 min-h-[4.5rem] text-sm leading-6 text-bone-dim">
                        {article.excerpt}
                    </p>
                    <div className="mt-auto flex min-h-11 items-end gap-3 pt-6 text-[10px] font-semibold uppercase text-blood">
                        <span className="mb-[.35rem] block h-px w-8 bg-current transition-all duration-300 group-hover:w-14" />
                        Διάβασε
                        <span aria-hidden="true">↗</span>
                    </div>
                </div>
            </Link>
        </article>
    );
}
