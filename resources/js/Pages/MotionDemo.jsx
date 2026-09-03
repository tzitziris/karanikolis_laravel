import HeroIntro from '../Components/HeroIntro';
import RevealOnScroll from '../Components/RevealOnScroll';

export default function MotionDemo({ message }) {
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
                eyebrow="Δεύτερη διαδρομή"
                summary={message}
                title="Δοκιμή κίνησης"
            />

            <RevealOnScroll className="px-6 py-14 sm:px-10 sm:py-20">
                <div className="mx-auto grid max-w-5xl gap-4 md:grid-cols-3">
                    <article
                        className="border border-line-strong bg-ink-2 p-5"
                        data-reveal-item
                    >
                        <h2 className="font-display text-2xl font-black text-bone">
                            Νέα σελίδα
                        </h2>
                        <p className="mt-3 leading-7 text-bone-dim">
                            Αυτή η διαδρομή φορτώνει διαφορετικό React
                            component, όπως θα κάνουν οι πραγματικές σελίδες.
                        </p>
                    </article>
                    <article
                        className="border border-line-strong bg-ink-2 p-5"
                        data-reveal-item
                    >
                        <h2 className="font-display text-2xl font-black text-bone">
                            Άμεσο κείμενο
                        </h2>
                        <p className="mt-3 leading-7 text-bone-dim">
                            Η κίνηση ξεκινά από περιεχόμενο που ήδη διαβάζεται,
                            χωρίς κενό ενδιάμεσο στάδιο.
                        </p>
                    </article>
                    <article
                        className="border border-line-strong bg-ink-2 p-5"
                        data-reveal-item
                    >
                        <h2 className="font-display text-2xl font-black text-bone">
                            Έλεγχος μνήμης
                        </h2>
                        <p className="mt-3 leading-7 text-bone-dim">
                            Οι μετρήσεις των scroll triggers δείχνουν μόνο τη
                            σελίδα που είναι ενεργή τώρα.
                        </p>
                    </article>
                </div>
            </RevealOnScroll>
        </main>
    );
}
