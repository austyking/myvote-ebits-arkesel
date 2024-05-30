<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="container mx-auto p-4">
                        <h1 class="text-2xl font-bold mb-4">Dashboard</h1>

                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
                            <div class="bg-white shadow-md rounded p-4">
                                <h2 class="text-xl font-bold">Active Elections</h2>
                                <p class="text-3xl">{{ $activeElectionsCount }}</p>
                            </div>
                            <div class="bg-white shadow-md rounded p-4">
                                <h2 class="text-xl font-bold">Total Candidates</h2>
                                <p class="text-3xl">{{ $totalCandidates }}</p>
                            </div>
                            <div class="bg-white shadow-md rounded p-4">
                                <h2 class="text-xl font-bold">Total Positions</h2>
                                <p class="text-3xl">{{ $totalPositions }}</p>
                            </div>
                            <div class="bg-white shadow-md rounded p-4">
                                <h2 class="text-xl font-bold">Total Voters</h2>
                                <p class="text-3xl">{{ $totalVoters }}</p>
                            </div>
                        </div>

                        <div class="mb-8">
                            <form action="{{ route('dashboard') }}" method="GET" class="flex items-center space-x-4">
                                <label for="election_id" class="block text-sm font-medium text-gray-700">Filter by Election</label>
                                <select name="election_id" id="election_id" class="form-select mt-1 block w-full">
                                    <option value="">All Elections</option>
                                    @foreach($elections as $election)
                                        <option value="{{ $election->id }}" {{ request('election_id') == $election->id ? 'selected' : '' }}>
                                            {{ $election->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Filter</button>
                            </form>
                        </div>

                        <div class="mb-8">
                            <label for="position_id" class="block text-sm font-medium text-gray-700">Filter by Position</label>
                            <select name="position_id" id="position_id" class="form-select mt-1 block w-full">
                                <option value="">All Positions</option>
                                @foreach($positions as $position)
                                    <option value="{{ $position->id }}">{{ $position->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="bg-white shadow-md rounded p-4">
                            <h2 class="text-xl font-bold mb-4">Votes per Candidate per Position</h2>
                            <div id="votesPerPositionChart"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @push('scripts')
        <script src="https://code.highcharts.com/highcharts.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const positionDropdown = document.getElementById('position_id');
                const electionDropdown = document.getElementById('election_id');

                positionDropdown.addEventListener('change', loadChartData);
                electionDropdown.addEventListener('change', loadChartData);

                function loadChartData() {
                    const positionId = positionDropdown.value;
                    const electionId = electionDropdown.value;

                    fetch(`{{ route('dashboard.votingData') }}?position_id=${positionId}&election_id=${electionId}`)
                    .then(response => response.json())
                    .then(data => {
                        renderChart(data);
                    });
                }

                function renderChart(votingData) {
                    Highcharts.chart('votesPerPositionChart', {
                        chart: {
                            type: 'column'
                        },
                        title: {
                            text: 'Votes per Candidate per Position'
                        },
                        xAxis: {
                            categories: Object.keys(votingData.overallVotes)
                        },
                        yAxis: {
                            title: {
                                text: 'Votes'
                            }
                        },
                        series: [{
                            name: 'Votes',
                            data: Object.values(votingData.overallVotes)
                        }]
                    });
                }

                // Load initial chart data
                loadChartData();
            });
        </script>
    @endpush
</x-app-layout>
