<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Candidates') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="container mx-auto p-4">
                    <a href="{{ route('elections.positions.candidates.create', [$election->id, $position->id]) }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded mb-4 inline-block">Create Candidate</a>
                    <table class="table-auto w-full mt-4">
                        <thead>
                        <tr>
                            <th class="px-4 py-2">Name</th>
                            <th class="px-4 py-2">Image</th>
                            <th class="px-4 py-2">No. of Votes</th>
                            <th class="px-4 py-2">Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($candidates as $candidate)
                            <tr>
                                <td class="border px-4 py-2">{{ $candidate->name }}</td>
                                <td class="border px-4 py-2">
                                    {{--@if ($candidate->hasMedia('images'))
                                        <img src="{{ $candidate->getFirstMediaUrl('images') }}" alt="{{ $candidate->name }}" class="w-16 h-16 object-cover">
                                    @else
                                        No image
                                    @endif--}}
                                </td>
                                <td class="border px-4 py-2">{{ number_format ($candidate->votes->count ()) }}</td>
                                <td class="border px-4 py-2">
                                    <a href="{{ route('candidates.show', $candidate->id) }}" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">View</a>
                                    <a href="{{ route('candidates.edit', $candidate->id) }}" class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded">Edit</a>
                                    <form action="{{ route('candidates.destroy', $candidate->id) }}" method="POST" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>