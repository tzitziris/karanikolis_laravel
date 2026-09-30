import { Link } from '@inertiajs/react';
import { useRef } from 'react';
import SiteImage from '../Components/SiteImage';
import { useCoachesPageAnimation } from '../animation/useCoachesPageAnimation';

const athletePlaceholders = [
    {
        detail: 'Τα επίσημα στοιχεία θα προστεθούν όταν επιβεβαιωθούν από τη σχολή.',
        image: 'athlete-padwork',
        imageAlt: 'Αθλητής kickboxing εξασκεί γόνατο με στόχους',
        marker: '01',
        title: 'Θέση αθλητή',
        trainingFocus: 'Στόχοι και τεχνική ακρίβεια',
    },
    {
        detail: 'Τα επίσημα στοιχεία θα προστεθούν όταν επιβεβαιωθούν από τη σχολή.',
        image: 'athlete-bag',
        imageAlt: 'Αθλητής kickboxing προπονείται σε σάκο',
        marker: '02',
        title: 'Θέση αθλήτριας',
        trainingFocus: 'Σάκος και φυσική κατάσταση',
    },
    {
        detail: 'Τα επίσημα στοιχεία θα προστεθούν όταν επιβεβαιωθούν από τη σχολή.',
        image: 'athlete-kick',
        imageAlt: 'Αθλητής kickboxing εκτελεί ψηλό λάκτισμα',
        marker: '03',
        title: 'Θέση αθλητή',
        trainingFocus: 'Λακτίσματα και κινητικότητα',
    },
    {
        detail: 'Τα επίσημα στοιχεία θα προστεθούν όταν επιβεβαιωθούν από τη σχολή.',
        image: 'athlete-sparring',
        imageAlt: 'Δύο αθλητές kickboxing σε δυναμική προπόνηση',
        marker: '04',
        title: 'Θέση αθλήτριας',
        trainingFocus: 'Sparring και αγωνιστικός ρυθμός',
    },
];

const coachMetrics = [
    { label: 'Αγώνες', value: '86' },
    { label: 'Νίκες', value: '81', highlight: true },
    { label: 'Ήττες', value: '5' },
    { label: 'Νοκ άουτ', value: '27' },
];

const coachAchievements = [
    { title: 'Παγκόσμιος πρωταθλητής', year: '2005', location: 'Κύπρος' },
    { title: 'Παγκόσμιος πρωταθλητής', year: '2000', location: 'Γαλλία' },
    { title: 'Πρωταθλητής Ευρώπης', year: '1999' },
    { title: 'Βαλκανιονίκης', year: '1997' },
    {
        title: 'Πρωταθλητής Ελλάδος',
        year: '1995–1999',
        detail: '1995, 1996, 1997, 1998, 1999',
    },
];

const cardSpans = [
    'lg:col-span-7',
    'lg:col-span-5',
    'lg:col-span-5',
    'lg:col-span-7',
];

const gallery = [
    {
        alt: 'Αθλητής kickboxing προπονείται σε σάκο',
        image: 'athlete-bag',
        label: 'Σάκος',
    },
    {
        alt: 'Αθλητής kickboxing εξασκεί γόνατο με στόχους',
        image: 'athlete-padwork',
        label: 'Στόχοι',
    },
    {
        alt: 'Αθλητής kickboxing εκτελεί ψηλό λάκτισμα',
        image: 'athlete-kick',
        label: 'Λάκτισμα',
    },
    {
        alt: 'Δύο αθλητές kickboxing σε δυναμική προπόνηση',
        image: 'athlete-sparring',
        label: 'Sparring',
    },
];

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

