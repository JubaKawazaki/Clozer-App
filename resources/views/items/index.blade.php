@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-8">
        Dressing
    </h1>

    <div class="mb-6 flex items-center justify-between">
        <p class="text-gray-600 dark:text-gray-400">
            Cette page affichera la liste des pièces de vêtements.
        </p>
        <a href="{{ route('items.create') }}" class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
            Ajouter une pièce
        </a>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-8">
        <p class="text-gray-500 dark:text-gray-400 text-center py-12">
            Aucune pièce trouvée.
        </p>
    </div>
</div>
@endsection
