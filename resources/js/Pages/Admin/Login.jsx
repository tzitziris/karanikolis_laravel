import { useForm } from '@inertiajs/react';

export default function Login() {
    const { data, setData, post, processing, errors, clearErrors } = useForm({
        email: '',
        password: '',
    });

    const handleSubmit = (event) => {
        event.preventDefault();
        clearErrors();

        post('/admin/login', {
            onFinish: () => setData('password', ''),
        });
    };

    const error = errors.email || errors.password || '';

    return (
        <main className="grid min-h-screen place-items-center bg-ink-1 px-5 py-10 text-bone">
            <section className="w-full max-w-md border border-line-strong bg-ink-2 p-8 shadow-2xl shadow-black/40 sm:p-10" aria-labelledby="admin-login-title">
                <p className="font-mono text-xs uppercase tracking-[0.24em] text-blood">Μαχητές Ελευθερούπολης</p>
                <h1 id="admin-login-title" className="mt-4 font-display text-4xl font-black uppercase leading-none text-bone sm:text-5xl">
                    Σύνδεση διαχείρισης
                </h1>
                <p className="mt-4 text-sm leading-6 text-bone-dim">
                    Συνδεθείτε για να διαχειριστείτε τις ειδήσεις της σχολής.
                </p>

                <form className="mt-8 grid gap-5" onSubmit={handleSubmit} aria-busy={processing}>
                    <label className="grid gap-2 text-sm font-bold text-bone" htmlFor="admin-email">
                        <span>Email</span>
                        <input
                            id="admin-email"
                            className="min-h-12 border border-line-strong bg-ink-1 px-4 text-base font-normal text-bone outline-none transition focus:border-blood focus:ring-2 focus:ring-blood-glow"
                            type="email"
                            value={data.email}
                            onChange={(event) => setData('email', event.target.value)}
                            autoComplete="username"
                            required
                        />
                    </label>

                    <label className="grid gap-2 text-sm font-bold text-bone" htmlFor="admin-password">
                        <span>Κωδικός</span>
                        <input
                            id="admin-password"
                            className="min-h-12 border border-line-strong bg-ink-1 px-4 text-base font-normal text-bone outline-none transition focus:border-blood focus:ring-2 focus:ring-blood-glow"
                            type="password"
                            value={data.password}
                            onChange={(event) => setData('password', event.target.value)}
                            autoComplete="current-password"
                            required
                        />
                    </label>

                    <p className="min-h-6 text-sm leading-6 text-blood-deep" role="alert" aria-live="polite">
                        {error}
                    </p>

                    <button
                        className="min-h-12 border border-blood bg-blood px-5 font-display text-lg font-black uppercase text-ink-0 transition hover:bg-blood-deep disabled:cursor-not-allowed disabled:opacity-60"
                        type="submit"
                        disabled={processing}
                    >
                        {processing ? 'Σύνδεση...' : 'Σύνδεση'}
                    </button>
                </form>
            </section>
        </main>
    );
}

Login.layout = (page) => page;
