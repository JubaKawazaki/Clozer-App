import { createRoot } from 'react-dom/client';
import { createInertiaApp } from '@inertiajs/react';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import ClozerLayout from '@/Components/ClozerLayout';

const appName = import.meta.env.VITE_APP_NAME || 'Clozer';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => resolvePageComponent(`./Pages/${name}.tsx`, import.meta.glob('./Pages/**/*.tsx')),
    progress: {
        color: '#4B5563',
    },
    setup({ el, App, props }) {
        const root = createRoot(el);
        root.render(
            <ClozerLayout {...props}>
                <App {...props} />
            </ClozerLayout>
        );
    },
});
