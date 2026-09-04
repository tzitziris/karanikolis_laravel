import { Link } from '@inertiajs/react';
import { useRef } from 'react';
import SiteImage from '../Components/SiteImage';
import { useSchedulePageAnimation } from '../animation/useSchedulePageAnimation';

const schedule = [
    {
        code: 'ΔΕΥ',
        day: 'Δευτέρα',
        slots: [
            { level: 'Αρχάριοι', time: '18:00 — 19:30', title: 'Kickboxing' },
            {
                level: 'Προχωρημένοι',
                time: '19:30 — 21:00',
                title: 'Kickboxing',
            },
        ],
    },
    {
        code: 'ΤΡΙ',
        day: 'Τρίτη',
        slots: [
            {
                level: 'Παιδικό',
                time: '17:30 — 18:30',
                title: 'Παιδικό Τμήμα',
            },
            {
                level: 'Προχωρημένοι',
                time: '19:00 — 20:30',
                title: 'Τεχνική & Φ.Κ.',
            },
        ],
    },
    {
        code: 'ΤΕΤ',
        day: 'Τετάρτη',
        slots: [
            { level: 'Αρχάριοι', time: '18:00 — 19:30', title: 'Kickboxing' },
            {
                level: 'Προχωρημένοι',
                time: '19:30 — 21:00',
                title: 'Kickboxing',
            },
        ],
    },
    {
        code: 'ΠΕΜ',
        day: 'Πέμπτη',
        slots: [
            {
                level: 'Παιδικό',
                time: '17:30 — 18:30',
                title: 'Παιδικό Τμήμα',
            },
            {
                level: 'Αγωνιστικό',
                time: '19:00 — 20:30',
                title: 'Αγωνιστικό',
            },
        ],
    },
    {
        code: 'ΠΑΡ',
        day: 'Παρασκευή',
        slots: [
            { level: 'Αρχάριοι', time: '18:00 — 19:30', title: 'Kickboxing' },
            {
                level: 'Προχωρημένοι',
                time: '19:30 — 21:00',
                title: 'Kickboxing',
            },
        ],
    },
    {
        code: 'ΣΑΒ',
        day: 'Σάββατο',
        slots: [
            {
                level: 'Ομαδική',
                time: '11:00 — 12:30',
                title: 'Ομαδική Προπόνηση',
            },
        ],
    },
];

