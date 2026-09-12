import { Link, router, usePage } from '@inertiajs/react';
import { useMemo, useState } from 'react';

const stateClasses = {
    hidden: 'border-line-strong bg-ink-4 text-bone-dim',
    live: 'border-blood bg-blood text-ink-0',
    scheduled: 'border-blood-deep bg-ink-3 text-blood-deep',
    undated: 'border-blood-glow bg-ink-3 text-bone',
};

function countLabel(count, singular, plural) {
    return Number(count) === 1 ? `1 ${singular}` : `${Number(count) || 0} ${plural}`;
}

function mediaSummary(article) {
    return [
        countLabel(article.imageCount, 'φωτογραφία', 'φωτογραφίες'),
        countLabel(article.videoCount, 'βίντεο', 'βίντεο'),
    ].join(' · ');
}

function deleteMessage(article) {
    return `Να διαγραφεί οριστικά το άρθρο «${article.title}»;\n\nΘα χαθούν μαζί του ${mediaSummary(article)}. Η διαγραφή δεν αναιρείται.`;
}

export default function Dashboard({ articles = [], user }) {
    const { flash = {} } = usePage().props;
    const [workingId, setWorkingId] = useState(null);
    const [loggingOut, setLoggingOut] = useState(false);

    const counts = useMemo(() => {
        return articles.reduce(
            (totals, article) => ({
                ...totals,
                [article.state.key]: (totals[article.state.key] ?? 0) + 1,
            }),
            { hidden: 0, live: 0, scheduled: 0, undated: 0 },
        );
    }, [articles]);

    const publish = (article) => {
        setWorkingId(article.id);
        router.patch(`/admin/articles/${article.id}/publish`, {}, {
            preserveScroll: true,
            onFinish: () => setWorkingId(null),
        });
    };

    const unpublish = (article) => {
        setWorkingId(article.id);
        router.patch(`/admin/articles/${article.id}/unpublish`, {}, {
            preserveScroll: true,
            onFinish: () => setWorkingId(null),
        });
    };

    const destroy = (article) => {
        if (!window.confirm(deleteMessage(article))) return;

        setWorkingId(article.id);
        router.delete(`/admin/articles/${article.id}`, {
            preserveScroll: true,
            onFinish: () => setWorkingId(null),
        });
    };

    const logout = () => {
        setLoggingOut(true);

        router.post('/admin/logout', {}, {
            onFinish: () => setLoggingOut(false),
        });
    };

    const busy = workingId !== null || loggingOut;

    return (
        <main className="min-h-screen bg-ink-1 text-bone">
            <header className="border-b border-line-strong bg-ink-0">
                <div className="mx-auto flex max-w-[1500px] flex-col gap-6 px-5 py-6 sm:px-8 lg:flex-row lg:items-center lg:justify-between lg:px-12">
                    <div>
                        <p className="font-mono text-xs font-bold uppercase tracking-[0.24em] text-blood">
                            Διαχείριση ειδήσεων
                        </p>
                        <h1 className="mt-3 font-display text-4xl font-black uppercase leading-none sm:text-5xl">
                            Πίνακας άρθρων
                        </h1>
                    </div>

                    <div className="flex flex-col gap-3 text-sm text-bone-dim sm:flex-row sm:items-center">
                        <Link
                            className="inline-flex min-h-11 items-center justify-center border border-blood bg-blood px-4 font-display text-base font-black uppercase text-ink-0 transition hover:bg-blood-deep"
                            href="/admin/articles/create"
                        >
                            Νέο άρθρο
                        </Link>
                        <span title={user?.email}>{user?.email}</span>
                        <button
                            className="min-h-11 border border-line-strong px-4 font-display text-base font-black uppercase text-bone transition hover:border-blood hover:text-blood disabled:cursor-not-allowed disabled:opacity-60"
                            type="button"
                            onClick={logout}
                            disabled={busy}
                        >
                            {loggingOut ? 'Αποσύνδεση...' : 'Αποσύνδεση'}
                        </button>
                    </div>
                </div>
            </header>

            <section className="mx-auto max-w-[1500px] px-5 py-8 sm:px-8 lg:px-12">
                {flash.success ? (
                    <p className="mb-6 border border-blood bg-ink-2 px-4 py-3 text-sm font-bold text-blood-deep" role="status" aria-live="polite">
                        {flash.success}
                    </p>
                ) : null}

                <div className="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                    <div className="border border-line-strong bg-ink-2 p-4">
                        <p className="font-mono text-xs uppercase text-pewter">Ζωντανά</p>
                        <p className="mt-2 font-display text-4xl font-black">{counts.live}</p>
                    </div>
                    <div className="border border-line-strong bg-ink-2 p-4">
                        <p className="font-mono text-xs uppercase text-pewter">Κρυφά</p>
                        <p className="mt-2 font-display text-4xl font-black">{counts.hidden}</p>
                    </div>
                    <div className="border border-line-strong bg-ink-2 p-4">
                        <p className="font-mono text-xs uppercase text-pewter">Χωρίς ημερομηνία</p>
                        <p className="mt-2 font-display text-4xl font-black">{counts.undated}</p>
                    </div>
                    <div className="border border-line-strong bg-ink-2 p-4">
                        <p className="font-mono text-xs uppercase text-pewter">Για αργότερα</p>
                        <p className="mt-2 font-display text-4xl font-black">{counts.scheduled}</p>
                    </div>
                </div>

                <section className="mt-8 border border-line-strong bg-ink-2" aria-labelledby="articles-heading">
                    <div className="border-b border-line-strong p-5 sm:p-6">
                        <h2 id="articles-heading" className="font-display text-3xl font-black uppercase">
                            Όλα τα άρθρα
                        </h2>
                        <p className="mt-2 max-w-3xl text-sm leading-6 text-bone-dim">
                            Εδώ φαίνονται και τα άρθρα που δεν βλέπει το κοινό: προσχέδια, μελλοντικές δημοσιεύσεις και άρθρα χωρίς ημερομηνία.
                        </p>
                    </div>

                    {articles.length === 0 ? (
                        <p className="p-6 text-bone-dim">Δεν υπάρχουν άρθρα ακόμη.</p>
                    ) : (
                        <div>
                            {articles.map((article, index) => {
                                const articleBusy = workingId === article.id;
                                const canUnpublish = article.isVisible;

                                return (
                                    <article className={`grid gap-5 p-5 sm:p-6 xl:grid-cols-[minmax(0,1fr)_20rem] ${index > 0 ? 'border-t border-line-strong' : ''}`} key={article.id}>
                                        <div className="min-w-0">
                                            <div className="flex flex-wrap items-center gap-3">
                                                <span className={`border px-3 py-1 text-xs font-black uppercase ${stateClasses[article.state.key]}`}>
                                                    {article.state.label}
                                                </span>
                                                <span className="font-mono text-xs uppercase text-pewter">
                                                    {article.slug}
                                                </span>
                                            </div>

                                            <h3 className="mt-4 font-display text-3xl font-black uppercase leading-tight [overflow-wrap:normal] [word-break:normal]">
                                                {article.title}
                                            </h3>
                                            <p className="mt-3 max-w-4xl text-sm leading-6 text-bone-dim">
                                                {article.excerpt}
                                            </p>

                                            <dl className="mt-5 grid gap-3 text-sm sm:grid-cols-2 xl:grid-cols-4">
                                                <div>
                                                    <dt className="font-mono text-xs uppercase text-pewter">Κατάσταση</dt>
                                                    <dd className="mt-1 text-bone">{article.state.detail}</dd>
                                                </div>
                                                <div>
                                                    <dt className="font-mono text-xs uppercase text-pewter">Ημερομηνία δημοσίευσης</dt>
                                                    <dd className="mt-1 text-bone">{article.publishedDate ?? 'Δεν έχει οριστεί'}</dd>
                                                </div>
                                                <div>
                                                    <dt className="font-mono text-xs uppercase text-pewter">Τελευταία αλλαγή</dt>
                                                    <dd className="mt-1 text-bone">{article.updatedDate ?? 'Άγνωστο'}</dd>
                                                </div>
                                                <div>
                                                    <dt className="font-mono text-xs uppercase text-pewter">Υλικό</dt>
                                                    <dd className="mt-1 text-bone">{mediaSummary(article)}</dd>
                                                </div>
                                            </dl>
                                        </div>

                                        <div className="flex flex-col gap-3 xl:items-stretch xl:justify-center">
                                            {canUnpublish ? (
                                                <button
                                                    className="min-h-11 border border-line-strong px-4 font-display text-base font-black uppercase text-bone transition hover:border-blood hover:text-blood disabled:cursor-not-allowed disabled:opacity-60"
                                                    type="button"
                                                    onClick={() => unpublish(article)}
                                                    disabled={busy}
                                                >
                                                    {articleBusy ? 'Αποθήκευση...' : 'Απόσυρση'}
                                                </button>
                                            ) : (
                                                <button
                                                    className="min-h-11 border border-blood bg-blood px-4 font-display text-base font-black uppercase text-ink-0 transition hover:bg-blood-deep disabled:cursor-not-allowed disabled:opacity-60"
                                                    type="button"
                                                    onClick={() => publish(article)}
                                                    disabled={busy}
                                                >
                                                    {articleBusy ? 'Αποθήκευση...' : 'Δημοσίευση'}
                                                </button>
                                            )}

                                            <Link
                                                className="inline-flex min-h-11 items-center justify-center border border-line-strong px-4 font-display text-base font-black uppercase text-bone transition hover:border-blood hover:text-blood"
                                                href={`/admin/articles/${article.id}/edit`}
                                            >
                                                Επεξεργασία
                                            </Link>

                                            <button
                                                className="min-h-11 border border-line-strong px-4 font-display text-base font-black uppercase text-bone-dim transition hover:border-blood-deep hover:text-blood-deep disabled:cursor-not-allowed disabled:opacity-60"
                                                type="button"
                                                onClick={() => destroy(article)}
                                                disabled={busy}
                                            >
                                                {articleBusy ? 'Διαγραφή...' : 'Διαγραφή'}
                                            </button>
                                        </div>
                                    </article>
                                );
                            })}
                        </div>
                    )}
                </section>
            </section>
        </main>
    );
}

Dashboard.layout = (page) => page;
