@extends('main::layouts.main')
@section('content')

    @if (!isset($series))
        <div class="blog">
            <h2 class="title">Ошибка адреса</h2>
            <div class="info-block">
                <div class="info_title"><b>Извините! Обнаружена ошибка</b></div>
                <div class="news_content">По данному адресу публикации на сайте не найдено</div>
            </div>
        </div>
    @else
        @if($films->currentPage() == 1)
        <div class="blog">
            <div class="container">
                <h2 class="title">СЕРИАЛ - {{ $series->name }}</h2>

                <div class="blog-left">
                    <div class="blog-info">
                        <div class="blog-info-text">
                            <div class="col-12 order-2">
                                <div class="poster-box" style="background: rgba(251, 251, 251, 0.9) url({{ isset($series->preview) && Storage::disk('public')->exists($series->preview) ? Storage::url($series->preview) : asset('images/730.png') }}) center center; background-size: cover;">
                                    @if (isset($series->preview) && Storage::disk('public')->exists($series->preview))
                                        <div>
                                            <img class="poster-films"
                                                 src="{{ Storage::url($series->preview) }}?timestamp={{ $series->updated_at->timestamp }}"
                                                 height="350" alt="{{ $series->name ?? 'preview' }}">
                                        </div>
                                    @endif
                                </div>
                            </div>

                        </div>
                        <div class="clearfix"></div>
                    </div>
                </div>

                <h4>Описание сериала</h4>
                <div class="text-show">
                    {!! $series->description !!}
                </div>

                @php
                    $detailBanner = \App\Http\Helpers\BannerHelper::getDetailBannerMenu(true);
                @endphp

                @if ($detailBanner->isNotEmpty())
                    <h4> {{ $detailBanner->count() > 1 ? 'Спонсоры' : 'Спонсор'  }}</h4>
                    <x-bannersmodule.banner-detail :banners="$detailBanner"></x-bannersmodule.banner-detail>
                @endif
            </div>
        </div>
        @endif

        <div class="review">
            <div class="container">
                @if (isset($films) && ($films->isNotEmpty() || $films->total() || $films->currentPage() < $films->lastPage()))
                    <h2 class="title">{{ $title }}</h2>

                    @foreach ($films as $key => $film)
                        @if ($key % 4 == 0)
                            <div class="row align-items-start">
                        @endif

                        <div class="col-md-4 sed-md">
                            <div class="col-1" style="border-radius: 10px;">
                                <a href="{{ route('detail.index.uri', ['uri' => $film->uri]) }}">
                                    <img class="img-responsive" src="{{ Storage::disk('public')->exists($film->preview_grid) ? Storage::url($film->preview_grid) : asset('images/440.png') }}?timestamp={{ $film->updated_at->timestamp }}" alt="{{ $film->name }}">
                                    @if ($film->is_sponsor)
                                        <span class="vers hit"><i class="fas fa-sync-alt"></i>СПОНСОР</span>
                                    @elseif ($film->is_waiting)
                                        <span class="vers hit"><i class="fas fa-hourglass"></i>ЕЩЕ НЕ ВЫШЛА</span>
                                    @elseif ($film->files->isNotEmpty() && $film->files->max('version') != 'v0.0')
                                        <span class="vers"><i class="fas fa-sync-alt"></i>{{ $film->files->max('version') }}</span>
                                    @endif
                                </a>
                                <a href="{{ route('detail.index.uri', ['uri' => $film->uri]) }}" class="film-name">
                                    <h4>{{ $film->name }}</h4>
                                </a>
                                @if (Auth::check())
                                    @if (Auth::user()->is_verify)
                                        <label class="heart-checkbox wishlist-action">
                                            <input type="checkbox" class="wishlist-checkbox"
                                                   data-film-id="{{ base64_encode($film->id) }}" {{ Auth::user()->wishlist->contains('id', $film->id) ? 'checked' : '' }}>
                                            <span class="heart-icon"><i class="far fa-heart"></i></span>
                                            <span class="heart-icon-filled"><i class="fas fa-heart"></i></span>
                                        </label>
                                    @endif
                                @else
                                    <label class="heart-checkbox button-enter">
                                        <input type="checkbox" class="wishlist-checkbox">
                                        <span class="heart-icon-stub"><i class="far fa-heart"></i></span>
                                    </label>
                                @endif
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

        @if(isset($films) && $films->isNotEmpty())
            <div class="pagination">
                {{ $films->onEachSide(1)->links('pagination::bootstrap-4') }}
            </div>
        @endif
    @endif
@endsection
