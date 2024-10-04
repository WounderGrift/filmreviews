@extends('layouts.main')
@section('content')

    <div class="review">
        <div class="container">
            <h2 class="title">НАСТРОЙКА СЕРИЙ</h2>

            <a class="btn btn-info create-series" style="float: right; margin-bottom: 10px;" href="{{ route('series.new') }}">
                Создать
            </a>

            <form id="filter" method="GET" style="margin-top: 30px;">
                <input type="text" placeholder="Фильтр" style="margin-top: 15px; text-align: center; width: 100%; margin-bottom: 10px;">
            </form>

            <table id="table-series" class="display">
                <thead>
                <tr>
                    <th>Название</th>
                    <th>Ссылка</th>
                    <th>Описание</th>
                    <th>Статус</th>
                </tr>
                </thead>
                <tbody>
                @foreach($series as $key => $serie)
                    <tr>
                        <td data-label="Название">
                            <a href="{{ route('series.indexSeries', ['uri' => $serie->uri]) }}">
                                {{ $serie->name }}
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                        <td data-label="Ссылка">
                            @if (Auth::user()->checkOwner())
                            <a href="{{ route('series.edit', ['uri' => $serie->uri]) }}">
                                {{ $serie->uri }}
                                <i class="fas fa-pencil-alt pencil-ico"></i>
                            </a>
                            @else
                                {{ $serie->uri }}
                            @endif
                        </td>
                        <td data-label="Описание">{!! App\Http\Helpers\TextHelper::fiveSentences(strip_tags($serie->description), 3) !!}</td>
                        <td data-label="Статус">
                            {{ $serie->trashed() ? "Неактивно" : "Активно" }}
                            <i class="fas fa-times fa-lg remove remove-series" data-code="{{ base64_encode($serie->id) }}"></i>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>

            <div class="error error_series">
                <h3></h3>
            </div>

        </div>
    </div>
    <script type="module" src="{{ asset('modules/seriesmodule/resources/assets/js/list-series.js') }}?version={{ config('app.version') }}"></script>
@endsection
