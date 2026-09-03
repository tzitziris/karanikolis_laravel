import { Link } from '@inertiajs/react';
import { useRef } from 'react';
import SiteImage from '../Components/SiteImage';
import { useHomePageAnimation } from '../animation/useHomePageAnimation';

const stats = [
    ['15+', 'χρόνια παρουσίας'],
    ['240', 'αθλητές & αθλήτριες'],
    ['18', 'μαθήματα την εβδομάδα'],
];

const chapters = [
    {
        body: 'Η τεχνική ξεκινά πριν από το πρώτο χτύπημα. Στάση, ισορροπία, ακρίβεια.',
        image: 'ring-training',
        imageAlt: 'Αθλητές kickboxing προπονούνται μέσα στο ρινγκ',
        number: '01',
        title: 'Μάθε να στέκεσαι.',
    },
    {
        body: 'Η πειθαρχία μετατρέπει την επανάληψη σε αυτοπεποίθηση και την προσπάθεια σε πρόοδο.',
        image: 'pad-work',
        imageAlt: 'Αθλητής εξασκεί χτυπήματα με την προπονήτριά του',
        number: '02',
        title: 'Μάθε να επιμένεις.',
    },
    {
        body: 'Με έλεγχο, σεβασμό και καθαρό μυαλό. Μέσα στο ρινγκ και έξω από αυτό.',
        image: 'sparring',
        imageAlt: 'Δύο αθλητές εξασκούν τεχνικές kickboxing',
        number: '03',
        title: 'Μάθε να μάχεσαι.',
    },
];

function HomeHero() {
    return (
        <section className="grain relative isolate min-h-[calc(100dvh-5rem)] overflow-hidden bg-ink-0">
            <SiteImage
                alt="Δύο αθλητές kickboxing σε δυναμική προπόνηση"
                className="absolute inset-0 -z-30 h-full w-full object-cover object-[58%_center]"
                data-home-hero-image
                image="hero-kickboxing"
                priority
                slot="hero"
            />
            <div className="absolute inset-0 -z-20 bg-[linear-gradient(180deg,rgba(0,0,0,.22),rgba(0,0,0,.1)_36%,rgba(0,0,0,.86)_100%)]" />
            <div className="absolute inset-0 -z-10 bg-[radial-gradient(circle_at_78%_28%,rgba(212,161,66,.18),transparent_35%)]" />

            <div
                className="mx-auto flex min-h-[calc(100dvh-5rem)] max-w-[1600px] flex-col justify-end px-5 pb-8 pt-20 sm:px-8 sm:pb-10 lg:px-12 lg:pb-12"
                data-home-hero-content
            >
                <p
                    className="mb-5 text-xs font-medium uppercase text-bone/80 sm:mb-7"
                    data-home-reveal
                >
                    Kickboxing · Ελευθερούπολη
                </p>
                <h1
                    className="max-w-[13ch] font-display text-[clamp(4.7rem,15.4vw,14.8rem)] font-black uppercase leading-[0.73] text-blood [text-shadow:0_10px_60px_rgba(0,0,0,.55)]"
                    data-home-reveal
                >
                    Μαχητές
                    <span className="block text-[clamp(2.65rem,8.3vw,8rem)] text-bone">
                        Ελευθερούπολης
                    </span>
                </h1>

                <div
                    className="mt-7 flex flex-col gap-6 border-t border-white/20 pt-5 sm:flex-row sm:items-end sm:justify-between"
                    data-home-reveal
                >
                    <p className="max-w-md text-sm leading-6 text-bone/80 sm:text-base">
                        Η δύναμη δεν χαρίζεται. Χτίζεται, μία προπόνηση τη
                        φορά.
                    </p>
                    <div className="flex items-center justify-between gap-6 sm:justify-end">
                        <Link className="cinematic-link" href="/schedule">
                            Κλείσε δοκιμαστική προπόνηση
                            <span aria-hidden="true">↗</span>
                        </Link>
                        <span className="scroll-cue hidden text-[10px] uppercase text-bone/55 sm:block">
                            Κύλησε
                        </span>
                    </div>
                </div>
            </div>
        </section>
    );
}

