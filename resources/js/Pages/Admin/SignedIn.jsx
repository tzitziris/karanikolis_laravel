import { useForm } from '@inertiajs/react';

export default function SignedIn() {
    const { post, processing } = useForm();

    return (
        <main className="grid min-h-screen place-items-center bg-ink-1 px-5 py-10 text-bone">
            <section className="w-full max-w-lg border border-line-strong bg-ink-2 p-8 shadow-2xl shadow-black/40 sm:p-10" aria-labelledby="admin-signed-in-title">
                <p className="font-mono text-xs uppercase tracking-[0.24em] text-blood">Περιοχή διαχείρισης</p>
                <h1 id="admin-signed-in-title" className="mt-4 font-display text-4xl font-black uppercase leading-none text-bone sm:text-5xl">
                    Είστε συνδεδεμένοι
                </h1>
                <p className="mt-4 text-sm leading-6 text-bone-dim">
                    Η διαχείριση ειδήσεων θα προστεθεί στο επόμενο βήμα.
                </p>
                <button
                    className="mt-8 min-h-12 border border-blood px-5 font-display text-lg font-black uppercase text-blood transition hover:bg-blood hover:text-ink-0 disabled:cursor-not-allowed disabled:opacity-60"
                    type="button"
                    disabled={processing}
                    onClick={() => post('/admin/logout')}
                >
                    Αποσύνδεση
                </button>
            </section>
        </main>
    );
}

SignedIn.layout = (page) => page;