function CoachesHero() {
    return (
        <section
            className="grain relative isolate min-h-[calc(100svh-5rem)] overflow-hidden bg-ink-0"
            data-coaches-hero
        >
            <SiteImage
                alt="Αθλητής σηκώνει τα χέρια του στη νίκη, με τον διαιτητή δίπλα του"
                className="absolute inset-0 -z-30 h-full w-full object-cover object-[87%_center] saturate-[.72] contrast-125 lg:object-[center_70%]"
                data-coaches-hero-image
                image="coaches-hero"
                priority
                slot="hero"
            />
            <div className="absolute inset-0 -z-20 bg-[linear-gradient(90deg,rgba(5,5,5,.82)_0%,rgba(5,5,5,.45)_48%,rgba(5,5,5,.05)_100%)]" />
            <div className="absolute inset-0 -z-10 bg-[linear-gradient(180deg,transparent_35%,rgba(5,5,5,.78)_100%)]" />

            <div
                className="mx-auto flex min-h-[calc(100svh-5rem)] max-w-[1600px] flex-col justify-end px-5 pb-8 pt-24 sm:px-8 sm:pb-10 lg:px-12 lg:pb-12"
                data-coaches-hero-content
            >
                <p
                    className="mb-5 text-[10px] font-medium uppercase text-blood"
                    data-coaches-reveal
                >
                    Η εμπειρία οδηγεί · Η συνέπεια χτίζει
                </p>
                <h1
                    className="max-w-[68rem] font-display text-[clamp(3rem,14.3vw,3.5rem)] font-black uppercase leading-[0.74] text-bone [text-shadow:0_6px_40px_rgba(0,0,0,.35)] sm:text-[clamp(3.5rem,9.5vw,9.5rem)]"
                    data-coaches-reveal
                >
                    Προπονητές
                    <span className="block text-[clamp(3rem,8vw,8rem)] text-blood sm:text-[clamp(2.5rem,6.4vw,6.4rem)]">
                        &amp; Αθλητές
                    </span>
                </h1>
                <div
                    className="mt-7 grid gap-5 border-t border-white/20 pt-5 sm:grid-cols-[minmax(0,34rem)_auto] sm:items-end sm:justify-between"
                    data-coaches-reveal
                >
                    <p className="max-w-lg text-sm leading-6 text-bone/80 sm:text-base">
                        Μια ομάδα που προπονείται με κοινό στόχο και
                        εξελίσσεται μαζί.
                    </p>
                    <p className="text-[10px] uppercase leading-5 text-bone/60 sm:[text-align:right]">
                        Ελευθερούπολη · Καβάλα
                        <br />
                        Αγωνιστική περίοδος · 2025/26
                    </p>
                </div>
            </div>
        </section>
    );
}

