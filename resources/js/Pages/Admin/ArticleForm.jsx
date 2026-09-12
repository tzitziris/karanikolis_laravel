import { Link, router, useForm, usePage } from '@inertiajs/react';
import { lazy, Suspense, useEffect, useMemo, useRef, useState } from 'react';
import ArticleImage from '../../Components/News/ArticleImage';

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

export default function ArticleForm({ article, bodyContract, mode, uploadLimits = {} }) {
    const { errors: pageErrors = {}, flash = {} } = usePage().props;
    const isEditing = mode === 'edit';
    const coverInputRef = useRef(null);
    const galleryInputRef = useRef(null);
    const [coverAltText, setCoverAltText] = useState(article?.coverImageAltText ?? '');
    const [coverLocalError, setCoverLocalError] = useState('');
    const [coverProcessing, setCoverProcessing] = useState(false);
    const [galleryAltText, setGalleryAltText] = useState('');
    const [galleryItems, setGalleryItems] = useState(article?.gallery ?? []);
    const [galleryLocalError, setGalleryLocalError] = useState('');
    const [galleryProcessing, setGalleryProcessing] = useState(false);
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
    const coverError = coverLocalError || pageErrors.cover_photo || pageErrors.photo;
    const galleryError = galleryLocalError || pageErrors.gallery_photo || pageErrors.gallery || pageErrors.photo;
    const maxUploadBytes = Number.isInteger(uploadLimits.maxBytes) ? uploadLimits.maxBytes : null;

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

    useEffect(() => {
        setCoverAltText(article?.coverImageAltText ?? '');
        setGalleryItems(article?.gallery ?? []);
    }, [article?.coverImageAltText, article?.gallery]);

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

    const mediaOptions = (setter) => ({
        forceFormData: true,
        preserveScroll: true,
        preserveState: true,
        onFinish: () => setter(false),
    });

    const uploadCover = (event) => {
        event.preventDefault();
        const photo = coverInputRef.current?.files?.[0];

        if (!photo || !isEditing) return;
        if (maxUploadBytes && photo.size > maxUploadBytes) {
            setCoverLocalError('Η φωτογραφία είναι πολύ μεγάλη. Ανεβάστε μικρότερο αρχείο.');
            return;
        }

        const formData = new FormData();
        formData.append('photo', photo);
        formData.append('alt_text', coverAltText);
        setCoverLocalError('');
        setCoverProcessing(true);

        router.post(`/admin/articles/${article.id}/cover`, formData, {
            ...mediaOptions(setCoverProcessing),
            onSuccess: () => {
                if (coverInputRef.current) coverInputRef.current.value = '';
            },
        });
    };

    const removeCover = () => {
        if (!isEditing || !window.confirm('Να αφαιρεθεί η φωτογραφία εξωφύλλου;')) return;

        setCoverProcessing(true);
        router.delete(`/admin/articles/${article.id}/cover`, mediaOptions(setCoverProcessing));
    };

    const uploadGalleryImage = (event) => {
        event.preventDefault();
        const photo = galleryInputRef.current?.files?.[0];

        if (!photo || !isEditing) return;
        if (maxUploadBytes && photo.size > maxUploadBytes) {
            setGalleryLocalError('Η φωτογραφία είναι πολύ μεγάλη. Ανεβάστε μικρότερο αρχείο.');
            return;
        }

        const formData = new FormData();
        formData.append('photo', photo);
        formData.append('alt_text', galleryAltText);
        setGalleryLocalError('');
        setGalleryProcessing(true);

        router.post(`/admin/articles/${article.id}/gallery`, formData, {
            ...mediaOptions(setGalleryProcessing),
            onSuccess: () => {
                setGalleryAltText('');
                if (galleryInputRef.current) galleryInputRef.current.value = '';
            },
        });
    };

    const moveGalleryImage = (index, direction) => {
        const targetIndex = index + direction;
        if (targetIndex < 0 || targetIndex >= galleryItems.length) return;

        setGalleryItems((items) => {
            const reordered = [...items];
            [reordered[index], reordered[targetIndex]] = [reordered[targetIndex], reordered[index]];
            return reordered;
        });
    };

    const updateGalleryAltText = (id, altText) => {
        setGalleryItems((items) =>
            items.map((item) => (item.id === id ? { ...item, altText } : item)),
        );
    };

    const saveGalleryOrder = () => {
        if (!isEditing) return;

        setGalleryProcessing(true);
        router.put(`/admin/articles/${article.id}/gallery`, {
            images: galleryItems.map((item) => ({
                alt_text: item.altText ?? '',
                id: item.id,
            })),
        }, {
            preserveScroll: true,
            preserveState: true,
            onFinish: () => setGalleryProcessing(false),
        });
    };

    const removeGalleryImage = (image) => {
        if (!isEditing || !window.confirm('Να αφαιρεθεί αυτή η φωτογραφία από τη συλλογή;')) return;

        setGalleryProcessing(true);
        router.delete(`/admin/articles/${article.id}/gallery/${image.id}`, {
            preserveScroll: true,
            preserveState: true,
            onFinish: () => setGalleryProcessing(false),
        });
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

                {isEditing ? (
                    <div className="mb-8 grid gap-6">
                        <section className="border border-line-strong bg-ink-2 p-4 sm:p-5" aria-labelledby="cover-heading">
                            <div className="grid gap-5 lg:grid-cols-[18rem_minmax(0,1fr)]">
                                <div className="aspect-[4/3] overflow-hidden border border-line-strong bg-ink-3">
                                    {article.coverImage ? (
                                        <ArticleImage
                                            alt={article.coverImageAltText ?? article.title}
                                            className="h-full w-full object-cover"
                                            image={article.coverImage}
                                            slot="card"
                                        />
                                    ) : (
                                        <div className="flex h-full items-center justify-center px-4 text-center font-mono text-xs uppercase text-pewter">
                                            Δεν έχει εξώφυλλο
                                        </div>
                                    )}
                                </div>

                                <div>
                                    <p className="font-mono text-xs uppercase text-blood">Φωτογραφία εξωφύλλου</p>
                                    <h2 id="cover-heading" className="mt-2 font-display text-3xl font-black uppercase">
                                        Εξώφυλλο άρθρου
                                    </h2>
                                    <form className="mt-5 grid gap-4" onSubmit={uploadCover}>
                                        <label className="grid gap-2" htmlFor="cover-alt-text">
                                            <span className="text-sm font-bold">Περιγραφή φωτογραφίας</span>
                                            <input
                                                className="min-h-11 border border-line-strong bg-ink-1 px-3 text-sm text-bone outline-none transition focus:border-blood focus:ring-2 focus:ring-blood-glow"
                                                id="cover-alt-text"
                                                type="text"
                                                value={coverAltText}
                                                onChange={(event) => setCoverAltText(event.target.value)}
                                            />
                                        </label>
                                        <label className="grid gap-2" htmlFor="cover-photo">
                                            <span className="text-sm font-bold">Νέα φωτογραφία</span>
                                            <input
                                                accept="image/jpeg,image/png"
                                                className="min-h-11 border border-line-strong bg-ink-1 px-3 py-2 text-sm text-bone file:mr-4 file:border-0 file:bg-blood file:px-3 file:py-2 file:font-mono file:text-xs file:font-bold file:uppercase file:text-ink-0"
                                                id="cover-photo"
                                                ref={coverInputRef}
                                                type="file"
                                            />
                                        </label>
                                        {coverError ? <p className="text-sm text-blood-deep" role="alert">{coverError}</p> : null}
                                        <div className="flex flex-wrap gap-3">
                                            <button
                                                className="min-h-11 border border-blood bg-blood px-4 font-display text-base font-black uppercase text-ink-0 transition hover:bg-blood-deep disabled:cursor-not-allowed disabled:opacity-60"
                                                disabled={coverProcessing}
                                                type="submit"
                                            >
                                                {coverProcessing ? 'Ανέβασμα...' : 'Αποθήκευση εξωφύλλου'}
                                            </button>
                                            {article.coverImage ? (
                                                <button
                                                    className="min-h-11 border border-line-strong px-4 font-display text-base font-black uppercase text-bone transition hover:border-blood hover:text-blood disabled:cursor-not-allowed disabled:opacity-60"
                                                    disabled={coverProcessing}
                                                    onClick={removeCover}
                                                    type="button"
                                                >
                                                    Αφαίρεση εξωφύλλου
                                                </button>
                                            ) : null}
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </section>

                        <section className="border border-line-strong bg-ink-2 p-4 sm:p-5" aria-labelledby="gallery-heading">
                            <div className="grid gap-5 lg:grid-cols-[minmax(0,1fr)_20rem] lg:items-start">
                                <div>
                                    <p className="font-mono text-xs uppercase text-blood">Συλλογή φωτογραφιών</p>
                                    <h2 id="gallery-heading" className="mt-2 font-display text-3xl font-black uppercase">
                                        Φωτογραφίες μέσα στο άρθρο
                                    </h2>

                                    {galleryItems.length ? (
                                        <div className="mt-5 grid gap-4">
                                            {galleryItems.map((image, index) => (
                                                <div className="grid gap-4 border border-line-strong bg-ink-1 p-3 sm:grid-cols-[10rem_minmax(0,1fr)]" key={image.id}>
                                                    <div className="aspect-[4/3] overflow-hidden bg-ink-3">
                                                        <ArticleImage
                                                            alt={image.altText ?? article.title}
                                                            className="h-full w-full object-cover"
                                                            image={image.image}
                                                            slot="card"
                                                        />
                                                    </div>
                                                    <div className="grid gap-3">
                                                        <label className="grid gap-2" htmlFor={`gallery-alt-${image.id}`}>
                                                            <span className="text-sm font-bold">Περιγραφή φωτογραφίας</span>
                                                            <input
                                                                className="min-h-11 border border-line-strong bg-ink-2 px-3 text-sm text-bone outline-none transition focus:border-blood focus:ring-2 focus:ring-blood-glow"
                                                                id={`gallery-alt-${image.id}`}
                                                                type="text"
                                                                value={image.altText ?? ''}
                                                                onChange={(event) => updateGalleryAltText(image.id, event.target.value)}
                                                            />
                                                        </label>
                                                        <div className="flex flex-wrap gap-2">
                                                            <button
                                                                className="min-h-10 border border-line-strong px-3 font-mono text-xs font-bold uppercase text-bone transition hover:border-blood hover:text-blood disabled:cursor-not-allowed disabled:opacity-40"
                                                                disabled={galleryProcessing || index === 0}
                                                                onClick={() => moveGalleryImage(index, -1)}
                                                                type="button"
                                                            >
                                                                Πάνω
                                                            </button>
                                                            <button
                                                                className="min-h-10 border border-line-strong px-3 font-mono text-xs font-bold uppercase text-bone transition hover:border-blood hover:text-blood disabled:cursor-not-allowed disabled:opacity-40"
                                                                disabled={galleryProcessing || index === galleryItems.length - 1}
                                                                onClick={() => moveGalleryImage(index, 1)}
                                                                type="button"
                                                            >
                                                                Κάτω
                                                            </button>
                                                            <button
                                                                className="min-h-10 border border-line-strong px-3 font-mono text-xs font-bold uppercase text-bone transition hover:border-blood hover:text-blood disabled:cursor-not-allowed disabled:opacity-40"
                                                                disabled={galleryProcessing}
                                                                onClick={() => removeGalleryImage(image)}
                                                                type="button"
                                                            >
                                                                Αφαίρεση
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            ))}
                                            {galleryError ? <p className="text-sm text-blood-deep" role="alert">{galleryError}</p> : null}
                                            <button
                                                className="min-h-11 justify-self-start border border-blood bg-blood px-4 font-display text-base font-black uppercase text-ink-0 transition hover:bg-blood-deep disabled:cursor-not-allowed disabled:opacity-60"
                                                disabled={galleryProcessing}
                                                onClick={saveGalleryOrder}
                                                type="button"
                                            >
                                                {galleryProcessing ? 'Αποθήκευση...' : 'Αποθήκευση σειράς'}
                                            </button>
                                        </div>
                                    ) : (
                                        <p className="mt-5 border border-line-strong bg-ink-1 p-4 text-sm text-bone-dim">
                                            Δεν υπάρχουν φωτογραφίες στη συλλογή.
                                        </p>
                                    )}
                                </div>

                                <form className="grid gap-4 border border-line-strong bg-ink-1 p-4" onSubmit={uploadGalleryImage}>
                                    <label className="grid gap-2" htmlFor="gallery-alt-text">
                                        <span className="text-sm font-bold">Περιγραφή φωτογραφίας</span>
                                        <input
                                            className="min-h-11 border border-line-strong bg-ink-2 px-3 text-sm text-bone outline-none transition focus:border-blood focus:ring-2 focus:ring-blood-glow"
                                            id="gallery-alt-text"
                                            type="text"
                                            value={galleryAltText}
                                            onChange={(event) => setGalleryAltText(event.target.value)}
                                        />
                                    </label>
                                    <label className="grid gap-2" htmlFor="gallery-photo">
                                        <span className="text-sm font-bold">Νέα φωτογραφία</span>
                                        <input
                                            accept="image/jpeg,image/png"
                                            className="min-h-11 border border-line-strong bg-ink-2 px-3 py-2 text-sm text-bone file:mr-4 file:border-0 file:bg-blood file:px-3 file:py-2 file:font-mono file:text-xs file:font-bold file:uppercase file:text-ink-0"
                                            id="gallery-photo"
                                            ref={galleryInputRef}
                                            type="file"
                                        />
                                    </label>
                                    {galleryError && !galleryItems.length ? <p className="text-sm text-blood-deep" role="alert">{galleryError}</p> : null}
                                    <button
                                        className="min-h-11 border border-blood bg-blood px-4 font-display text-base font-black uppercase text-ink-0 transition hover:bg-blood-deep disabled:cursor-not-allowed disabled:opacity-60"
                                        disabled={galleryProcessing}
                                        type="submit"
                                    >
                                        {galleryProcessing ? 'Ανέβασμα...' : 'Προσθήκη στη συλλογή'}
                                    </button>
                                </form>
                            </div>
                        </section>
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
