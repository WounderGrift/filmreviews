@extends('layouts.main')
@section('content')
    <div class="review">
        <div class="container">
            <h2 class="title">{{ $title }}</h2>

            <x-recyclebinmodule.recyclebin-menu></x-recyclebinmodule.recyclebin-menu>

            <input type="checkbox" id="nav-toggle" hidden>

            <div class="error error_trashed_games">
                <h3></h3>
            </div>

            @if (isset($games) && ($games->isNotEmpty() || $games->total() || $games->currentPage() < $games->lastPage()))
                @foreach ($games as $key => $game)
                    @if ($key % 4 == 0)
                        <div class="row align-items-start">
                            @endif

                            <div class="col-md-4 sed-md">
                                <div class="col-1">
                                    <a href="{{ route('detail.index.uri', ['uri' => $game->uri]) }}"
                                       target="_blank">
                                        <img class="img-responsive" src="{{ Storage::url($game->preview_grid) }}?timestamp={{ $game->updated_at->timestamp }}"
                                             alt="">
                                        @if ($game->is_sponsor)
                                            <span class="vers hit"><i class="fas fa-sync-alt"></i>СПОНСОР</span>
                                        @elseif (isset($game->torrents) && $game->torrents->isNotEmpty() && $game->torrents->max('version') != 'v0.0')
                                            <span class="vers"><i class="fas fa-sync-alt"></i>{{ $game->torrents->max('version') }}</span>
                                        @endif
                                    </a>
                                    <a href="{{ route('detail.index.uri', ['uri' => $game->uri]) }}" target="_blank"
                                       class="game-name">
                                        <h4>{{ $game->name }}</h4>
                                    </a>
                                    <div style="display: flex; justify-content: space-around; margin-bottom: 10px;">
                                        @if (Auth::user()->checkOwner())
                                        <button class="btn btn-danger remove-game" data-game-id="{{ $game->id }}">
                                            Удалить
                                        </button>
                                        @endif
                                        <button class="btn btn-info reset-game" data-game-id="{{ $game->id }}">
                                            Вернуть
                                        </button>
                                    </div>
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

    @if (!isset($games) || $games->isEmpty() || !$games->total() || $games->currentPage() > $games->lastPage())
        <div class="info-block">
            <div class="info_title"><b>Корзина пуста</b></div>
        </div>
    @else
        @if (Auth::user()->checkOwner())
            <div style="display: flex; justify-content: flex-end; margin-right: 40px; margin-top: 40px;">
                <button id="empty-trash" class="btn btn-danger">
                    Очистить корзину
                </button>
            </div>
        @endif
    @endif

    @if(isset($games) && $games->isNotEmpty())
        <div class="pagination">
            {{ $games->onEachSide(1)->links('pagination::bootstrap-4') }}
        </div>
    @endif
    <script type="module" src="{{ asset('Modules/RecyclebinModule/resources/assets/js/recyclebin-games.js') }}?version={{ config('app.version') }}"></script>
@endsection