function CoachSection() {
    return (
        <section
            aria-labelledby="coach-name"
            className="relative mx-auto grid max-w-[1600px] bg-ink-0 lg:grid-cols-2"
        >
            <div className="coaches-sticky relative h-[72svh] min-h-[34rem] overflow-hidden lg:sticky lg:top-20 lg:h-[calc(100svh-5rem)]">
                <SiteImage
                    alt="Αθλητής kickboxing εξασκεί τεχνική σε σάκο"
                    className="h-full w-full object-cover object-top grayscale contrast-125"
                    data-coach-image
                    image="coach-portrait"
                    slot="half"
                />
                <div className="absolute inset-0 bg-[linear-gradient(180deg,transparent_40%,rgba(5,5,5,.85)),linear-gradient(90deg,rgba(212,161,66,.18),transparent_40%)]" />
                <div className="absolute inset-x-5 bottom-6 flex items-end justify-between border-t border-white/25 pt-4 sm:inset-x-8 lg:inset-x-12">
                    <p className="text-[10px] uppercase text-bone/75">
                        Κύριος Προπονητής
                    </p>
                    <p className="font-display text-6xl leading-none text-blood">
                        ΠΚ
                    </p>
                </div>
            </div>

            <div className="@container flex min-w-0 flex-col justify-center px-5 py-16 sm:px-8 sm:py-24 lg:px-12 xl:px-16">
                <p
                    className="text-xs font-medium uppercase tracking-wider text-blood"
                    data-coaches-reveal
                >
                    Καθοδήγηση με ακρίβεια
                </p>
                <h2
                    className="mt-5 max-w-full font-display text-[min(13cqw,5.5rem)] font-black uppercase leading-[0.95] text-bone"
                    data-coaches-reveal
                    id="coach-name"
                >
                    <span className="block overflow-visible">Παναγιώτης</span>
                    <span className="block overflow-visible text-bone-dim">
                        Καρανικολής
                    </span>
                </h2>

                <p
                    className="mt-6 flex flex-wrap items-baseline gap-x-4 gap-y-2"
                    data-coaches-reveal
                >
                    <span className="text-xs uppercase tracking-wider text-pewter">
                        Στυλ
                    </span>
                    <span className="text-base font-medium text-bone-dim">
                        Κικ μπόξινγκ{' '}
                        <span aria-hidden="true" className="px-2 text-blood">·</span>{' '}
                        Μποξ
                    </span>
                </p>

                <div className="mt-9" data-coaches-reveal>
                    <h3 className="text-xs font-medium uppercase tracking-wider text-pewter">
                        Αγωνιστικό ρεκόρ
                    </h3>
                    <dl className="mt-4 grid grid-cols-2 border-y border-line-strong @min-[28rem]:grid-cols-4">
                        {coachMetrics.map((metric, index) => (
                            <div
                                className={`flex min-w-0 flex-col gap-2 border-line-strong py-5 @max-[15rem]:col-span-2 @max-[15rem]:[border-right-width:0px] @max-[15rem]:px-0 ${
                                    index % 2 === 0 ? 'border-r pr-4' : 'pl-4'
                                } ${index < 2 ? 'border-b @min-[28rem]:border-b-0' : ''} ${
                                    index === 1 ? '@min-[28rem]:border-r @min-[28rem]:pr-4' : ''
                                } ${index === 2 ? '@max-[15rem]:border-b @min-[28rem]:pl-4' : ''}`}
                                key={metric.label}
                            >
                                <dt className="text-xs uppercase tracking-wide text-bone-dim">
                                    {metric.label}
                                </dt>
                                <dd className={`order-first font-display text-[clamp(3rem,11cqw,4.5rem)] font-black leading-none tabular-nums ${metric.highlight ? 'text-blood' : 'text-bone'}`}>
                                    {metric.value}
                                </dd>
                            </div>
                        ))}
                    </dl>
                </div>

                <div className="mt-9" data-coaches-reveal>
                    <h3 className="text-xs font-medium uppercase tracking-wider text-blood">
                        Διακρίσεις
                    </h3>
                    <ul className="mt-3 divide-y divide-line-strong">
                        {coachAchievements.map((achievement) => (
                            <li
                                className="grid grid-cols-1 items-baseline gap-x-4 gap-y-1 py-4 @min-[18rem]:grid-cols-[minmax(0,1fr)_auto]"
                                key={achievement.year}
                            >
                                <p className="text-base font-medium leading-6 text-bone">
                                    {achievement.title}
                                </p>
                                <p className="font-display text-xl font-bold leading-6 tabular-nums text-blood">
                                    {achievement.year}
                                </p>
                                {(achievement.location || achievement.detail) && (
                                    <p className="text-sm leading-6 text-bone-dim @min-[18rem]:col-span-2">
                                        {achievement.location || achievement.detail}
                                    </p>
                                )}
                            </li>
                        ))}
                    </ul>
                </div>
            </div>
        </section>
    );
}

function TeamStatement() {
    return (
        <section className="relative overflow-hidden border-b border-t border-line bg-blood px-5 py-28 text-ink-0 sm:px-8 sm:py-40 lg:px-12 lg:py-52">
            <div
                aria-hidden="true"
                className="absolute -right-[.08em] -top-[.24em] font-display text-[clamp(18rem,45vw,42rem)] leading-none text-black/7"
            >
                Μ
            </div>
            <div className="relative mx-auto max-w-[1500px]">
                <p
                    className="text-[10px] font-semibold uppercase"
                    data-coaches-reveal
                >
                    Η ομάδα
                </p>
                <h2
                    className="mt-7 max-w-[72rem] [text-wrap:balance] font-display text-[clamp(3.1rem,7vw,7rem)] font-black uppercase leading-[0.84]"
                    data-coaches-reveal
                >
                    Κάθε αθλητής έχει τον δικό του ρυθμό. Όλοι μοιράζονται την
                    ίδια πειθαρχία.
                </h2>
            </div>
        </section>
    );
}

