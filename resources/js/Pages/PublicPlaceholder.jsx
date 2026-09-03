import HeroIntro from '../Components/HeroIntro';
import RevealOnScroll from '../Components/RevealOnScroll';

export default function PublicPlaceholder({ eyebrow, message, title }) {
    return (
        <div className="min-h-screen bg-ink-1">
            <HeroIntro eyebrow={eyebrow} summary={message} title={title} />

            <RevealOnScroll className="px-6 py-14 sm:px-10 sm:py-20">
                <div className="mx-auto max-w-5xl border border-line-strong bg-ink-2 p-6">
                    <h2
                        className="font-display text-3xl font-black uppercase text-bone"
                        data-reveal-item
                    >
                        Έρχεται σύντομα
                    </h2>
                    <p
                        className="mt-3 max-w-2xl leading-7 text-bone-dim"
                        data-reveal-item
                    >
                        Αυτό το σημείο υπάρχει μόνο για να δοκιμαστεί η
                        πλοήγηση του κελύφους. Το πραγματικό περιεχόμενο θα
                        αντικαταστήσει αυτή την προσωρινή σελίδα.
                    </p>
                </div>
            </RevealOnScroll>
        </div>
    );
}