function ManifestoSection() {
    return (
        <section className="grain relative isolate overflow-clip bg-ink-0">
            <div className="absolute inset-0 lg:hidden">
                <SiteImage
                    alt="Δύο αθλητές kickboxing προπονούνται με ένταση και έλεγχο"
                    className="h-full w-full object-cover object-[62%_center]"
                    data-home-statement-image
                    image="sparring"
                    slot="full"
                />
                <div className="absolute inset-0 bg-[linear-gradient(180deg,rgba(5,5,5,.8),rgba(5,5,5,.45)_42%,rgba(5,5,5,.92))]" />
            </div>

            <div className="relative mx-auto grid max-w-[1600px] lg:grid-cols-2">
                <div className="relative z-10 px-5 sm:px-8 lg:px-12 lg:pr-16">
                    <div className="flex min-h-[100dvh] flex-col justify-center py-28">
                        <span
                            aria-hidden="true"
                            className="mb-8 block h-px w-20 bg-blood"
                        />
                        <h2
                            className="max-w-[13ch] font-display text-[clamp(3.2rem,13.2vw,4.25rem)] font-black uppercase leading-[0.84] text-bone lg:text-[clamp(4.25rem,5vw,6rem)]"
                            data-home-reveal
                        >
                            <span className="text-blood">
                                Δεν είναι απλώς άθλημα.
                            </span>{' '}
                            Είναι ο τρόπος που επιλέγεις να στέκεσαι.
                        </h2>
                    </div>

                    <div className="grid min-h-[58dvh] content-center gap-8 border-t border-white/20 py-20 sm:grid-cols-3 lg:min-h-[50dvh] lg:grid-cols-1 lg:gap-10">
                        {stats.map(([value, label]) => (
                            <div
                                className="grid grid-cols-[6rem_1fr] items-end gap-4 lg:grid-cols-[8rem_1fr]"
                                data-home-reveal
                                key={label}
                            >
                                <p className="font-display text-6xl font-black leading-none text-blood sm:text-7xl lg:text-8xl">
                                    {value}
                                </p>
                                <p className="pb-1 text-xs uppercase text-bone/75">
                                    {label}
                                </p>
                            </div>
                        ))}
                    </div>
                </div>

                <div className="relative hidden lg:block">
                    <div className="sticky top-20 h-[calc(100dvh-5rem)] overflow-hidden">
                        <SiteImage
                            alt="Δύο αθλητές kickboxing προπονούνται με ένταση και έλεγχο"
                            className="h-full w-full object-cover object-center"
                            data-home-statement-image
                            image="sparring"
                            slot="half"
                        />
                        <div className="absolute inset-0 bg-[linear-gradient(90deg,rgba(5,5,5,.32),transparent_35%),linear-gradient(180deg,rgba(5,5,5,.14),rgba(5,5,5,.5))]" />
                        <div className="absolute inset-y-0 left-0 w-px bg-blood/55" />
                    </div>
                </div>
            </div>
        </section>
    );
}

