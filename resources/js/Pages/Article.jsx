import { Link } from '@inertiajs/react';
import { useRef } from 'react';
import ArticleGallery from '../Components/News/ArticleGallery';
import ArticleImage from '../Components/News/ArticleImage';
import Photo from '../Components/News/Photo';
import YoutubeEmbed from '../Components/News/YoutubeEmbed';
import SiteImage from '../Components/SiteImage';
import { useArticlePageAnimation } from '../animation/useArticlePageAnimation';

function CoverFallback() {
    return (
        <div className="flex h-full min-h-[18rem] items-center justify-center bg-[linear-gradient(135deg,var(--ink-2),var(--ink-3)_55%,rgba(212,161,66,0.16))]">
            <span className="font-display text-[clamp(7rem,20vw,16rem)] font-black leading-none text-blood/25">
                ΝΕΑ
            </span>
        </div>
    );
}

export default function Article({ article }) {
    const pageRef = useRef(null);
    const hasCover = Boolean(article.coverImage);
    const coverAlt = article.coverImageAltText ?? article.title;
    const gallery = article.gallery ?? [];
    const videos = article.videos ?? [];

    useArticlePageAnimation(pageRef);

    return (
        <div className="w-full max-w-full overflow-x-clip bg-ink-0" ref={pageRef}>
            <article>
                <section className="grain relative isolate min-h-[calc(100dvh-5rem)] overflow-hidden bg-ink-0">
                    {hasCover ? (
                        <ArticleImage
                            alt=""
                            aria-hidden="true"
                            className="absolute inset-0 -z-30 h-full w-full object-cover object-center saturate-[.55] contrast-125"
                            data-article-hero-image
                            image={article.coverImage}
                            priority
                            slot="hero"
                        />
                    ) : (
                        <SiteImage
                            alt=""
                            aria-hidden="true"
                            className="absolute inset-0 -z-30 h-full w-full object-cover object-center grayscale contrast-125"
                            data-article-hero-image
                            image="ring-training"
                            priority
                            slot="hero"
                        />
                    )}
                    <div className="absolute inset-0 -z-20 bg-[linear-gradient(90deg,rgba(5,5,5,.98)_0%,rgba(5,5,5,.8)_58%,rgba(5,5,5,.32)_100%)]" />
                    <div className="absolute inset-0 -z-10 bg-[linear-gradient(180deg,rgba(5,5,5,.08),rgba(5,5,5,.95)_100%)]" />

                    <div
                        className="mx-auto flex min-h-[calc(100dvh-5rem)] max-w-[1600px] flex-col justify-end px-5 pb-10 pt-24 sm:px-8 sm:pb-12 lg:px-12 lg:pb-14"
                        data-article-hero-content
                    >
                        <div data-article-section>
                            <Link
                                className="inline-flex min-h-11 items-center gap-3 py-2 text-[10px] font-medium uppercase text-bone-dim transition-colors hover:text-blood"
                                href="/news"
                                prefetch={['hover']}
                            >
                                <span
                                    aria-hidden="true"
                                    className="block h-px w-8 bg-blood"
                                />
                                Πίσω στα νέα
                            </Link>
                            <p className="mt-8 text-[10px] font-medium uppercase text-blood">
                                <time dateTime={article.publishedAt}>
                                    {article.date}
                                </time>
                            </p>
                        </div>

                        <h1 className="mt-6 max-w-[min(100%,22ch)] [overflow-wrap:normal] [word-break:normal] font-display text-[clamp(2.45rem,9.4vw,8.2rem)] font-black uppercase leading-[0.88] text-bone sm:text-[clamp(3.8rem,7.8vw,8.8rem)] sm:leading-[0.8]">
                            {article.title}
                        </h1>

                        <div
                            className="mt-8 grid gap-5 border-t border-white/20 pt-5 sm:grid-cols-[minmax(0,48rem)_auto] sm:items-end sm:justify-between"
                            data-article-section
                        >
                            <p className="max-w-3xl text-sm leading-6 text-bone/76 sm:text-lg sm:leading-8">
                                {article.excerpt}
                            </p>
                            <p className="text-[9px] uppercase leading-5 text-bone/50">
                                Μαχητές Ελευθερούπολης
                                <br />
                                Αρχείο ομάδας
                            </p>
                        </div>
                    </div>
                </section>

                <section className="border-b border-line bg-ink-1 px-5 py-20 sm:px-8 sm:py-28 lg:px-12 lg:py-36">
                    <div className="mx-auto max-w-[1400px]">
                        <div className="mb-8 flex items-end justify-between gap-6 border-b border-blood pb-5">
                            <p className="text-[10px] font-medium uppercase text-blood">
                                Η εικόνα της ιστορίας
                            </p>
                            <p className="hidden text-[9px] uppercase text-pewter sm:block">
                                Πλήρες κάδρο
                            </p>
                        </div>
                        <div
                            className="overflow-hidden border border-line-strong bg-ink-2"
                            data-article-section
                        >
                            {hasCover ? (
                                <Photo
                                    alt={coverAlt}
                                    height={article.coverImageHeight}
                                    image={article.coverImage}
                                    imageName={article.coverImageName}
                                    mode="natural"
                                    slot="full"
                                    width={article.coverImageWidth}
                                />
                            ) : (
                                <CoverFallback />
                            )}
                        </div>
                    </div>
                </section>

                <section className="border-b border-line bg-ink-0 px-5 py-24 sm:px-8 sm:py-32 lg:px-12 lg:py-40">
                    <div className="mx-auto grid max-w-[1200px] gap-12 lg:grid-cols-[12rem_minmax(0,48rem)] lg:gap-20">
                        <aside
                            className="lg:sticky lg:top-28 lg:self-start"
                            data-article-section
                        >
                            <p className="text-[10px] font-medium uppercase text-blood">
                                Η ιστορία
                            </p>
                            <p className="mt-5 border-t border-line-strong pt-5 text-[9px] uppercase leading-5 text-pewter">
                                {article.date}
                                <br />
                                Ελευθερούπολη
                            </p>
                        </aside>
                        <div
                            className="article-content min-w-0"
                            dangerouslySetInnerHTML={{
                                __html: article.bodyHtml,
                            }}
                            data-article-section
                        />
                    </div>
                </section>

                {gallery.length ? (
                    <section className="border-b border-line bg-ink-1 px-5 py-24 sm:px-8 sm:py-32 lg:px-12 lg:py-40">
                        <div className="mx-auto max-w-[1400px]">
                            <header className="grid gap-8 border-b border-blood pb-9 lg:grid-cols-[minmax(0,1fr)_auto] lg:items-end">
                                <div>
                                    <p className="text-[10px] font-medium uppercase text-blood">
                                        Ολόκληρη η στιγμή
                                    </p>
                                    <h2 className="mt-5 font-display text-[clamp(3.5rem,13vw,5rem)] font-black uppercase leading-[0.84] text-bone sm:text-[clamp(4.5rem,10vw,9rem)] sm:leading-[0.78]">
                                        Φωτογραφίες
                                    </h2>
                                </div>
                                <p className="max-w-sm text-sm leading-7 text-bone-dim">
                                    Κάθε εικόνα παρουσιάζεται στο πλήρες κάδρο
                                    της. Επίλεξε μία φωτογραφία για προβολή.
                                </p>
                            </header>
                            <ArticleGallery
                                fallbackAlt={article.title}
                                images={gallery}
                            />
                        </div>
                    </section>
                ) : null}

                {videos.length ? (
                    <section className="border-b border-line bg-ink-0 px-5 py-24 sm:px-8 sm:py-32 lg:px-12 lg:py-40">
                        <div className="mx-auto max-w-[1200px]">
                            <p className="text-[10px] font-medium uppercase text-blood">
                                Από το ρινγκ
                            </p>
                            <h2 className="mt-5 font-display text-[clamp(3.5rem,13vw,5rem)] font-black uppercase leading-[0.84] text-bone sm:text-[clamp(4.5rem,10vw,9rem)] sm:leading-[0.78]">
                                Βίντεο
                            </h2>
                            <div className="mt-12 space-y-8">
                                {videos.map((video, index) => (
                                    <div
                                        className="grid gap-5 lg:grid-cols-[5rem_minmax(0,1fr)]"
                                        key={video.id}
                                    >
                                        <p className="font-display text-5xl leading-none text-blood">
                                            {String(index + 1).padStart(2, '0')}
                                        </p>
                                        <YoutubeEmbed
                                            title={article.title}
                                            youtubeId={video.youtubeId}
                                        />
                                    </div>
                                ))}
                            </div>
                        </div>
                    </section>
                ) : null}

                <section className="grain relative isolate min-h-[76svh] overflow-hidden">
                    <SiteImage
                        alt=""
                        aria-hidden="true"
                        className="absolute inset-0 -z-20 h-full w-full object-cover object-center grayscale contrast-125"
                        image="schedule-hero"
                        slot="hero"
                    />
                    <div className="absolute inset-0 -z-10 bg-[linear-gradient(180deg,rgba(5,5,5,.22),rgba(5,5,5,.94))]" />
                    <div className="mx-auto flex min-h-[76svh] max-w-[1600px] flex-col items-start justify-end px-5 pb-14 sm:px-8 sm:pb-20 lg:px-12 lg:pb-24">
                        <p className="text-[10px] font-medium uppercase text-blood">
                            Συνέχισε στο αρχείο
                        </p>
                        <h2 className="mt-5 max-w-[14ch] font-display text-[clamp(3.5rem,13vw,5rem)] font-black uppercase leading-[0.84] text-bone sm:text-[clamp(4.5rem,10vw,10rem)] sm:leading-[0.78]">
                            Κάθε ιστορία ξεκινά από την ομάδα.
                        </h2>
                        <div className="mt-8 flex flex-wrap gap-4">
                            <Link
                                className="btn-sweep text-blood"
                                href="/news"
                                prefetch={['hover']}
                            >
                                Όλα τα νέα <span aria-hidden="true">↗</span>
                            </Link>
                            <Link
                                className="btn-sweep text-bone"
                                href="/schedule"
                                prefetch={['hover']}
                            >
                                Δες το πρόγραμμα <span aria-hidden="true">↗</span>
                            </Link>
                        </div>
                    </div>
                </section>

                <section className="border-t border-line bg-ink-0 px-5 py-10 sm:px-8 lg:px-12">
                    <div className="mx-auto max-w-[1500px]">
                        <Link
                            className="inline-flex min-h-11 items-center gap-3 py-2 text-[10px] font-medium uppercase text-bone transition-colors hover:text-blood"
                            href="/news"
                            prefetch={['hover']}
                        >
                            <span
                                aria-hidden="true"
                                className="block h-px w-12 bg-current transition-all duration-300"
                            />
                            Επιστροφή στα νέα
                        </Link>
                    </div>
                </section>
            </article>
        </div>
    );
}
