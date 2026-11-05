import { Link } from '@inertiajs/react';
import { useRef, useState } from 'react';
import YoutubeEmbed from '../Components/News/YoutubeEmbed';
import SiteImage from '../Components/SiteImage';
import { useCoachesPageAnimation } from '../animation/useCoachesPageAnimation';

const fightVideos = [
    { name: 'Giannis “Hercules” Stoforidis', youtubeId: 'K0jLEe7oz8E' },
    { name: 'Kostas “Ripper” Papadelis', youtubeId: 'r8dTR_YOQb0' },
    { name: 'Vasiliki Stavridou', youtubeId: '4CaRuOnfSUU' },
];

const athletes = [
    { image: 'athlete-padwork', name: 'Γιάννης Στοφορίδης' },
    { image: 'athlete-bag', name: 'Γιώργος Αραμπατζής' },
    { image: 'athlete-kick', name: 'Βασιλική Σταυρίδου' },
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
            {/* On a phone the photograph is lifted and trimmed top and bottom, so the athlete's face and chest sit above the text and the floor fades away behind it. */}
            <SiteImage
                alt="Αθλητής σηκώνει τα χέρια του στη νίκη, με τον διαιτητή δίπλα του"
                className="absolute inset-0 -z-30 h-full w-full object-cover object-[80%_center] saturate-[.72] contrast-125 max-lg:bottom-auto max-lg:top-[-20%] max-lg:h-[110%] max-lg:[mask-image:linear-gradient(#000_72%,transparent_98%)] lg:object-[center_70%]"
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
                        Kick Boxing{' '}
                        <span aria-hidden="true" className="px-2 text-blood">·</span>{' '}
                        Boxing{' '}
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
        <section
            aria-label="Οι μαχητές μας"
            aria-roledescription="carousel"
            className="athletes-section bg-ink-0"
            data-athletes
        >
            <div className="athletes-inner" data-athletes-inner>
                <h2 className="athletes-heading font-display font-black uppercase text-bone">
                    Οι μαχητές μας
                </h2>
                <ul className="athletes-stage" data-athletes-track>
                    {athletes.map((athlete, index) => (
                        <li className="athletes-slot" data-athlete-slot key={athlete.image}>
                            <article className="athlete-card" data-athlete-card>
                                <SiteImage
                                    alt={athlete.name}
                                    className="athlete-image"
                                    data-athlete-image
                                    image={athlete.image}
                                    slot="coverflow"
                                />
                                <div className="athlete-gradient" aria-hidden="true" />
                                <div className="athlete-caption">
                                    <h3 className="font-display font-black uppercase text-blood">
                                        {athlete.name}
                                    </h3>
                                </div>
                                <div className="athlete-dim" data-athlete-dim aria-hidden="true" />
                                <button
                                    aria-label={`Προβολή αθλητή: ${athlete.name}`}
                                    className="athlete-select"
                                    data-athlete-select={index}
                                    hidden
                                    type="button"
                                />
                            </article>
                        </li>
                    ))}
                </ul>
                <div className="athletes-controls" data-athletes-controls hidden>
                    <span className="font-mono text-sm" aria-atomic="true" aria-live="polite">
                        <span data-athletes-counter>01</span> / {String(athletes.length).padStart(2, '0')}
                    </span>
                    <div className="athletes-progress" aria-hidden="true">
                        <div data-athletes-progress />
                    </div>
                </div>
            </div>
        </section>
    );
}