function JourneySection() {
    return (
        <section className="relative bg-ink-0">
            <div className="sm:motion-safe:hidden">
                <p className="px-5 py-6 text-[10px] font-medium uppercase text-blood">
                    Η διαδρομή
                </p>
                {chapters.map((chapter) => (
                    <article
                        className="grain relative isolate flex min-h-[78svh] items-end overflow-hidden border-t border-line px-5 pb-12 pt-28"
                        key={chapter.number}
                    >
                        <SiteImage
                            alt={chapter.imageAlt}
                            className="absolute inset-0 -z-20 h-full w-full object-cover"
                            image={chapter.image}
                            slot="full"
                        />
                        <div className="absolute inset-0 -z-10 bg-[linear-gradient(180deg,rgba(5,5,5,.16),rgba(5,5,5,.94)_88%)]" />
                        <div data-home-reveal>
                            <span className="font-display text-5xl font-black text-blood">
                                {chapter.number}
                            </span>
                            <h2 className="mt-3 max-w-full font-display text-[clamp(3.45rem,15vw,4rem)] font-black uppercase leading-[0.84] text-bone">
                                {chapter.title}
                            </h2>
                            <p className="mt-6 max-w-md text-sm leading-6 text-bone/75">
                                {chapter.body}
                            </p>
                        </div>
                    </article>
                ))}
            </div>

            <div
                className="relative hidden h-[100dvh] w-full max-w-full overflow-hidden sm:motion-safe:block"
                data-home-journey-desktop
            >
                <div className="flex h-full w-max" data-home-journey-track>
                    {chapters.map((chapter) => (
                        <article
                            className="grain relative isolate flex h-[100dvh] w-screen shrink-0 items-end overflow-hidden px-8 pb-16 pt-24 lg:items-center lg:px-12"
                            key={chapter.number}
                        >
                            <div
                                className="absolute -inset-x-[5%] inset-y-0 -z-20"
                                data-home-journey-image
                            >
                                <SiteImage
                                    alt={chapter.imageAlt}
                                    className="h-full w-full object-cover"
                                    image={chapter.image}
                                    slot="full"
                                />
                            </div>
                            <div className="absolute inset-0 -z-10 bg-[linear-gradient(90deg,rgba(0,0,0,.94)_0%,rgba(0,0,0,.72)_42%,rgba(0,0,0,.18)_82%,rgba(0,0,0,.42)_100%)]" />
                            <div className="absolute inset-x-0 bottom-0 -z-10 h-1/2 bg-[linear-gradient(0deg,rgba(0,0,0,.7),transparent)]" />

                            <p className="absolute left-8 top-8 text-[10px] font-medium uppercase text-blood lg:left-12">
                                Η διαδρομή
                            </p>

                            <div className="relative z-10 mx-auto w-full max-w-[1600px]">
                                <div className="max-w-3xl" data-home-reveal>
                                    <span className="font-display text-7xl font-black leading-none text-blood">
                                        {chapter.number}
                                    </span>
                                    <h2 className="mt-3 max-w-[9ch] font-display text-[clamp(4rem,10vw,9rem)] font-black uppercase leading-[0.78] text-bone [text-shadow:0_8px_40px_rgba(0,0,0,.8)]">
                                        {chapter.title}
                                    </h2>
                                    <p className="mt-6 max-w-md text-base leading-7 text-bone/80">
                                        {chapter.body}
                                    </p>
                                </div>
                            </div>

                            <div
                                aria-hidden="true"
                                className="absolute bottom-7 right-8 flex items-center gap-3 font-mono text-[9px] text-bone/55 lg:right-12"
                            >
                                <span className="text-blood">
                                    {chapter.number}
                                </span>
                                <span className="h-px w-14 bg-blood" />
                                <span>03</span>
                            </div>
                        </article>
                    ))}
                </div>
            </div>
        </section>
    );
}

function TestimonialSection() {
    return (
        <section className="relative overflow-hidden bg-blood px-5 py-28 text-ink-0 sm:px-8 sm:py-40 lg:px-12 lg:py-52">
            <div
                aria-hidden="true"
                className="absolute -right-[.08em] -top-[.25em] font-display text-[clamp(18rem,45vw,42rem)] font-black leading-none text-black/7"
            >
                Μ
            </div>
            <div className="relative mx-auto max-w-[1500px]">
                <p
                    className="text-[10px] font-semibold uppercase"
                    data-home-reveal
                >
                    Η νοοτροπία μας
                </p>
                <blockquote
                    className="mt-8 max-w-[18ch] font-display text-[clamp(3.1rem,14vw,3.6rem)] font-black uppercase leading-[0.9] sm:text-[clamp(3.6rem,7.5vw,7rem)] sm:leading-[0.84]"
                    data-home-reveal
                >
                    Δεν ψάχνουμε τον εύκολο δρόμο. Χτίζουμε τον δυνατό άνθρωπο.
                </blockquote>
                <p
                    className="mt-10 text-xs font-semibold uppercase"
                    data-home-reveal
                >
                    Παναγιώτης Καρανικολής · Προπονητής
                </p>
            </div>
        </section>
    );
}

