import { Link } from '@inertiajs/react';
import { useRef } from 'react';
import SiteImage from '../Components/SiteImage';
import { useAboutPageAnimation } from '../animation/useAboutPageAnimation';

const principles = [
    {
        body: 'Στον προπονητή, τον συναθλητή και τον εαυτό σου.',
        number: 'I',
        title: 'Σεβασμός',
    },
    {
        body: 'Καθαρές κινήσεις πριν από την ταχύτητα. Πάντα.',
        number: 'II',
        title: 'Τεχνική',
    },
    {
        body: 'Η σταθερή παρουσία είναι το πραγματικό ταλέντο.',
        number: 'III',
        title: 'Συνέχεια',
    },
    {
        body: 'Νοητική και σωματική. Χωρίς δικαιολογίες.',
        number: 'IV',
        title: 'Σκληράδα',
    },
];

const contactDetails = [
    {
        key: 'Διεύθυνση',
        lines: ['Ελευθερούπολη, Καβάλα', 'Κεντρική οδός 00 · προσωρινή διεύθυνση'],
    },
    {
        key: 'Τηλέφωνο',
        lines: ['2510 000000'],
    },
    {
        key: 'Email',
        lines: ['info@maxites-eleftheroupolis.gr'],
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

function AboutHero() {
    return (
        <section
            className="grain relative isolate min-h-[calc(100dvh-5rem)] overflow-hidden bg-ink-0"
            data-about-hero
        >
            <SiteImage
                alt="Αθλητής kickboxing προπονείται με συγκέντρωση μέσα στη σχολή"
                className="absolute inset-0 -z-30 h-full w-full object-cover object-[58%_center] saturate-[.65] contrast-125 sm:object-[62%_center] lg:object-[72%_42%]"
                data-about-hero-image
                image="about-hero"
                priority
                slot="hero"
            />
            <div className="absolute inset-0 -z-20 bg-[linear-gradient(90deg,rgba(5,5,5,.97)_0%,rgba(5,5,5,.76)_46%,rgba(5,5,5,.12)_100%)]" />
            <div className="absolute inset-0 -z-10 bg-[linear-gradient(180deg,rgba(5,5,5,.04),rgba(5,5,5,.9)_100%)]" />
            <div className="absolute inset-0 -z-10 bg-[radial-gradient(circle_at_74%_40%,rgba(212,161,66,.16),transparent_30%)]" />

            <div
                className="mx-auto flex min-h-[calc(100dvh-5rem)] max-w-[1600px] flex-col justify-end px-5 pb-8 pt-24 sm:px-8 sm:pb-10 lg:px-12 lg:pb-12"
                data-about-hero-content
            >
                <p
                    className="mb-5 text-[10px] font-medium uppercase text-blood"
                    data-about-reveal
                >
                    Ταυτότητα · Πειθαρχία · Εξέλιξη
                </p>
                <h1
                    className="max-w-full font-display text-[clamp(4.4rem,14vw,13.4rem)] font-black uppercase leading-[0.74] text-bone [text-shadow:0_10px_60px_rgba(0,0,0,.6)]"
                    data-about-reveal
                >
                    Μαχητές
                    <span className="block text-[clamp(2.4rem,8.3vw,8rem)] text-blood">
                        Ελευθερούπολης
                    </span>
                </h1>
                <div
                    className="mt-7 grid gap-5 border-t border-white/20 pt-5 sm:grid-cols-[minmax(0,38rem)_auto] sm:items-end sm:justify-between"
                    data-about-reveal
                >
                    <p className="max-w-xl text-sm leading-6 text-bone/80 sm:text-base">
                        Μια σχολή που χτίζει τεχνική, πειθαρχία και χαρακτήρα.
                        Από την πρώτη προπόνηση μέχρι το αγωνιστικό επίπεδο, η
                        εξέλιξη είναι πάντα συλλογική.
                    </p>
                    <p className="text-[10px] uppercase leading-5 text-bone/65 sm:[text-align:right]">
                        Από το 2010
                        <br />
                        Ελευθερούπολη · Καβάλα
                    </p>
                </div>
            </div>
        </section>
    );
}

function StorySection() {
    return (
        <section
            className="relative mx-auto grid max-w-[1600px] bg-ink-0 lg:grid-cols-[minmax(0,1fr)_minmax(0,1fr)]"
            data-about-story
        >
            <div className="relative lg:min-h-[210dvh]">
                <div className="about-sticky relative h-[72svh] min-h-[34rem] overflow-hidden lg:sticky lg:top-20 lg:h-[calc(100dvh-5rem)]">
                    <SiteImage
                        alt="Σάκοι προπόνησης σε οργανωμένο χώρο πυγμαχίας"
                        className="h-full w-full object-cover object-center grayscale contrast-125"
                        data-about-story-image
                        image="about-story"
                        slot="half"
                    />
                    <div className="absolute inset-0 bg-[linear-gradient(180deg,rgba(5,5,5,.02)_30%,rgba(5,5,5,.88)),linear-gradient(90deg,rgba(212,161,66,.18),transparent_42%)]" />
                    <div className="absolute inset-x-5 bottom-6 border-t border-white/25 pt-4 sm:inset-x-8 lg:inset-x-12">
                        <p className="max-w-md text-[10px] uppercase leading-5 text-bone/75">
                            Ο χώρος δεν σε αλλάζει μόνος του.
                            <br />
                            Η καθημερινή δουλειά το κάνει.
                        </p>
                    </div>
                </div>
            </div>

            <div className="px-5 sm:px-8 lg:px-14 xl:px-20">
                <article className="flex min-h-[92svh] flex-col justify-center border-b border-line-strong py-28 sm:py-36 lg:min-h-[105dvh] lg:py-48">
                    <p
                        className="text-[10px] font-medium uppercase text-blood"
                        data-about-reveal
                    >
                        Η αρχή
                    </p>
                    <h2
                        className="mt-6 max-w-full font-display text-[clamp(3.45rem,15vw,4rem)] font-black uppercase leading-[0.84] text-bone sm:text-[clamp(4rem,7.5vw,6rem)] sm:leading-[0.78]"
                        data-about-reveal
                    >
                        Από το
                        <span className="block text-blood">2010</span>
                    </h2>
                    <p
                        className="mt-10 max-w-xl text-base leading-[1.85] text-bone-dim sm:text-lg"
                        data-about-reveal
                    >
                        Οι Μαχητές Ελευθερούπολης δημιουργήθηκαν για να
                        προσφέρουν έναν σοβαρό και οργανωμένο χώρο προπόνησης
                        kickboxing στην περιοχή. Με σταθερή παρουσία στην
                        τοπική κοινότητα, η σχολή στηρίζει νέους και ενήλικες
                        που θέλουν να γυμναστούν, να μάθουν τεχνική και να
                        εξελιχθούν με πειθαρχία.
                    </p>
                </article>

                <article className="flex min-h-[92svh] flex-col justify-center py-28 sm:py-36 lg:min-h-[105dvh] lg:py-48">
                    <p
                        className="text-[10px] font-medium uppercase text-blood"
                        data-about-reveal
                    >
                        Η φιλοσοφία
                    </p>
                    <h2
                        className="mt-6 max-w-full font-display text-[clamp(3.45rem,15vw,4rem)] font-black uppercase leading-[0.84] text-bone sm:text-[clamp(4rem,7.5vw,6rem)] sm:leading-[0.78]"
                        data-about-reveal
                    >
                        Σεβασμός
                        <span className="block text-blood">&amp; τεχνική</span>
                    </h2>
                    <p
                        className="mt-10 max-w-xl text-base leading-[1.85] text-bone-dim sm:text-lg"
                        data-about-reveal
                    >
                        Η προπόνηση βασίζεται στον σεβασμό, την καθαρή τεχνική
                        και τη συνεχή προσπάθεια. Κάθε μάθημα έχει στόχο να
                        χτίσει δυνατό σώμα, γρήγορη σκέψη και αυτοπεποίθηση,
                        χωρίς υπερβολές και χωρίς βιασύνη. Η ομάδα προχωράει
                        μαζί, με σοβαρότητα και αγωνιστικό πνεύμα.
                    </p>
                </article>
            </div>
        </section>
    );
}

function PrinciplesSection() {
    return (
        <section className="relative overflow-hidden border-b border-t border-line bg-ink-0 py-24 sm:py-32 lg:py-36">
            <div className="mx-auto max-w-[1600px] px-5 sm:px-8 lg:px-12">
                <div className="grid gap-12 lg:grid-cols-[minmax(0,5fr)_minmax(0,7fr)] lg:items-end">
                    <div data-about-reveal>
                        <p className="text-[10px] font-medium uppercase text-blood">
                            Το σύστημά μας
                        </p>
                        <h2 className="mt-6 max-w-[48rem] font-display text-[clamp(3.6rem,8vw,7rem)] font-black uppercase leading-[0.78] text-bone">
                            Τέσσερις
                            <span className="block text-blood">σταθερές.</span>
                        </h2>
                    </div>
                    <p
                        className="max-w-xl text-sm leading-7 text-bone-dim sm:text-base lg:justify-self-end"
                        data-about-reveal
                    >
                        Η πρόοδος δεν είναι στιγμιαία. Είναι ένας τρόπος να
                        στέκεσαι, να μαθαίνεις και να επιστρέφεις κάθε μέρα
                        λίγο πιο δυνατός.
                    </p>
                </div>

                <ol className="mt-16 border-t border-line-strong sm:mt-20">
                    {principles.map((principle) => (
                        <li
                            className="group grid min-h-72 items-center gap-8 border-b border-line-strong py-12 transition-colors duration-300 hover:bg-ink-2 sm:min-h-80 sm:grid-cols-[7rem_minmax(0,1fr)] sm:px-5 lg:grid-cols-[7rem_minmax(0,1fr)_minmax(16rem,24rem)] lg:px-6 xl:grid-cols-[10rem_minmax(0,1fr)_minmax(18rem,28rem)] xl:gap-12 xl:px-8"
                            data-about-reveal
                            key={principle.number}
                        >
                            <p
                                aria-hidden="true"
                                className="font-display text-7xl leading-none text-blood sm:text-8xl lg:text-9xl"
                            >
                                {principle.number}
                            </p>
                            <h3 className="font-display text-[clamp(2.8rem,4.8vw,5.75rem)] font-black uppercase leading-[0.84] text-bone transition-colors duration-300 group-hover:text-blood">
                                {principle.title}
                            </h3>
                            <p className="max-w-md text-sm leading-7 text-bone-dim sm:col-start-2 lg:col-start-auto lg:text-base">
                                {principle.body}
                            </p>
                        </li>
                    ))}
                </ol>
            </div>
        </section>
    );
}

function StatementSection() {
    return (
        <section className="grain relative isolate min-h-[100svh] overflow-hidden px-5 py-32 sm:px-8 sm:py-44 lg:flex lg:items-center lg:px-12 lg:py-52">
            <div className="absolute inset-0 -z-30" data-about-statement-image>
                <SiteImage
                    alt="Αθλητής kickboxing εκτελεί τεχνική με ακρίβεια"
                    className="h-full w-full object-cover object-[66%_center] saturate-[.6] contrast-125"
                    image="about-hero"
                    slot="full"
                />
            </div>
            <div className="absolute inset-0 -z-20 bg-[linear-gradient(90deg,rgba(5,5,5,.94),rgba(5,5,5,.72)_62%,rgba(5,5,5,.42))]" />
            <div className="absolute inset-0 -z-10 bg-[radial-gradient(circle_at_72%_42%,rgba(212,161,66,.16),transparent_32%)]" />
            <div className="mx-auto w-full max-w-[1500px]">
                <p
                    className="text-[10px] font-medium uppercase text-blood"
                    data-about-reveal
                >
                    Η νοοτροπία μας
                </p>
                <blockquote
                    className="mt-8 max-w-[72rem] [text-wrap:balance] font-display text-[clamp(3.3rem,7vw,7rem)] font-black uppercase leading-[0.84] text-bone [text-shadow:0_8px_40px_rgba(0,0,0,.8)]"
                    data-about-reveal
                >
                    Δεν ψάχνουμε τον εύκολο δρόμο. Χτίζουμε τον δυνατό άνθρωπο.
                </blockquote>
                <p
                    className="mt-10 text-xs font-semibold uppercase text-bone"
                    data-about-reveal
                >
                    Παναγιώτης Καρανικολής · Προπονητής
                </p>
            </div>
        </section>
    );
}

function ContactSection() {
    return (
        <section className="grain relative isolate overflow-hidden px-5 py-32 sm:px-8 sm:py-40 lg:px-12 lg:py-52">
            <div className="absolute inset-0 -z-30">
                <SiteImage
                    alt=""
                    className="h-full w-full object-cover object-center grayscale contrast-125"
                    image="about-story"
                    slot="full"
                />
            </div>
            <div className="absolute inset-0 -z-20 bg-[linear-gradient(90deg,rgba(5,5,5,.98),rgba(5,5,5,.86)_58%,rgba(5,5,5,.62))]" />
            <div className="mx-auto max-w-[1500px]">
                <div className="grid gap-10 border-b border-white/20 pb-12 lg:grid-cols-[minmax(0,1fr)_auto] lg:items-end">
                    <div data-about-reveal>
                        <p className="text-[10px] font-medium uppercase text-blood">
                            Η πρώτη προπόνηση
                        </p>
                        <h2 className="mt-6 max-w-[56rem] font-display text-[clamp(3.7rem,8vw,7.5rem)] font-black uppercase leading-[0.78] text-bone">
                            Έλα να μας
                            <span className="block text-blood">
                                γνωρίσεις.
                            </span>
                        </h2>
                    </div>
                    <div data-about-reveal>
                        <SweepLink href="/schedule" variant="blood">
                            Δες το Πρόγραμμα
                        </SweepLink>
                    </div>
                </div>

                <dl className="grid lg:grid-cols-3">
                    {contactDetails.map((detail) => (
                        <div
                            className="border-b border-white/20 py-8 md:border-r md:px-8 md:first:pl-0 md:last:[border-right-width:0px] md:last:pr-0"
                            data-about-reveal
                            key={detail.key}
                        >
                            <dt className="text-[9px] font-medium uppercase text-blood">
                                {detail.key}
                            </dt>
                            <dd className="mt-4 space-y-1">
                                {detail.lines.map((line) => (
                                    <span
                                        className={`block text-bone ${
                                            detail.key === 'Email'
                                                ? 'font-mono text-xs sm:text-sm lg:text-[0.72rem] xl:text-sm'
                                                : 'font-display text-[clamp(1.3rem,5vw,2rem)] font-black uppercase'
                                        }`}
                                        key={line}
                                    >
                                        {line}
                                    </span>
                                ))}
                            </dd>
                        </div>
                    ))}
                </dl>
            </div>
        </section>
    );
}

export default function About() {
    const pageRef = useRef(null);

    useAboutPageAnimation(pageRef);

    return (
        <div className="w-full max-w-full overflow-x-clip bg-ink-0" ref={pageRef}>
            <AboutHero />
            <StorySection />
            <PrinciplesSection />
            <StatementSection />
            <ContactSection />
        </div>
    );
}
