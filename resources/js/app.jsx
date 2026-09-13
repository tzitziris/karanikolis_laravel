import '../css/app.css';

import { createInertiaApp, Head, usePage } from '@inertiajs/react';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createRoot } from 'react-dom/client';
import SiteShell from './Layouts/SiteShell';

// The server writes the whole head, because crawlers do not run JavaScript.
// A client-side visit replaces only the body, so the browser tab would keep
// the first page's title — this puts it back in step, from the same value.
function PageTitle() {
    const title = usePage().props.meta?.title;

    return title ? <Head title={title} /> : null;
}

createInertiaApp({
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.jsx`,
            import.meta.glob('./Pages/**/*.jsx'),
        ),
    setup({ el, App, props }) {
        createRoot(el).render(
            <App {...props}>
                {({ Component, key, props }) => {
                    const page = <Component key={key} {...props} />;
                    const layout =
                        Component.layout ??
                        ((children) => <SiteShell>{children}</SiteShell>);

                    return (
                        <>
                            <PageTitle />
                            {layout(page)}
                        </>
                    );
                }}
            </App>,
        );
    },
});
