<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-auto shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    {{-- Filter form --}}
                    <form action="{{ route('exchange_rate.index') }}" method="GET" class="flex-grow mb-5">
                        @csrf

                        <div class="mb-4 flex flex-col md:flex-row items-center">
                            <div class="mb-4 md:mb-0 md:mr-4 w-full md:w-auto">
                                <label for="start_date" class="cursor-pointer block text-sm font-medium text-gray-700">{{ __('Start Date') }}</label>
                                <input class="cursor-pointer mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                       type="date"
                                       id="start_date"
                                       name="start_date"
                                       min="{{ $minDate }}"
                                       max="{{ $maxDate }}"
                                       value="{{ old('start_date', $start_date) }}"
                                >
                            </div>
                            <div class="w-full md:w-auto">
                                <label for="end_date" class="cursor-pointer block text-sm font-medium text-gray-700">{{ __('End Date (max 7 days)') }}</label>
                                <input class="cursor-pointer mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
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

                        <div class="mb-4 grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-2">
                            @foreach ($currencies as $currency)
                                <div class="flex items-center">
                                    <input class="h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500 cursor-pointer"
                                           type="checkbox"
                                           id="currency_{{$loop->index}}"
                                           name="currencies[]"
                                           value="{{$currency}}"
                                        {{ in_array($currency, $selectedCurrencies) ? 'checked' : '' }}
                                    >
                                    <label for="currency_{{$loop->index}}" class="ml-2 text-sm text-gray-600 cursor-pointer">{{ $currency }}</label>
                                </div>
                            @endforeach
                        </div>

                        <button type="submit" class="cursor-pointer mt-3 w-full md:w-auto bg-indigo-600 text-white font-bold py-2 px-4 rounded hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            {{ __('Filter') }}
                        </button>
                    </form>

                    {{-- Displayed results --}}
                    <div class="overflow-x-auto">
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
    </div>
</x-app-layout>

