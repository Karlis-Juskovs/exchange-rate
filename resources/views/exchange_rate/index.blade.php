<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-auto shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                        {{-- todo filters --}}
                    </div>

                    <table class="min-w-full bg-white">
                        <thead>
                        <tr>
                            <th class="px-4 py-2"></th>
                            @foreach($dateArray as $date)
                                <th class="px-4 py-2 text-center">{{ $date }}</th>
                            @endforeach
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($exchangeRates as $currency => $rates)
                            <tr>
                                <td class="border px-4 py-2 font-bold text-center">{{ $currency }}</td>
                                @php
                                    $rateCounter = 0;
                                @endphp
                                @foreach($dateArray as $date)
                                    <td class="border px-4 py-2 text-right">
                                        @if($rates[$rateCounter]->created_at === $date)
                                            {{ $rates[$rateCounter]->rate }}
                                            @php
                                                $rateCounter++;
                                            @endphp
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
