import { Link } from '@inertiajs/react';
import { useRef } from 'react';
import ArticleGrid from '../Components/News/ArticleGrid';
import SiteImage from '../Components/SiteImage';
import { useNewsPageAnimation } from '../animation/useNewsPageAnimation';

function pageHref(page) {
    return page <= 1 ? '/news' : `/news?page=${page}`;
}

function Pagination({ pagination }) {
    if (pagination.lastPage <= 1) {
        return null;
    }

    const pages = Array.from({ length: pagination.lastPage }, (_, index) => index + 1);

    return (
        <nav
            aria-label="Σελιδοποίηση αρχείου νέων"
            className="mt-12 flex flex-col gap-4 border-t border-line pt-8 sm:flex-row sm:items-center sm:justify-between"
        >
            <p className="font-mono text-[10px] uppercase text-pewter">
                {pagination.from}-{pagination.to} από {pagination.total}
            </p>
            <div className="flex flex-wrap items-center gap-2">
                {pagination.currentPage > 1 ? (
                    <Link
                        className="inline-flex min-h-11 items-center border border-line-strong px-4 text-[10px] font-semibold uppercase text-bone transition-colors hover:border-blood hover:text-blood"
                        href={pageHref(pagination.currentPage - 1)}
                        preserveScroll
                    >
                        Προηγούμενη
                    </Link>
                ) : null}

                {pages.map((page) => (
                    <Link
                        aria-current={page === pagination.currentPage ? 'page' : undefined}
                        className={`inline-flex h-11 min-w-11 items-center justify-center border px-3 font-mono text-xs transition-colors ${
                            page === pagination.currentPage
                                ? 'border-blood bg-blood text-ink-0'
                                : 'border-line-strong text-bone hover:border-blood hover:text-blood'
                        }`}
                        href={pageHref(page)}
                        key={page}
                        preserveScroll
                    >
                        {page}
                    </Link>
                ))}

                {pagination.currentPage < pagination.lastPage ? (
                    <Link
                        className="inline-flex min-h-11 items-center border border-line-strong px-4 text-[10px] font-semibold uppercase text-bone transition-colors hover:border-blood hover:text-blood"
                        href={pageHref(pagination.currentPage + 1)}
                        preserveScroll
                    >
                        Επόμενη
                    </Link>
                ) : null}
            </div>
        </nav>
    );
}

export default function News({ articles = [], pagination }) {
    const pageRef = useRef(null);
    const total = pagination?.total ?? articles.length;

    useNewsPageAnimation(pageRef);

    return (
        <div className="w-full max-w-full overflow-x-clip bg-ink-0" ref={pageRef}>
            <section className="grain relative isolate min-h-[calc(100dvh-5rem)] overflow-hidden bg-ink-0">
                <SiteImage
                    alt="Αθλητές kickboxing σε έντονη προπόνηση"
                    className="absolute inset-0 -z-30 h-full w-full object-cover object-[58%_center] saturate-[.65] contrast-125 lg:object-[center_44%]"
                    data-news-hero-image
                    image="ring-training"
                    priority
                    slot="hero"
                />
                <div className="absolute inset-0 -z-20 bg-[linear-gradient(90deg,rgba(5,5,5,.97)_0%,rgba(5,5,5,.74)_48%,rgba(5,5,5,.14)_100%)]" />
                <div className="absolute inset-0 -z-10 bg-[linear-gradient(180deg,rgba(5,5,5,.03),rgba(5,5,5,.9)_100%)]" />

                <div
                    className="mx-auto flex min-h-[calc(100dvh-5rem)] max-w-[1600px] flex-col justify-end px-5 pb-8 pt-24 sm:px-8 sm:pb-10 lg:px-12 lg:pb-12"
                    data-news-hero-content
                >
                    <p className="mb-5 text-[10px] font-medium uppercase text-blood">
                        Αγώνες · Διακρίσεις · Ανακοινώσεις
                    </p>
                    <h1 className="max-w-full font-display text-[clamp(4rem,17vw,5.3rem)] font-black uppercase leading-[0.82] text-bone sm:max-w-[15ch] sm:text-[clamp(4.6rem,14vw,13.6rem)] sm:leading-[0.74]">
                        Νέα
                        <span className="block text-blood">&amp; Ιστορίες</span>
                    </h1>
                    <div className="mt-7 grid gap-5 border-t border-white/20 pt-5 sm:grid-cols-[minmax(0,36rem)_auto] sm:items-end sm:justify-between">
                        <p className="max-w-xl text-sm leading-6 text-bone/78 sm:text-base">
                            Από το ρινγκ και την καθημερινή προπόνηση. Όσα
                            χτίζει η ομάδα, καταγράφονται εδώ.
                        </p>
                        <p className="text-[10px] uppercase leading-5 text-bone/55">
                            Αρχείο · {String(total).padStart(2, '0')} ιστορίες
                            <br />
                            Ελευθερούπολη · Καβάλα
                        </p>
                    </div>
                </div>
            </section>

            <section className="relative overflow-hidden px-5 py-24 sm:px-8 sm:py-32 lg:px-12 lg:py-40">
                <div
                    aria-hidden="true"
                    className="absolute -right-[.06em] top-[-.1em] font-display text-[clamp(18rem,44vw,42rem)] font-black leading-none text-blood/[.035]"
                >
                    Ν
                </div>
                <div className="relative mx-auto max-w-[1500px]">
                    <p className="text-[10px] font-medium uppercase text-blood">
                        Το αρχείο της ομάδας
                    </p>
                    <h2 className="mt-7 max-w-[20ch] font-display text-[clamp(3.35rem,14vw,4rem)] font-black uppercase leading-[0.88] text-bone sm:text-[clamp(3.65rem,8.5vw,8.5rem)] sm:leading-[0.82]">
                        Κάθε αγώνας αφήνει ένα αποτέλεσμα. Κάθε προπόνηση
                        αφήνει μια ιστορία.
                    </h2>
                </div>
            </section>

            <section className="border-b border-t border-line bg-ink-1 px-5 py-20 sm:px-8 sm:py-28 lg:px-12 lg:py-36">
                <div className="mx-auto max-w-[1500px]">
                    <header className="mb-12 grid gap-8 border-b border-blood pb-9 sm:mb-16 lg:grid-cols-[minmax(0,1fr)_auto] lg:items-end">
                        <div>
                            <p className="text-[10px] font-medium uppercase text-blood">
                                Νεότερες δημοσιεύσεις
                            </p>
                            <h2 className="mt-5 max-w-full font-display text-[clamp(3.6rem,15vw,4.5rem)] font-black uppercase leading-[0.84] text-bone sm:max-w-[10ch] sm:text-[clamp(4.5rem,10vw,9rem)] sm:leading-[0.78]">
                                Από τη σχολή.
                            </h2>
                        </div>
                        <p className="max-w-sm text-sm leading-7 text-bone-dim sm:text-base">
                            Νέα από διοργανώσεις, συμμετοχές, σεμινάρια και την
                            καθημερινή εξέλιξη των αθλητών μας.
                        </p>
                    </header>

                    <ArticleGrid
                        articles={articles}
                        emptyMessage="Δεν υπάρχουν δημοσιευμένα νέα ακόμη."
                        revealAttribute="data-news-card"
                    />

                    <Pagination pagination={pagination ?? { lastPage: 1 }} />
                </div>
            </section>
        </div>
    );
}
