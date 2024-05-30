<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Candidate Details') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="container mx-auto p-4">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Name</label>
                        <p class="mt-1 p-2 w-full border border-gray-300 rounded-md">{{ $candidate->name }}</p>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Image</label>
                        {{--@if ($candidate->hasMedia('images'))
                            <img src="{{ $candidate->getFirstMediaUrl('images') }}" alt="{{ $candidate->name }}" class="mt-1 w-32 h-32 object-cover border border-gray-300 rounded-md">
                        @else
                            <p class="mt-1 p-2 w-full border border-gray-300 rounded-md">No image</p>
                        @endif--}}
                    </div>
                    <a href="{{ route('candidates.edit', $candidate->id) }}" class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded">Edit</a>
                    <form action="{{ route('candidates.destroy', $candidate->id) }}" method="POST" style="display:inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
