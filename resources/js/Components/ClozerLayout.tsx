import { Head, Link } from '@inertiajs/react';
import ApplicationLogo from '@/Components/ApplicationLogo';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink';
import { useState, useEffect } from 'react';
import { usePage } from '@inertiajs/react';

export default function Layout({ children }: { children: React.ReactNode }) {
    const page = usePage();
    const user = page.props.auth.user as { name: string } | null;
    const [showingNavigationDropdown, setShowNavigationDropdown] = useState(false);

    return (
        <div className="min-h-screen bg-gray-50 dark:bg-gray-900">
            <Head title={page.props.title || 'Clozer'} />

            {/* Navigation */}
            <div className="bg-white dark:bg-gray-800 border-b border-gray-300 dark:border-gray-700">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div className="flex justify-between h-16">
                        <div className="flex">
                            <div className="flex-shrink-0 flex items-center">
                                <Link href="/">
                                    <ApplicationLogo className="h-8 w-auto text-gray-500" />
                                </Link>
                            </div>

                            <div className="hidden sm:ml-6 sm:flex sm:space-x-8">
                                <NavLink href="/items" active={page.url.startsWith('/items')}>
                                    Dressing
                                </NavLink>
                                <NavLink href="/outfits" active={page.url.startsWith('/outfits')}>
                                    Tenues
                                </NavLink>
                                <NavLink href="/wears" active={page.url.startsWith('/wears')}>
                                    Wear Log
                                </NavLink>
                            </div>
                        </div>

                        <div className="flex items-center">
                            {user ? (
                                <div className="ml-3 relative">
                                    <div>
                                        <button
                                            type="button"
                                            className="flex text-sm border-2 border-transparent rounded-full focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all"
                                            onClick={() => setShowNavigationDropdown(!showingNavigationDropdown)}
                                        >
                                            <span className="sr-only">Open user menu</span>
                                            <div className="h-8 w-8 rounded-full bg-indigo-500 flex items-center justify-center text-white font-medium">
                                                {user.name.charAt(0).toUpperCase()}
                                            </div>
                                        </button>
                                    </div>

                                    {showingNavigationDropdown && (
                                        <div className="origin-top-right absolute right-0 mt-2 w-48 rounded-md shadow-lg py-1 bg-white dark:bg-gray-800 ring-1 ring-black ring-opacity-5">
                                            <Link
                                                href={route('profile.edit')}
                                                className="block px-4 py-2 text-sm text-gray-700 dark:text-gray-200"
                                            >
                                                Votre Profil
                                            </Link>
                                            <Link
                                                href={route('logout')}
                                                method="post"
                                                className="block px-4 py-2 text-sm text-red-600 dark:text-red-400"
                                            >
                                                Déconnexion
                                            </Link>
                                        </div>
                                    )}
                                </div>
                            ) : (
                                <div className="flex items-center space-x-4">
                                    <Link
                                        href={route('login')}
                                        className="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white"
                                    >
                                        Connexion
                                    </Link>
                                    <Link
                                        href={route('register')}
                                        className="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white"
                                    >
                                        Inscription
                                    </Link>
                                </div>
                            )}
                        </div>
                    </div>
                </div>
            </div>

            {/* Main Content */}
            <main className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                {children}
            </main>
        </div>
    );
}

function NavLink({
    href,
    active,
    children,
}: {
    href: string;
    active: boolean;
    children: React.ReactNode;
}) {
    return (
        <ResponsiveNavLink
            href={href}
            active={active}
            className={({ isActive }) =>
                (isActive
                    ? 'border-indigo-400 text-gray-900 dark:text-gray-100'
                    : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300')
            }
        >
            {children}
        </ResponsiveNavLink>
    );
}
