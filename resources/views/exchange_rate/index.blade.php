<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-auto shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    {{-- Filter form --}}
                    <form action="{{ route('exchange_rate.index') }}" method="GET" class="flex-grow mb-5">
                        @csrf

                        <div class="mb-4 flex items-center">
                            <div class="mr-4">
                                <label for="start_date" class="block text-sm font-medium text-gray-700">{{ __('Start Date') }}</label>
                                <input class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                       type="date"
                                       id="start_date"
                                       name="start_date"
                                       min="{{ $minDate }}"
                                       max="{{ $maxDate }}"
                                       value="{{ old('start_date', $start_date) }}"
                                >
                            </div>
                            <div class="mr-4">
                                <label for="end_date" class="block text-sm font-medium text-gray-700">{{ __('End Date (max 7 days)') }}</label>
                                <input class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                       type="date"
                                       id="end_date"
                                       name="end_date"
                                       min="{{ $minDate }}"
                                       max="{{ $maxDate }}"
                                       value="{{ old('end_date', $end_date) }}"
                                >
                            </div>
                        </div>

                        @error('end_date')
                            <div class="text-red-500 text-sm mb-3">
                                {{ $message }}
                            </div>
                        @enderror

                        <div class="mb-4 grid grid-cols-12 gap-2">
                            @foreach ($currencies as $currency)
                                <div class="flex items-center">
                                    <input class="h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500"
                                           type="checkbox"
                                           id="currency_{{$loop->index}}"
                                           name="currencies[]"
                                           value="{{$currency}}"
                                           {{ in_array($currency, $selectedCurrencies) ? 'checked' : '' }}
                                    >
                                    <label for="currency_{{$loop->index}}" class="ml-2 text-sm text-gray-600">{{ $currency }}</label>
                                </div>
                            @endforeach
                        </div>

                        <button type="submit" class="mt-3 bg-indigo-600 text-white font-bold py-2 px-4 rounded hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            {{ __('Filter') }}
                        </button>
                    </form>

                    {{-- Displayed results --}}
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
