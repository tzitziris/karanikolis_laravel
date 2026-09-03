import '../css/app.css';

import { createInertiaApp } from '@inertiajs/react';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createRoot } from 'react-dom/client';
import { useEffect } from 'react';

function MountedApp({ App, props }) {
    useEffect(() => {
        document.getElementById('readable-fallback')?.remove();
    }, []);

    return <App {...props} />;
}

createInertiaApp({
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.jsx`,
            import.meta.glob('./Pages/**/*.jsx'),
        ),
    setup({ el, App, props }) {
        createRoot(el).render(<MountedApp App={App} props={props} />);
    },
});
