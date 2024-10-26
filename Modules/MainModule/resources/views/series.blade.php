@extends('main::layouts.main')
@section('content')
    <div class="review">
        <div class="container">
            <h2 class="title">{{ $title }}</h2>
            <input type="checkbox" id="nav-toggle" hidden>

            @if (isset($series) || $series->isNotEmpty())
                @foreach ($series as $key => $serie)
                    @if ($key % 4 == 0)
                        <div class="row align-items-start">
                    @endif

                    <div class="col-md-10">
                        <div class="col-1 series" style="border-radius: 10px;">
                            <a href="{{ route('series.indexSeries', ['uri' => $serie->uri]) }}" style="display: flex; align-items: center; flex-direction: column; text-shadow: 2px 2px 2px #000000;">
                                <img class="img-responsive series-img"
                                     src="{{ Storage::disk('public')->exists($serie->preview) ? Storage::url($serie->preview) : asset('images/440.png') }}?timestamp={{ $serie->updated_at->timestamp }}"
                                     alt="{{ $serie->series }}">
                                <span style="color: #fff; padding: 10px;">{!! App\Http\Helpers\TextHelper::fiveSentences(strip_tags($serie->description), 3) !!}</span>
                            </a>
                            <a href="{{ route('series.indexSeries', ['uri' => $serie->uri]) }}" class="film-name">
                                <h4>{{ $serie->name }}</h4>
                            </a>
                        </div>
                    </div>

                    @if (($key + 1) % 4 == 0 || $loop->last)
                        <div class="clearfix"></div>
                        </div>
                    @endif
                @endforeach
            @endif
        </div>
    </div>

    @if (!isset($series) || $series->isEmpty() || !$series->total() || $series->currentPage() > $series->lastPage())
        @if (isset($isSeriesSearch))
            <div class="info-block">
                <div class="info_title"><b>Такой серии на сайте не найдено</b></div>
            </div>
        @else
            <div class="info-block">
                <div class="info_title"><b>Добро пожаловать на мой сайт</b></div>
                <div class="news_content">Извините, но серий пока не найдено</div>
            </div>
        @endif
    @endif

    @if(isset($series) || $series->isNotEmpty())
        <div class="pagination">
            {{ $series->onEachSide(1)->links('pagination::bootstrap-4') }}
        </div>
    @endif

    @if (isset($jsFile))
        <script src="{{ asset($jsFile) }}?version={{config('app.version')}}"></script>
    @endif
@endsection
