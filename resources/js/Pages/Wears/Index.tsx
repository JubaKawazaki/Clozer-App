import { Head } from '@inertiajs/react';
import { usePage } from '@inertiajs/react';

interface Wear {
    id: number;
    worn_on: string;
    outfit_id: number | null;
    items: Array<{
        id: number;
        slot: string | null;
        item: {
            id: number;
            name: string | null;
            photo_path: string;
        };
    }>;
    outfit?: {
        id: number;
        name: string | null;
    };
}

interface WearsPageProps {
    wears: Wear[];
}

export default function Wears() {
    const { wears } = usePage<WearsPageProps>().props;

    return (
        <div>
            <Head title="Wear Log" />

            <h1 className="text-3xl font-bold text-gray-900 dark:text-white mb-8">
                Wear Log
            </h1>

            <div className="space-y-6">
                {wears.map((wear) => (
                    <div key={wear.id} className="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                        <div className="flex items-center justify-between mb-4">
                            <div className="flex items-center space-x-4">
                                <div className="bg-indigo-100 dark:bg-indigo-900 text-indigo-800 dark:text-indigo-200 px-4 py-2 rounded-lg">
                                    <span className="font-semibold">
                                        {new Date(wear.worn_on).toLocaleDateString('fr-FR', {
                                            weekday: 'long',
                                            year: 'numeric',
                                            month: 'long',
                                            day: 'numeric',
                                        })}
                                    </span>
                                </div>
                                {wear.outfit && (
                                    <div className="bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200 px-4 py-2 rounded-lg">
                                        <span className="font-medium">
                                            {wear.outfit.name || `Tenue ${wear.outfit.id}`}
                                        </span>
                                    </div>
                                )}
                            </div>
                        </div>

                        {wear.photo_path && (
                            <div className="mb-4">
                                <img
                                    src={wear.photo_path}
                                    alt={`Porté le ${wear.worn_on}`}
                                    className="w-full h-48 object-cover rounded-lg"
                                />
                            </div>
                        )}

                        {wear.items.length > 0 && (
                            <div className="space-y-2">
                                <h3 className="text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Pièces portées
                                </h3>
                                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                                    {wear.items.map((item) => (
                                        <div key={item.id} className="flex items-center space-x-3 bg-gray-50 dark:bg-gray-700 p-3 rounded-lg">
                                            <img
                                                src={item.item.photo_path}
                                                alt={item.item.name || `Pièce ${item.item.id}`}
                                                className="w-12 h-12 object-cover rounded"
                                            />
                                            <div className="flex-1">
                                                <p className="font-medium text-gray-900 dark:text-white">
                                                    {item.item.name || `Pièce ${item.item.id}`}
                                                </p>
                                                {item.slot && (
                                                    <p className="text-sm text-gray-500 dark:text-gray-400">
                                                        {item.slot}
                                                    </p>
                                                )}
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            </div>
                        )}
                    </div>
                ))}
            </div>

            {wears.length === 0 && (
                <div className="text-center py-12">
                    <p className="text-gray-500 dark:text-gray-400">
                        Aucun port enregistré pour le moment.
                    </p>
                </div>
            )}
        </div>
    );
}
