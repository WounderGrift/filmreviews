@extends('layouts.main')
@section('content')
    <div class="review">
        <div class="container">
            <h2 class="title">{{ $title }}</h2>

            <x-recyclebinmodule.recyclebin-menu></x-recyclebinmodule.recyclebin-menu>

            <input type="checkbox" id="nav-toggle" hidden>

            <div class="error error_trashed_files">
                <h3></h3>
            </div>

            @if (isset($games) && ($games->isNotEmpty() || $games->total() || $games->currentPage() < $games->lastPage()))
                @foreach ($games as $key => $game)
                    <div class="spoiler" data-game-id="{{ $game->id }}">
                        <div class="spoiler-header">
                            <div style="display: flex; align-items: flex-start;">
                                <h2 class="download-title">
                                    Файлы игры {{ $game->name }}
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
                                @foreach($game->trashedTorrents as $torrent)
                                    <div class="file-container" style="margin-top: 10px" data-id="{{ $torrent->id }}">
                                        <h2 class="download-title">
                                            {{ $torrent?->repacks?->label ? 'Репак от ' . $torrent->repacks->label . ' | ' : ''}}
                                            {{ $torrent?->size ? ' Размер: ' . $torrent->size . ' | ': '' }}
                                            {{ $torrent->version }}
                                        </h2>

                                        <div style="display: flex; justify-content: center;">
                                            @if ($torrent->is_link)
                                                <a data-code="{{ base64_encode($torrent->id) }}" class="btn btn-success download" style="background-image: url({{ asset('images/download-button-tor-bg.png') }});">
                                                    Перейти на сайт
                                                </a>
                                            @elseif (pathinfo($torrent->name, PATHINFO_EXTENSION) == \App\Models\Torrents::getExtendedFile()[0])
                                                <a data-code="{{ base64_encode($torrent->id) }}" class="btn btn-success download" style="background-image: url({{ asset('images/download-button-tor-bg.png') }});">
                                                    Скачать .{{ pathinfo($torrent->name, PATHINFO_EXTENSION) }}
                                                </a>
                                            @elseif (pathinfo($torrent->name, PATHINFO_EXTENSION) == \App\Models\Torrents::getExtendedFile()[1])
                                                <a data-code="{{ base64_encode($torrent->id) }}" class="btn btn-success download-rar" style="background-image: url({{ asset('images/download-button-rar-bg.png') }});">
                                                    Скачать .{{ pathinfo($torrent->name, PATHINFO_EXTENSION) }}
                                                </a>
                                            @endif

                                            <div class="download-count" style="display: flex; justify-content: space-between; width: 70px;">
                                                @if (Auth::user()->checkOwner())
                                                <i class="fas fa-times fa-lg remove remove-file-force"
                                                   style="margin-left: 5px;" title="Жестко удалить"></i>
                                                @endif
                                                <i class="fas fa-arrow-up restore restore-file"
                                                   style="margin-left: 5px; cursor: pointer;" title="Восстановить"></i>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach

                                <a href="{{ route('detail.edit.index', ['uri' => $game->uri]) }}#screenshots"
                                   target="_blank"
                                   style="display: flex; align-items: center; justify-content: space-around; margin-top: 10px;"
                                   class="btn btn-orange">
                                    Перейти
                                </a>
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
    <script type="module" src="{{ asset('modules/recyclebinmodule/resources/assets/js/recyclebin-files.js') }}?version={{ config('app.version') }}"></script>
@endsection