function AthletesSection() {
    return (
        <section className="bg-ink-0 px-5 py-28 sm:px-8 sm:py-40 lg:px-12 lg:py-52">
            <div className="mx-auto max-w-[1500px]">
                <div className="flex items-end justify-between gap-6">
                    <div data-coaches-reveal>
                        <h2 className="max-w-[52rem] font-display text-[clamp(3.4rem,12vw,4rem)] font-black uppercase leading-[0.84] text-bone sm:text-[clamp(4rem,8vw,7.5rem)] sm:leading-[0.8]">
                            Οι μαχητές μας
                        </h2>
                        <p className="mt-6 max-w-2xl text-sm leading-7 text-bone-dim sm:text-base">
                            Η παρουσίαση των αθλητών είναι προσωρινή: μέχρι να
                            δοθούν τα επίσημα στοιχεία, δεν εμφανίζονται
                            ονόματα ή αγωνιστικές κατηγορίες.
                        </p>
                    </div>
                    <p className="hidden border-blood pl-4 text-[10px] uppercase leading-5 text-pewter sm:block sm:[border-left-width:1px]">
                        Προσωρινή
                        <br />
                        παρουσίαση
                    </p>
                </div>

                <div className="mt-14 grid grid-flow-dense grid-cols-1 gap-px bg-line-strong lg:grid-cols-12">
                    {athletePlaceholders.map((athlete, index) => (
                        <article
                            className={`group relative min-w-0 overflow-hidden bg-ink-1 ${cardSpans[index]}`}
                            data-coaches-reveal
                            key={athlete.image}
                        >
                            <div className="relative min-h-[32rem] overflow-hidden sm:min-h-[38rem]">
                                <SiteImage
                                    alt={athlete.imageAlt}
                                    className="absolute inset-0 h-full w-full object-cover object-center grayscale contrast-125 transition-[filter,transform] duration-700 ease-out group-hover:scale-105 group-hover:grayscale-0"
                                    data-athlete-image
                                    image={athlete.image}
                                    slot={
                                        index === 0 || index === 3
                                            ? 'full'
                                            : 'half'
                                    }
                                />
                                <div className="absolute inset-0 bg-[linear-gradient(180deg,rgba(5,5,5,.06),rgba(5,5,5,.9)_90%)]" />
                                <div className="absolute inset-x-5 bottom-5 sm:inset-x-7 sm:bottom-7">
                                    <div className="flex items-center justify-between border-b border-white/25 pb-3 text-[9px] uppercase text-bone/75">
                                        <span>Προσωρινή θέση</span>
                                        <span>Χωρίς κατηγορία</span>
                                    </div>
                                    <div className="mt-4 flex items-end justify-between gap-5">
                                        <div>
                                            <h3 className="max-w-full font-display text-[clamp(2.35rem,9vw,2.8rem)] font-black uppercase leading-[0.9] text-bone sm:text-[clamp(2.6rem,5.5vw,4.5rem)] sm:leading-[0.86]">
                                                {athlete.title}
                                            </h3>
                                            <p className="mt-3 text-[10px] uppercase leading-5 text-blood">
                                                {athlete.trainingFocus}
                                            </p>
                                            <p className="mt-3 max-w-[34rem] text-sm leading-6 text-bone/70">
                                                {athlete.detail}
                                            </p>
                                        </div>
                                        <span
                                            aria-hidden="true"
                                            className="font-display text-5xl text-blood sm:text-7xl"
                                        >
                                            {athlete.marker}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </article>
                    ))}
                </div>
            </div>
        </section>
    );
}

