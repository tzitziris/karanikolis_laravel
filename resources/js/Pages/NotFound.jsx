import { Link } from '@inertiajs/react';
import SiteImage from '../Components/SiteImage';

export default function NotFound() {
    return (
        <div className="w-full max-w-full overflow-x-clip bg-ink-0">
            <section className="grain relative isolate min-h-[calc(100dvh-5rem)] overflow-hidden bg-ink-0">
                <SiteImage
                    alt=""
                    aria-hidden="true"
                    className="absolute inset-0 -z-30 h-full w-full object-cover object-center grayscale contrast-125"
                    image="ring-training"
                    priority
                    slot="hero"
                />
                <div className="absolute inset-0 -z-20 bg-[linear-gradient(90deg,rgba(5,5,5,.98)_0%,rgba(5,5,5,.82)_58%,rgba(5,5,5,.38)_100%)]" />
                <div className="absolute inset-0 -z-10 bg-[linear-gradient(180deg,rgba(5,5,5,.08),rgba(5,5,5,.95)_100%)]" />

                <div className="mx-auto flex min-h-[calc(100dvh-5rem)] max-w-[1500px] flex-col justify-end px-5 pb-12 pt-24 sm:px-8 sm:pb-16 lg:px-12 lg:pb-20">
                    <p className="text-[10px] font-medium uppercase text-blood">
                        Η σελίδα δεν βρέθηκε
                    </p>
                    <h1 className="mt-5 max-w-[13ch] font-display text-[clamp(3.6rem,15vw,5rem)] font-black uppercase leading-[0.84] text-bone sm:text-[clamp(4.8rem,10vw,9rem)] sm:leading-[0.78]">
                        Δεν υπάρχει εδώ δημοσιευμένο νέο.
                    </h1>
                    <p className="mt-7 max-w-2xl text-sm leading-7 text-bone-dim sm:text-base">
                        Το άρθρο μπορεί να έχει μετακινηθεί, να μην έχει
                        δημοσιευτεί ακόμη ή να μην είναι πλέον διαθέσιμο.
                    </p>
                    <div className="mt-8 flex flex-wrap gap-4">
                        <Link
                            className="btn-sweep text-blood"
                            href="/news"
                            prefetch={['hover']}
                        >
                            Πήγαινε στα νέα <span aria-hidden="true">↗</span>
                        </Link>
                        <Link
                            className="btn-sweep text-bone"
                            href="/"
                            prefetch={['hover']}
                        >
                            Αρχική <span aria-hidden="true">↗</span>
                        </Link>
                    </div>
                </div>
            </section>
        </div>
    );
}
