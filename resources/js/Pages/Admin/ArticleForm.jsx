import { Link, router, useForm, usePage } from '@inertiajs/react';
import { lazy, Suspense, useEffect, useMemo } from 'react';

const RichTextEditor = lazy(() => import('../../Components/Admin/RichTextEditor'));

const emptyBody = {
    content: [
        {
            content: [{ text: '', type: 'text' }],
            type: 'paragraph',
        },
    ],
    type: 'doc',
};

function formatDateForInput(value) {
    return value ?? '';
}

export default function ArticleForm({ article, bodyContract, mode }) {
    const { flash = {} } = usePage().props;
    const isEditing = mode === 'edit';
    const { data, setData, post, put, processing, errors, isDirty } = useForm({
        body: article?.body ?? emptyBody,
        excerpt: article?.excerpt ?? '',
        published_at: formatDateForInput(article?.publishedAt),
        title: article?.title ?? '',
    });

    const title = isEditing ? 'Επεξεργασία άρθρου' : 'Νέο άρθρο';
    const submitLabel = isEditing ? 'Αποθήκευση άρθρου' : 'Δημιουργία άρθρου';
    const action = isEditing ? `/admin/articles/${article.id}` : '/admin/articles';
    const bodyError = Array.isArray(errors.body) ? errors.body[0] : errors.body;

    const metadata = useMemo(() => {
        if (!isEditing) return 'Το άρθρο θα μείνει προσχέδιο μέχρι να δημοσιευτεί από τον πίνακα άρθρων.';

        return article.isVisible
            ? 'Το άρθρο είναι σημειωμένο ως ορατό. Η αποθήκευση εδώ δεν αλλάζει την ορατότητά του.'
            : 'Το άρθρο δεν είναι δημόσια ορατό. Η αποθήκευση εδώ δεν το δημοσιεύει.';
    }, [article, isEditing]);

    useEffect(() => {
        const beforeUnload = (event) => {
            if (!isDirty || processing) return;

            event.preventDefault();
            event.returnValue = '';
        };

        window.addEventListener('beforeunload', beforeUnload);

        return () => window.removeEventListener('beforeunload', beforeUnload);
    }, [isDirty, processing]);

    const leaveSafely = () => {
        if (isDirty && !window.confirm('Υπάρχουν αλλαγές που δεν αποθηκεύτηκαν. Να φύγετε από τη σελίδα;')) {
            return;
        }

        router.visit('/admin');
    };

    const submit = (event) => {
        event.preventDefault();

        const options = {
            preserveScroll: true,
        };

        if (isEditing) {
            put(action, options);
            return;
        }

        post(action, options);
    };

    return (
        <main className="min-h-screen bg-ink-1 text-bone">
            <header className="border-b border-line-strong bg-ink-0">
                <div className="mx-auto flex max-w-[1500px] flex-col gap-6 px-5 py-6 sm:px-8 lg:flex-row lg:items-end lg:justify-between lg:px-12">
                    <div>
                        <p className="font-mono text-xs font-bold uppercase tracking-[0.24em] text-blood">
                            Κείμενο άρθρου
                        </p>
                        <h1 className="mt-3 font-display text-4xl font-black uppercase leading-none sm:text-5xl">
                            {title}
                        </h1>
                        <p className="mt-4 max-w-3xl text-sm leading-6 text-bone-dim">
                            {metadata}
                        </p>
                    </div>

                    <button
                        className="min-h-11 border border-line-strong px-4 font-display text-base font-black uppercase text-bone transition hover:border-blood hover:text-blood"
                        type="button"
                        onClick={leaveSafely}
                    >
                        Πίσω στον πίνακα
                    </button>
                </div>
            </header>

            <section className="mx-auto max-w-[1200px] px-5 py-8 sm:px-8 lg:px-12">
                {flash.success ? (
                    <p className="mb-6 border border-blood bg-ink-2 px-4 py-3 text-sm font-bold text-blood-deep" role="status" aria-live="polite">
                        {flash.success}
                    </p>
                ) : null}

                {isEditing ? (
                    <div className="mb-6 grid gap-3 border border-line-strong bg-ink-2 p-4 text-sm sm:grid-cols-2">
                        <div>
                            <p className="font-mono text-xs uppercase text-pewter">Διεύθυνση</p>
                            <p className="mt-1 text-bone">{article.slug}</p>
                        </div>
                        <div>
                            <p className="font-mono text-xs uppercase text-pewter">Κατάσταση</p>
                            <p className="mt-1 text-bone">{article.isVisible ? 'Ορατό από τον πίνακα' : 'Κρυφό από τον πίνακα'}</p>
                        </div>
                    </div>
                ) : null}

                <form className="grid gap-6" onSubmit={submit}>
                    <label className="grid gap-2" htmlFor="article-title">
                        <span className="text-sm font-bold">Τίτλος</span>
                        <input
                            className="min-h-12 border border-line-strong bg-ink-2 px-4 text-base text-bone outline-none transition focus:border-blood focus:ring-2 focus:ring-blood-glow"
                            id="article-title"
                            type="text"
                            value={data.title}
                            onChange={(event) => setData('title', event.target.value)}
                            required
                        />
                        {errors.title ? <span className="text-sm text-blood-deep" role="alert">{errors.title}</span> : null}
                    </label>

                    <label className="grid gap-2" htmlFor="article-excerpt">
                        <span className="text-sm font-bold">Σύνοψη</span>
                        <textarea
                            className="min-h-32 border border-line-strong bg-ink-2 px-4 py-3 text-base leading-7 text-bone outline-none transition focus:border-blood focus:ring-2 focus:ring-blood-glow"
                            id="article-excerpt"
                            value={data.excerpt}
                            onChange={(event) => setData('excerpt', event.target.value)}
                            required
                        />
                        {errors.excerpt ? <span className="text-sm text-blood-deep" role="alert">{errors.excerpt}</span> : null}
                    </label>

                    <label className="grid gap-2 sm:max-w-md" htmlFor="article-published-at">
                        <span className="text-sm font-bold">Ημερομηνία δημοσίευσης</span>
                        <input
                            className="min-h-12 border border-line-strong bg-ink-2 px-4 text-base text-bone outline-none transition focus:border-blood focus:ring-2 focus:ring-blood-glow"
                            id="article-published-at"
                            type="datetime-local"
                            value={data.published_at}
                            onChange={(event) => setData('published_at', event.target.value)}
                        />
                        {errors.published_at ? <span className="text-sm text-blood-deep" role="alert">{errors.published_at}</span> : null}
                    </label>

                    <section className="grid gap-2" aria-labelledby="body-label">
                        <div>
                            <h2 id="body-label" className="text-sm font-bold">Σώμα άρθρου</h2>
                            <p className="mt-1 text-sm leading-6 text-bone-dim">
                                Αποθηκεύεται ως δομημένο κείμενο. Το HTML δημιουργείται μόνο στον server όταν εμφανιστεί το άρθρο.
                            </p>
                        </div>
                        <Suspense fallback={<div className="border border-line-strong bg-ink-2 p-6 text-bone-dim">Φόρτωση επεξεργαστή...</div>}>
                            <RichTextEditor
                                bodyContract={bodyContract}
                                error={bodyError}
                                value={data.body}
                                onChange={(body) => setData('body', body)}
                            />
                        </Suspense>
                    </section>

                    <div className="flex flex-wrap gap-3 border-t border-line-strong pt-6">
                        <button
                            className="min-h-12 border border-blood bg-blood px-5 font-display text-lg font-black uppercase text-ink-0 transition hover:bg-blood-deep disabled:cursor-not-allowed disabled:opacity-60"
                            type="submit"
                            disabled={processing}
                        >
                            {processing ? 'Αποθήκευση...' : submitLabel}
                        </button>
                        <Link
                            className="inline-flex min-h-12 items-center border border-line-strong px-5 font-display text-lg font-black uppercase text-bone transition hover:border-blood hover:text-blood"
                            href="/admin"
                            onClick={(event) => {
                                if (isDirty && !window.confirm('Υπάρχουν αλλαγές που δεν αποθηκεύτηκαν. Να φύγετε από τη σελίδα;')) {
                                    event.preventDefault();
                                }
                            }}
                        >
                            Ακύρωση
                        </Link>
                    </div>
                </form>
            </section>
        </main>
    );
}

ArticleForm.layout = (page) => page;