function GallerySection() {
    return (
        <section className="bg-ink-0 px-5 pb-28 sm:px-8 sm:pb-40 lg:px-12 lg:pb-52">
            <div className="mx-auto max-w-[1500px]">
                <div className="mb-12 grid gap-6 border-t border-line-strong pt-10 lg:grid-cols-[1fr_auto] lg:items-end">
                    <div data-coaches-reveal>
                        <p className="text-[10px] font-medium uppercase text-blood">
                            Στιγμές προπόνησης
                        </p>
                        <h2 className="mt-5 max-w-[52rem] font-display text-[clamp(3.4rem,12vw,4rem)] font-black uppercase leading-[0.84] text-bone sm:text-[clamp(4rem,8vw,7.5rem)] sm:leading-[0.8]">
                            Η δουλειά στο ρινγκ
                        </h2>
                    </div>
                    <p
                        className="max-w-md text-sm leading-7 text-bone-dim sm:text-base"
                        data-coaches-reveal
                    >
                        Σάκοι, στόχοι, sparring και επανάληψη. Η εικόνα της
                        ομάδας μένει απλή: καθημερινή δουλειά, καθαρή τεχνική,
                        σταθερός ρυθμός.
                    </p>
                </div>

                <div className="grid gap-px bg-line-strong md:grid-cols-2">
                    {gallery.map((item, index) => (
                        <figure
                            className={`relative min-h-[20rem] overflow-hidden bg-ink-1 ${
                                index === 1 ? 'md:translate-y-10' : ''
                            } ${index === 2 ? 'md:-translate-y-10' : ''}`}
                            data-coaches-reveal
                            key={item.image}
                        >
                            <SiteImage
                                alt={item.alt}
                                className="absolute inset-0 h-full w-full object-cover object-center grayscale contrast-125"
                                data-gallery-image
                                image={item.image}
                                slot="half"
                            />
                            <div className="absolute inset-0 bg-[linear-gradient(180deg,rgba(5,5,5,.08),rgba(5,5,5,.62))]" />
                            <figcaption className="absolute inset-x-5 bottom-5 flex items-center justify-between border-t border-white/25 pt-3 text-[10px] uppercase text-bone/75 sm:inset-x-7 sm:bottom-7">
                                <span>{item.label}</span>
                                <span>{String(index + 1).padStart(2, '0')}</span>
                            </figcaption>
                        </figure>
                    ))}
                </div>
            </div>
        </section>
    );
}

function ClosingSection() {
    return (
        <section className="grain relative isolate min-h-[86svh] overflow-hidden bg-ink-0">
            <div className="absolute inset-0 -z-20" data-coaches-closing-image>
                <SiteImage
                    alt="Δυναμική προπόνηση kickboxing μέσα στη σχολή"
                    className="h-full w-full object-cover object-center grayscale contrast-125"
                    image="athlete-kick"
                    slot="full"
                />
            </div>
            <div className="absolute inset-0 -z-10 bg-[linear-gradient(180deg,rgba(5,5,5,.18),rgba(5,5,5,.92))]" />
            <div className="mx-auto flex min-h-[86svh] max-w-[1600px] flex-col items-start justify-end px-5 pb-14 sm:px-8 sm:pb-20 lg:px-12 lg:pb-24">
                <p
                    className="text-[10px] font-medium uppercase text-blood"
                    data-coaches-reveal
                >
                    Η θέση σου στην ομάδα
                </p>
                <h2
                    className="mt-5 max-w-[60rem] [text-wrap:balance] font-display text-[clamp(3.55rem,8vw,7.5rem)] font-black uppercase leading-[0.8] text-bone"
                    data-coaches-reveal
                >
                    Γνώρισε τον μαχητή μέσα σου.
                </h2>
                <div className="mt-8" data-coaches-reveal>
                    <SweepLink href="/schedule" variant="blood">
                        Δες το πρόγραμμα
                    </SweepLink>
                </div>
            </div>
        </section>
    );
}

export default function Coaches() {
    const pageRef = useRef(null);

    useCoachesPageAnimation(pageRef);

    return (
        <div className="w-full max-w-full overflow-x-clip bg-ink-0" ref={pageRef}>
            <CoachesHero />
            <CoachSection />
            <TeamStatement />
            <AthletesSection />
            <GallerySection />
            <ClosingSection />
        </div>
    );
}
