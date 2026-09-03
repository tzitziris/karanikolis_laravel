import '../css/app.css';

import { createInertiaApp } from '@inertiajs/react';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createRoot } from 'react-dom/client';
import SiteShell from './Layouts/SiteShell';

createInertiaApp({
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.jsx`,
            import.meta.glob('./Pages/**/*.jsx'),
        ),
    setup({ el, App, props }) {
        createRoot(el).render(
            <App {...props}>
                {({ Component, key, props }) => (
                    <SiteShell>
                        <Component key={key} {...props} />
                    </SiteShell>
                )}
            </App>,
        );
    },
});
