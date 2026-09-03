import HeroIntro from '../Components/HeroIntro';
import RevealOnScroll from '../Components/RevealOnScroll';

export default function Placeholder({ message }) {
    const actions = [
        {
            href: '/',
            label: 'Αρχική δοκιμή',
        },
        {
            href: '/dokimi-kinisis',
            label: 'Δοκιμή κίνησης',
        },
    ];

    return (
        <main className="min-h-screen bg-ink-1">
            <HeroIntro
                actions={actions}
                eyebrow="Βήμα θεμελίωσης"
                summary={message}
                title="Προσωρινή σελίδα"
            />

            <RevealOnScroll className="px-6 py-14 sm:px-10 sm:py-20">
                <div className="mx-auto grid max-w-5xl gap-4 md:grid-cols-3">
                    <article
                        className="border border-line-strong bg-ink-2 p-5"
                        data-reveal-item
                    >
                        <h2 className="font-display text-2xl font-black text-bone">
                            Πρώτη ανάγνωση
                        </h2>
                        <p className="mt-3 leading-7 text-bone-dim">
                            Το περιεχόμενο υπάρχει κανονικά πριν ξεκινήσει
                            οποιαδήποτε κίνηση.
                        </p>
                    </article>
                    <article
                        className="border border-line-strong bg-ink-2 p-5"
                        data-reveal-item
                    >
                        <h2 className="font-display text-2xl font-black text-bone">
                            Κύλιση
                        </h2>
                        <p className="mt-3 leading-7 text-bone-dim">
                            Τα στοιχεία μπορούν να εμφανίζονται διακριτικά όταν
                            μπαίνουν στο οπτικό πεδίο.
                        </p>
                    </article>
                    <article
                        className="border border-line-strong bg-ink-2 p-5"
                        data-reveal-item
                    >
                        <h2 className="font-display text-2xl font-black text-bone">
                            Καθαρό κλείσιμο
                        </h2>
                        <p className="mt-3 leading-7 text-bone-dim">
                            Κάθε ενεργή κίνηση ανήκει στη σελίδα που τη
                            δημιούργησε και καθαρίζεται στην αλλαγή σελίδας.
                        </p>
                    </article>
                </div>
            </RevealOnScroll>
        </main>
    );
}
