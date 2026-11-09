import { Head } from '@inertiajs/react';
import { useState } from 'react';
import { usePage } from '@inertiajs/react';

export default function Home() {
    const page = usePage();
    const user = page.props.auth.user as { name: string } | null;
    const [suggestion, setSuggestion] = useState<any>(null);
    const [loading, setLoading] = useState(true);

    // Placeholder pour la suggestion du jour (à remplacer par un appel API backend)
    useEffect(() => {
        // TODO: Appeler l'API /api/suggestion une fois le backend implémenté
        // Pour l'instant, on simule une suggestion
        setTimeout(() => {
            setSuggestion({
                id: 1,
                items: [
                    { id: 1, slot: 'top', position: 0, item: { id: 1, name: 'T-shirt blanc', photo_path: '/storage/items/placeholder.jpg' } },
                    { id: 2, slot: 'bottom', position: 0, item: { id: 2, name: 'Jean bleu', photo_path: '/storage/items/placeholder.jpg' } },
                ],
            });
            setLoading(false);
        }, 1000);
    }, []);

    return (
        <div>
            <Head title="Accueil" />

            {user && (
                <div className="mb-8">
                    <h1 className="text-3xl font-bold text-gray-900 dark:text-white">
                        Bonjour, {user.name} 👋
                    </h1>
                    <p className="text-gray-600 dark:text-gray-400 mt-2">
                        Voici votre suggestion du jour.
                    </p>
                </div>
            )}

            <div className="max-w-4xl mx-auto">
                <div className="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                    <div className="flex items-center justify-between mb-6">
                        <h2 className="text-2xl font-semibold text-gray-900 dark:text-white">
                            Suggestion du jour
                        </h2>
                        <button
                            onClick={() => setLoading(true)}
                            className="text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300"
                        >
                            Recommencer
                        </button>
                    </div>

                    {loading ? (
                        <div className="flex items-center justify-center py-12">
                            <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600"></div>
                        </div>
                    ) : suggestion ? (
                        <div className="space-y-4">
                            <div className="flex items-center space-x-4">
                                {suggestion.items.map((item: any, index: number) => (
                                    <div key={item.id} className="flex-1">
                                        <div className="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 text-center">
                                            <img
                                                src={item.item.photo_path}
                                                alt={item.item.name}
                                                className="w-full h-48 object-cover rounded-lg mb-2"
                                            />
                                            <p className="font-medium text-gray-900 dark:text-white">
                                                {item.item.name}
                                            </p>
                                            <p className="text-sm text-gray-500 dark:text-gray-400">
                                                {item.slot} {item.position > 0 && `(Couche ${item.position})`}
                                            </p>
                                        </div>
                                    </div>
                                ))}
                            </div>
                            <div className="flex justify-end">
                                <button
                                    onClick={() => {
                                        // TODO: Enregistrer le port de cette suggestion
                                        alert('Portage enregistré (à implémenter)');
                                    }}
                                    className="bg-indigo-600 text-white px-6 py-2 rounded-lg hover:bg-indigo-700 transition-colors"
                                >
                                    Porter cette tenue aujourd'hui
                                </button>
                            </div>
                        </div>
                    ) : (
                        <p className="text-center text-gray-500 dark:text-gray-400 py-8">
                            Aucune suggestion disponible pour le moment.
                        </p>
                    )}
                </div>
            </div>
        </div>
    );
}
