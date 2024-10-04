@extends('layouts.main')
@section('content')
    <div class="review">
        <div class="container">
            <h2 class="title">{{ $title }}</h2>

            <x-recyclebinmodule.recyclebin-menu></x-recyclebinmodule.recyclebin-menu>

            <input type="checkbox" id="nav-toggle" hidden>

            <div class="error error_trashed_screenshots">
                <h3></h3>
            </div>

            @if (isset($games) && ($games->isNotEmpty() || $games->total() || $games->currentPage() < $games->lastPage()))
                @foreach ($games as $key => $game)
                    <div class="spoiler" data-game-id="{{ $game->id }}">
                        <div class="spoiler-header">
                            <div style="display: flex; align-items: flex-start;">
                                <h2 class="download-title">
                                    Скриншоты из игры {{ $game->name }}
                                </h2>
                                <span class="toggle-icon">▲</span>
                            </div>
                            @if ($game->status != App\Models\Game::STATUS_PUBLISHED)
                                <h2 class="download-title" style="color: black">
                                    неопубликованной игры
                                </h2>
                            @endif
                        </div>
                        <div class="spoiler-content" style="text-align: center">
                            <div class="spoiler-description">
                                <div class="grid-box grid-block">

                                    <div class="gallery removed">
                                        @foreach($game->trashedScreenshots as $screenshot)
                                            <div class="photo-container" data-id="{{ $screenshot->id }}">
                                                <a href="{{ isset($screenshot->path) && Storage::disk('public')->exists($screenshot->path) ? Storage::url($screenshot->path) : asset('images/350.png') }}"
                                                   data-fancybox="gallery" class="photo">
                                                    <img src="{{ isset($screenshot->path) && Storage::disk('public')->exists($screenshot->path) ? Storage::url($screenshot->path) : asset('images/350.png') }}"
                                                         alt="{{ $game->name }}">
                                                </a>
                                                <div style="position: absolute; top: 0; right: 0; width: 100%;">
                                                    <div style="display: flex; justify-content: space-between;">
                                                        @if (Auth::user()->checkOwner())
                                                        <div style="display: flex;">
                                                            <i class="fas fa-times fa-lg remove remove-screen-force"
                                                               style="color: red;" title="Жестко удалить"></i>
                                                        </div>
                                                        @endif
                                                        <div style="display: flex;">
                                                            <i class="fas fa-times fa-lg restore restore-screen"
                                                               title="Восстановить"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach

                                        <a href="{{ route('detail.edit.index', ['uri' => $game->uri]) }}#screenshots"
                                           target="_blank"
                                           style="display: flex; align-items: center; justify-content: space-around;"
                                           class="btn btn-orange">
                                            Перейти
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
    </div>

    @if (!isset($games) || $games->isEmpty() || !$games->total() || $games->currentPage() > $games->lastPage())
        <div class="info-block">
            <div class="info_title"><b>Корзина пуста</b></div>
        </div>
    @else
        <div style="display: flex; justify-content: flex-end; margin-right: 40px; margin-top: 40px;">
            <button id="empty-trash" class="btn btn-danger">
                Очистить корзину
            </button>
        </div>
    @endif

    @if(isset($games) && $games->isNotEmpty())
        <div class="pagination">
            {{ $games->onEachSide(1)->links('pagination::bootstrap-4') }}
        </div>
    @endif
    <script type="module" src="{{ asset('modules/recyclebinmodule/resources/assets/js/recyclebin-screenshots.js') }}?version={{ config('app.version') }}"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.5.7/jquery.fancybox.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.5.7/jquery.fancybox.min.js"></script>
@endsection