const levels = [
    {
        description: 'Για τα πρώτα βήματα στην τεχνική και τη φυσική κατάσταση.',
        name: 'Αρχάριοι',
    },
    {
        description: 'Για ασκούμενους με σταθερή βάση και μεγαλύτερη ένταση.',
        name: 'Προχωρημένοι',
    },
    {
        description: 'Προπόνηση προσαρμοσμένη σε μικρότερες ηλικίες.',
        name: 'Παιδικό',
    },
    {
        description: 'Προετοιμασία για αθλητές με αγωνιστικούς στόχους.',
        name: 'Αγωνιστικό',
    },
    {
        description: 'Κοινή προπόνηση ρυθμού, τεχνικής και συνεργασίας.',
        name: 'Ομαδική',
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

function ScheduleHero() {
    return (
        <section
            className="grain relative isolate min-h-[calc(100dvh-5rem)] overflow-hidden bg-ink-0"
            data-schedule-hero
        >
            <SiteImage
                alt="Αθλητής kickboxing προπονείται με συγκέντρωση στη σχολή"
                className="absolute inset-0 -z-30 h-full w-full object-cover object-[62%_center] saturate-[.7] contrast-125 sm:object-[58%_center] lg:object-[72%_42%]"
                data-schedule-hero-image
                image="schedule-hero"
                priority
                slot="hero"
            />
            <div className="absolute inset-0 -z-20 bg-[linear-gradient(90deg,rgba(5,5,5,.96)_0%,rgba(5,5,5,.74)_45%,rgba(5,5,5,.16)_100%)]" />
            <div className="absolute inset-0 -z-10 bg-[linear-gradient(180deg,rgba(5,5,5,.04),rgba(5,5,5,.88)_100%)]" />

            <div
                className="mx-auto flex min-h-[calc(100dvh-5rem)] max-w-[1600px] flex-col justify-end px-5 pb-8 pt-24 sm:px-8 sm:pb-10 lg:px-12 lg:pb-12"
                data-schedule-hero-content
            >
                <p
                    className="mb-5 text-[10px] font-medium uppercase text-blood"
                    data-schedule-reveal
                >
                    Η συνέπεια γίνεται δύναμη
                </p>
                <h1
                    className="max-w-full font-display text-[clamp(3.85rem,13vw,12.4rem)] font-black uppercase leading-[0.76] text-bone [text-shadow:0_10px_60px_rgba(0,0,0,.6)]"
                    data-schedule-reveal
                >
                    Πρόγραμμα
                    <span className="block text-[clamp(2.9rem,8.4vw,7.8rem)] text-blood">
                        Μαθημάτων
                    </span>
                </h1>
                <div
                    className="mt-7 grid gap-5 border-t border-white/20 pt-5 lg:grid-cols-[minmax(0,36rem)_minmax(18rem,28rem)] lg:items-end lg:justify-between"
                    data-schedule-reveal
                >
                    <p className="max-w-xl text-sm leading-6 text-bone/80 sm:text-base">
                        Από τα πρώτα βήματα μέχρι την αγωνιστική προετοιμασία.
                        Βρες το τμήμα που ταιριάζει στο επίπεδο και τον ρυθμό
                        σου.
                    </p>
                    <p className="text-[10px] uppercase leading-5 text-bone/65 lg:[text-align:right]">
                        6 ημέρες · 11 προπονήσεις
                        <br />
                        Ελευθερούπολη · Καβάλα
                    </p>
                </div>
            </div>
        </section>
    );
}

function LevelsMarquee() {
    const repeatedLevels = [...levels, ...levels, ...levels, ...levels];

    return (
        <div
            aria-label="Επίπεδα και τμήματα"
            className="overflow-hidden border-b border-t border-line bg-blood py-4 text-ink-0"
        >
            <div aria-hidden="true" className="marquee-track">
                {repeatedLevels.map((level, index) => (
                    <span
                        className="flex items-center gap-7 px-7 font-display text-3xl font-black uppercase leading-none sm:text-4xl"
                        key={`${level.name}-${index}`}
                    >
                        {level.name}
                        <span className="h-1.5 w-1.5 shrink-0 bg-ink-0" />
                    </span>
                ))}
            </div>
            <span className="sr-only">
                {levels.map((level) => level.name).join(', ')}
            </span>
        </div>
    );
}

function StatementSection() {
    return (
        <section className="relative overflow-hidden bg-ink-0 px-5 py-28 sm:px-8 sm:py-40 lg:px-12 lg:py-52">
            <div
                aria-hidden="true"
                className="absolute -right-[.07em] top-[.02em] font-display text-[clamp(16rem,42vw,40rem)] leading-none text-blood/[.035]"
            >
                Ω
            </div>
            <div className="relative mx-auto max-w-[1500px]">
                <p
                    className="text-[10px] font-medium uppercase text-blood"
                    data-schedule-reveal
                >
                    Κάθε μέρα με στόχο
                </p>
                <h2
                    className="mt-7 max-w-[18ch] whitespace-normal font-display text-[clamp(3.15rem,8vw,8.2rem)] font-black uppercase leading-[0.86] text-bone"
                    data-schedule-reveal
                >
                    Η πρόοδος χτίζεται μέσα στον ρυθμό της εβδομάδας.
                </h2>
            </div>
        </section>
    );
}

function ScheduleSlot({ slot }) {
    return (
        <li className="grid min-w-0 gap-6 bg-ink-1 px-4 py-5 transition-colors duration-300 hover:bg-ink-2 sm:px-5 sm:py-6 xl:grid-cols-[minmax(8rem,.44fr)_minmax(0,1fr)]">
            <div className="min-w-0">
                <p className="text-[9px] font-semibold uppercase leading-5 text-blood">
                    Ώρα
                </p>
                <time className="mt-2 block whitespace-normal font-mono text-sm font-semibold leading-6 text-bone sm:text-base">
                    {slot.time}
                </time>
            </div>
            <div className="min-w-0">
                <p className="whitespace-normal font-display text-[clamp(1.55rem,6vw,2.1rem)] font-black uppercase leading-[0.94] text-bone md:text-[clamp(1.65rem,2.7vw,2.35rem)]">
                    {slot.title}
                </p>
                <p className="mt-3 flex min-w-0 items-center gap-2.5 text-[9px] font-semibold uppercase leading-5 text-blood">
                    <span
                        aria-hidden="true"
                        className="h-1.5 w-1.5 shrink-0 bg-blood"
                    />
                    <span className="min-w-0 whitespace-normal">{slot.level}</span>
                </p>
            </div>
        </li>
    );
}

function WeeklyTimetable() {
    return (
        <section
            className="relative border-b border-t border-line bg-ink-1"
            data-schedule-grid
        >
            <div className="mx-auto grid max-w-[1600px] lg:grid-cols-[minmax(20rem,.76fr)_minmax(0,1.24fr)]">
                <div className="relative border-b border-line lg:border-r lg:[border-bottom-width:0px]">
                    <aside className="schedule-sticky relative h-[68svh] min-h-[34rem] overflow-hidden lg:sticky lg:top-20 lg:h-[calc(100dvh-5rem)]">
                        <SiteImage
                            alt="Αθλητής kickboxing εκτελεί γόνατο σε προπονητικό στόχο"
                            className="h-full w-full object-cover object-[58%_center] saturate-[.55] contrast-125"
                            data-schedule-rhythm-image
                            image="schedule-rhythm"
                            slot="portrait"
                        />
                        <div className="absolute inset-0 bg-[linear-gradient(180deg,rgba(5,5,5,.08),rgba(5,5,5,.88)),linear-gradient(90deg,rgba(212,161,66,.18),transparent_45%)]" />
                        <div className="absolute inset-x-5 bottom-6 sm:inset-x-8 lg:inset-x-10 lg:bottom-10">
                            <p className="text-[10px] font-medium uppercase text-blood">
                                Ο εβδομαδιαίος ρυθμός
                            </p>
                            <p className="mt-4 max-w-full whitespace-normal font-display text-[clamp(2.65rem,11vw,3rem)] font-black uppercase leading-[0.9] text-bone sm:max-w-md sm:text-[clamp(3rem,7vw,5.8rem)] sm:leading-[0.84]">
                                Βρες την ώρα. Μπες στην προπόνηση.
                            </p>
                        </div>
                    </aside>
                </div>

                <div className="px-5 py-24 sm:px-8 sm:py-32 lg:px-10 lg:py-40 xl:px-14">
                    <header
                        className="mb-14 border-b border-blood pb-8 sm:mb-20"
                        data-schedule-reveal
                    >
                        <p className="text-[10px] font-medium uppercase text-blood">
                            Εβδομαδιαία προπόνηση
                        </p>
                        <h2 className="mt-5 max-w-full whitespace-normal font-display text-[clamp(3.05rem,12vw,4rem)] font-black uppercase leading-[0.86] text-bone sm:max-w-[11ch] sm:text-[clamp(4rem,8.4vw,7.4rem)] sm:leading-[0.82]">
                            Διάλεξε τη μέρα σου.
                        </h2>
                        <p className="mt-6 max-w-2xl text-sm leading-7 text-bone-dim sm:text-base">
                            Οι ώρες που εμφανίζονται εδώ είναι ενδεικτικές και
                            δεν αποτελούν ακόμη το επίσημο πρόγραμμα της
                            σχολής.
                        </p>
                    </header>

                    <ol aria-label="Εβδομαδιαίο πρόγραμμα μαθημάτων">
                        {schedule.map((day, dayIndex) => (
                            <li
                                className="group border-b border-line-strong py-9 first:border-t sm:py-11"
                                data-schedule-reveal
                                key={day.day}
                            >
                                <header className="grid min-w-0 gap-4 sm:grid-cols-[minmax(0,1fr)_auto] sm:items-end">
                                    <div className="min-w-0">
                                        <p className="text-[9px] font-medium uppercase leading-5 text-blood">
                                            {day.code} ·{' '}
                                            {String(dayIndex + 1).padStart(2, '0')}
                                        </p>
                                        <h3 className="mt-3 whitespace-normal font-display text-[clamp(3rem,11vw,5.8rem)] font-black uppercase leading-[0.86] text-bone transition-colors duration-300 group-hover:text-blood sm:text-[clamp(3.5rem,6vw,6.2rem)] sm:leading-[0.82]">
                                            {day.day}
                                        </h3>
                                    </div>
                                    <span
                                        aria-hidden="true"
                                        className="hidden font-display text-[clamp(4.5rem,8vw,7.5rem)] font-black leading-[.7] text-blood/10 sm:block"
                                    >
                                        {day.code}
                                    </span>
                                </header>

                                <ul
                                    className={`mt-7 grid gap-px bg-line-strong sm:mt-9 ${
                                        day.slots.length > 1
                                            ? '2xl:grid-cols-2'
                                            : ''
                                    }`}
                                >
                                    {day.slots.map((slot) => (
                                        <ScheduleSlot
                                            key={`${day.day}-${slot.time}-${slot.title}`}
                                            slot={slot}
                                        />
                                    ))}
                                </ul>
                            </li>
                        ))}
                    </ol>
                </div>
            </div>
        </section>
    );
}

function LevelsLegend() {
    return (
        <section className="bg-ink-0 px-5 py-24 sm:px-8 sm:py-32 lg:px-12 lg:py-40">
            <div className="mx-auto max-w-[1500px]">
                <div
                    className="grid gap-8 border-b border-line-strong pb-10 lg:grid-cols-[minmax(0,5fr)_minmax(0,7fr)] lg:items-end"
                    data-schedule-reveal
                >
                    <div>
                        <p className="text-[10px] font-medium uppercase text-blood">
                            Υπόμνημα επιπέδων
                        </p>
                        <h2 className="mt-5 max-w-full whitespace-normal font-display text-[clamp(3.1rem,11vw,4rem)] font-black uppercase leading-[0.86] text-bone sm:max-w-[13ch] sm:text-[clamp(4rem,7.2vw,6.8rem)] sm:leading-[0.82]">
                            Πέντε τρόποι προπόνησης.
                        </h2>
                    </div>
                    <p className="max-w-2xl text-sm leading-7 text-bone-dim sm:text-base lg:justify-self-end">
                        Οι ετικέτες δείχνουν τον τύπο τμήματος, ώστε κάθε
                        ασκούμενος να βρίσκει γρήγορα το επίπεδο που του
                        ταιριάζει.
                    </p>
                </div>

                <ol className="mt-12 grid gap-px bg-line-strong md:grid-cols-2 xl:grid-cols-5">
                    {levels.map((level, index) => (
                        <li
                            className="min-w-0 bg-ink-1 p-5 sm:p-6"
                            data-schedule-reveal
                            key={level.name}
                        >
                            <p className="font-mono text-xs text-blood">
                                {String(index + 1).padStart(2, '0')}
                            </p>
                            <h3 className="mt-8 whitespace-normal font-display text-[clamp(2.2rem,7vw,3.4rem)] font-black uppercase leading-[0.9] text-bone md:text-[clamp(2.4rem,4vw,3.3rem)] xl:text-[clamp(2rem,2.2vw,2.7rem)]">
                                {level.name}
                            </h3>
                            <p className="mt-4 text-sm leading-7 text-bone-dim">
                                {level.description}
                            </p>
                        </li>
                    ))}
                </ol>
            </div>
        </section>
    );
}

function ClosingSection() {
    return (
        <section
            className="grain relative isolate overflow-hidden px-5 py-28 sm:px-8 sm:py-40 lg:px-12 lg:py-52"
            data-schedule-note
        >
            <div className="absolute inset-0 -z-30" data-schedule-closing-image>
                <SiteImage
                    alt=""
                    className="h-full w-full object-cover object-[52%_center] grayscale contrast-125"
                    image="schedule-hero"
                    slot="full"
                />
            </div>
            <div className="absolute inset-0 -z-20 bg-[linear-gradient(90deg,rgba(5,5,5,.96),rgba(5,5,5,.7)_65%,rgba(5,5,5,.45))]" />
            <div className="relative mx-auto max-w-[1500px]">
                <p
                    className="text-[10px] font-medium uppercase text-blood"
                    data-schedule-reveal
                >
                    Πριν την πρώτη προπόνηση
                </p>
                <h2
                    className="mt-5 max-w-full whitespace-normal font-display text-[clamp(3.05rem,12vw,4rem)] font-black uppercase leading-[0.86] text-bone sm:max-w-[12ch] sm:text-[clamp(4rem,8.4vw,8rem)] sm:leading-[0.82]"
                    data-schedule-reveal
                >
                    Μην ξεκινήσεις χωρίς επιβεβαίωση.
                </h2>
                <p
                    className="mt-7 max-w-2xl text-sm leading-[1.8] text-bone/80 sm:text-base"
                    data-schedule-reveal
                >
                    Το πρόγραμμα αυτής της σελίδας είναι προσωρινό. Για την
                    πρώτη σου προπόνηση, επικοινώνησε με τη σχολή ώστε να
                    επιβεβαιώσεις ημέρα, ώρα και τμήμα.
                </p>
                <div className="mt-9" data-schedule-reveal>
                    <SweepLink href="/about" variant="blood">
                        Επικοινωνία
                    </SweepLink>
                </div>
            </div>
        </section>
    );
}

export default function Schedule() {
    const pageRef = useRef(null);

    useSchedulePageAnimation(pageRef);

    return (
        <div
            className="w-full max-w-full overflow-x-clip bg-ink-0"
            ref={pageRef}
        >
            <ScheduleHero />
            <LevelsMarquee />
            <StatementSection />
            <WeeklyTimetable />
            <LevelsLegend />
            <ClosingSection />
        </div>
    );
}
