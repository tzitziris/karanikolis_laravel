import { Link } from '@inertiajs/react';

const socials = [
    { label: 'Facebook', href: '#', code: 'FB' },
    { label: 'Instagram', href: '#', code: 'IG' },
];

const linkColumns = [
    {
        title: 'Σχολή',
        items: [
            { label: 'Αρχική', href: '/' },
            { label: 'Σχετικά', href: '/about' },
            { label: 'Προπονητές & Αθλητές', href: '/coaches' },
        ],
    },
    {
        title: 'Προπόνηση',
        items: [
            { label: 'Πρόγραμμα', href: '/schedule' },
            { label: 'Νέα', href: '/news' },
        ],
    },
];

export default function Footer() {
    return (
        <footer
            className="relative bg-ink-0 text-bone-dim"
            data-site-footer
            id="site-footer"
        >
            <div className="mx-auto max-w-[1500px] px-5 py-20 sm:px-8 lg:px-12 lg:py-24">
                <div className="grid grid-cols-1 gap-12 lg:grid-cols-[1.7fr_1fr_1fr_1fr]">
                    <div>
                        <p className="text-[10px] font-medium uppercase text-blood">
                            Ελευθερούπολη · Καβάλα
                        </p>
                        <h2 className="mt-5 max-w-full font-display text-[clamp(2.75rem,11vw,4.5rem)] font-black uppercase leading-[0.82] text-bone">
                            Μαχητές
                            <span className="block text-[clamp(2rem,8vw,3.4rem)] text-blood">
                                Ελευθερούπολης
                            </span>
                        </h2>
                        <p className="mt-4 max-w-sm text-sm leading-relaxed text-bone-dim">
                            Πειθαρχία, τεχνική και δύναμη μέσα από την
                            καθημερινή προπόνηση. Ένας χώρος για όσους ζητούν
                            κάτι παραπάνω από γυμναστήριο.
                        </p>
                    </div>

                    {linkColumns.map((column) => (
                        <div key={column.title}>
                            <p className="font-mono text-[10px] font-bold uppercase text-pewter-dim">
                                {column.title}
                            </p>
                            <ul className="mt-5 space-y-3">
                                {column.items.map((item) => (
                                    <li key={item.href}>
                                        <Link
                                            className="group inline-flex min-h-11 min-w-11 items-center gap-2 py-2 font-display text-xl font-black uppercase text-bone-dim transition-colors hover:text-bone"
                                            href={item.href}
                                            prefetch={['mount', 'hover']}
                                        >
                                            <span className="h-px w-0 bg-blood transition-all duration-300 group-hover:w-5" />
                                            {item.label}
                                        </Link>
                                    </li>
                                ))}
                            </ul>
                        </div>
                    ))}

                    <div>
                        <p className="font-mono text-[10px] font-bold uppercase text-pewter-dim">
                            Επικοινωνία
                        </p>
                        <ul className="mt-5 space-y-2 font-mono text-xs leading-relaxed text-bone-dim">
                            <li>Τηλέφωνο</li>
                            <li className="text-bone">2510 000000</li>
                            <li className="pt-2">Email</li>
                            <li className="text-bone">
                                info@maxites-eleftheroupolis.gr
                            </li>
                            <li className="pt-2">Διεύθυνση</li>
                            <li className="text-bone">
                                Ελευθερούπολη, Καβάλα
                            </li>
                        </ul>

                        <div className="mt-8 flex gap-2">
                            {socials.map((social) => (
                                <a
                                    aria-label={social.label}
                                    className="group flex h-11 w-11 items-center justify-center border border-line-strong font-mono text-[11px] font-bold text-bone-dim transition-all duration-300 hover:border-blood hover:bg-blood hover:text-bone focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blood"
                                    href={social.href}
                                    key={social.label}
                                >
                                    {social.code}
                                </a>
                            ))}
                        </div>
                    </div>
                </div>
            </div>

            <div className="border-t border-line">
                <div className="mx-auto flex max-w-[1500px] flex-col gap-3 px-5 py-5 sm:flex-row sm:items-center sm:justify-between sm:px-8 lg:px-12">
                    <p className="font-mono text-[10px] font-bold uppercase text-pewter-dim">
                        © 2026 Μαχητές Ελευθερούπολης
                    </p>
                    <p className="font-mono text-[10px] font-bold uppercase text-pewter-dim">
                        Όλα τα δικαιώματα κατοχυρωμένα
                    </p>
                </div>
            </div>
        </footer>
    );
}