function LatestNewsSection({ articles = [] }) {
    const hasArticles = articles.length > 0;

    return (
        <section className="bg-ink-0 py-28 sm:py-40 lg:py-48">
            <div className="mx-auto max-w-[1500px] px-5 sm:px-8 lg:px-12">
                <div className="mb-12 grid grid-cols-1 items-end gap-6 lg:mb-16 lg:grid-cols-[1fr_auto]">
                    <div data-home-reveal>
                        <p className="text-[10px] font-medium uppercase text-blood">
                            Από τη σχολή
                        </p>
                        <h2 className="mt-5 font-display text-[clamp(4.5rem,10vw,9rem)] font-black uppercase leading-[0.8] text-bone">
                            Τελευταία Νέα
                        </h2>
                    </div>
                    <div data-home-reveal>
                        <Link
                            className="group inline-flex min-h-11 items-center gap-3 py-2 text-xs font-medium uppercase text-bone transition-colors duration-300 hover:text-blood"
                            href="/news"
                            prefetch={['mount', 'hover']}
                        >
                            <span className="block h-px w-8 bg-current transition-all duration-300 group-hover:w-14" />
                            Όλα τα νέα
                        </Link>
                    </div>
                </div>

                {hasArticles ? (
                    <div className="grid gap-4 md:grid-cols-3">
                        {articles.slice(0, 3).map((article) => (
                            <article
                                className="border border-line-strong bg-ink-2 p-5"
                                data-home-reveal
                                key={article.href ?? article.slug ?? article.title}
                            >
                                <p className="font-mono text-[10px] uppercase text-pewter-dim">
                                    {article.date ?? 'Νέα σχολής'}
                                </p>
                                <h3 className="mt-4 font-display text-3xl font-black uppercase leading-none text-bone">
                                    {article.title}
                                </h3>
                                {article.excerpt ? (
                                    <p className="mt-4 text-sm leading-6 text-bone-dim">
                                        {article.excerpt}
                                    </p>
                                ) : null}
                            </article>
                        ))}
                    </div>
                ) : (
                    <div
                        className="border border-line-strong bg-ink-2 px-5 py-8 sm:px-8"
                        data-home-reveal
                    >
                        <p className="font-display text-3xl font-black uppercase text-bone">
                            Δεν υπάρχουν δημοσιευμένα νέα ακόμη.
                        </p>
                        <p className="mt-3 max-w-2xl text-sm leading-6 text-bone-dim">
                            Όταν ανέβουν οι πρώτες ανακοινώσεις της σχολής, οι
                            τρεις νεότερες θα εμφανίζονται εδώ.
                        </p>
                    </div>
                )}
            </div>
        </section>
    );
}

function SweepLink({ children, href, variant }) {
    const variantClass = variant === 'blood' ? 'btn-blood' : 'btn-bone';

    return (
        <Link
            className={`btn-sweep ${variantClass}`}
            href={href}
            prefetch={['mount', 'hover']}
        >
            <span className="btn-label">{children}</span>
            <span aria-hidden="true" className="btn-arrow">
                →
            </span>
        </Link>
    );
}

function FinalCallSection() {
    return (
        <section className="relative min-h-[88dvh] overflow-hidden bg-ink-0">
            <div className="absolute inset-0" data-home-final-image>
                <SiteImage
                    alt="Προπόνηση kickboxing με στόχους"
                    className="h-full w-full object-cover object-center"
                    image="pad-work"
                    slot="full"
                />
                <div className="absolute inset-0 bg-[linear-gradient(180deg,rgba(0,0,0,.25),rgba(0,0,0,.85))]" />
            </div>
            <div
                className="relative mx-auto flex min-h-[88dvh] max-w-[1600px] flex-col items-start justify-end px-5 pb-14 sm:px-8 sm:pb-20 lg:px-12 lg:pb-24"
                data-home-final-copy
            >
                <p className="text-[10px] font-medium uppercase text-blood">
                    Η πρώτη προπόνηση ξεκινά εδώ
                </p>
                <h2 className="mt-5 max-w-[12ch] font-display text-[clamp(4.5rem,10vw,10rem)] font-black uppercase leading-[0.78] text-bone">
                    Μπες στο ρινγκ.
                </h2>
                <div className="mt-8 flex flex-wrap gap-4">
                    <SweepLink href="/schedule" variant="blood">
                        Δες το πρόγραμμα
                    </SweepLink>
                    <SweepLink href="/coaches" variant="bone">
                        Γνώρισε την ομάδα
                    </SweepLink>
                </div>
            </div>
        </section>
    );
}

export default function Home({ articles = [] }) {
    const pageRef = useRef(null);

    useHomePageAnimation(pageRef);

    return (
        <div className="bg-ink-0" ref={pageRef}>
            <HomeHero />
            <ManifestoSection />
            <JourneySection />
            <TestimonialSection />
            <LatestNewsSection articles={articles} />
            <FinalCallSection />
        </div>
    );
}