function FightVideosSection() {
    const [activeVideo, setActiveVideo] = useState(null);

    return (
        <section
            aria-label="Βίντεο από αγώνες"
            className="bg-ink-0 px-5 py-28 sm:px-8 sm:py-40 lg:px-12"
        >
            <div className="mx-auto max-w-[1500px]">
                <div data-coaches-reveal>
                    <p className="text-[10px] font-medium uppercase text-blood">
                        Από τους αγώνες
                    </p>
                    <h2 className="mt-5 font-display text-[clamp(3.4rem,8vw,7.5rem)] font-black uppercase leading-[0.9] text-bone">
                        Στο ρινγκ.
                    </h2>
                </div>
                <div className="mt-14 grid grid-cols-1 gap-px bg-line-strong lg:grid-cols-6">
                    {fightVideos.map((video, index) => {
                        // Expand the final row to its actual count, without empty cells.
                        const remainder = fightVideos.length % 3;
                        const columns = index < fightVideos.length - remainder ? 3 : remainder;
                        const span = { 1: 'lg:col-span-6', 2: 'lg:col-span-3', 3: 'lg:col-span-2' }[columns];
                        const posterSizes = `(min-width: 1024px) ${columns === 3 ? 33 : 100 / columns}vw, 100vw`;

                        return (
                            <article
                                className={`@container min-w-0 bg-ink-1 p-5 sm:p-8 lg:p-6 ${span}`}
                                data-coaches-reveal
                                key={video.youtubeId}
                            >
                                <h3 className="mb-5 flex min-h-[2.5em] items-start gap-3 font-display text-[min(7cqw,2rem)] font-black uppercase leading-tight text-blood">
                                    <span aria-hidden="true" className="mt-[0.5em] h-1.5 w-1.5 shrink-0 bg-blood" />
                                    {video.name}
                                </h3>
                                <YoutubeEmbed
                                    isActive={activeVideo === video.youtubeId}
                                    onActivate={() => setActiveVideo(video.youtubeId)}
                                    playLabel={video.name}
                                    // Posters use the athlete's final surname as their filename.
                                    poster={`video-${video.name.split(' ').at(-1).toLowerCase()}`}
                                    posterSizes={posterSizes}
                                    title={`${video.name} — βίντεο αγώνα`}
                                    youtubeId={video.youtubeId}
                                />
                            </article>
                        );
                    })}
                </div>
            </div>
        </section>
    );
}

function ClosingSection() {
    return (
        <section className="grain relative isolate overflow-hidden bg-ink-0 lg:min-h-[86svh]">
            <div
                className="relative h-[clamp(24rem,110vw,34rem)] overflow-hidden lg:absolute lg:inset-0 lg:-z-20 lg:h-auto lg:origin-top"
                data-coaches-closing-image
            >
                <SiteImage
                    alt="Αθλήτρια με τα χρώματα της Ελλάδας φωνάζει από χαρά, καθώς ο διαιτητής την ανακηρύσσει νικήτρια"
                    className="h-full w-full object-cover object-left lg:object-[center_60%]"
                    image="coaches-closing"
                    slot="coachesClosing"
                />
                <div className="absolute inset-0 bg-[linear-gradient(180deg,transparent_65%,#050505)] lg:bg-[linear-gradient(180deg,transparent_35%,rgba(5,5,5,.88)_100%)]" />
            </div>
            <div className="mx-auto flex max-w-[1600px] flex-col justify-end px-5 pb-12 pt-6 sm:px-8 sm:pb-16 lg:min-h-[86svh] lg:px-12 lg:pb-8 lg:pt-24">
                <div className="w-full max-w-[40rem] lg:max-w-[48rem]">
                    <p
                        className="text-[10px] font-medium uppercase text-blood"
                        data-coaches-reveal
                    >
                        Η θέση σου στην ομάδα
                    </p>
                    <h2
                        className="mt-4 [text-wrap:balance] font-display text-[clamp(2.25rem,7.5vw,3.25rem)] font-black uppercase leading-[0.95] text-bone lg:mt-3 lg:text-[clamp(2rem,2.8vw,2.75rem)]"
                        data-coaches-reveal
                    >
                        Γνώρισε τον μαχητή μέσα σου.
                    </h2>
                    <div className="mt-6 lg:mt-4" data-coaches-reveal>
                        <SweepLink href="/schedule" variant="blood">
                            Δες το πρόγραμμα
                        </SweepLink>
                    </div>
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
            <FightVideosSection />
            <ClosingSection />
        </div>
    );
}
