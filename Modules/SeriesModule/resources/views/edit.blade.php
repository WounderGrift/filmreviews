@extends('layouts.main')
@section('content')

    <div id="loading-model" class="info-block" style="display: none;">
        <div class="info_title"><b>Данные были загружены из хранилища ({{ base64_encode($series->id) }})</b></div>
        <div class="news_content">Нажмите Ctrl+R, чтобы обнулить анкету</div>
        <button id="clear-model" class="btn btn-orange">
            Или нажмите тут
        </button>
    </div>

    @if (!isset($series) || (Auth::check() && !Auth::user()->checkOwner()))
        <div class="info-block">
            <div class="info_title"><b>Извините! Обнаружена ошибка</b></div>
            <div class="news_content">По данному адресу публикации на сайте не найдено</div>
        </div>
    @else
    <div class="blog">
        <div class="container" data-code="{{ base64_encode($series->id) }}">
            <h2 class="title">РЕДАКТИРОВАНИЕ СЕРИИ - {{ $series->name }}
                <a href="{{ route('series.list') }}">
                    <i class="fas fa-arrow-left"></i>
                </a>
            </h2>

            <div class="blog-left">
                <div class="blog-info">
                    <div class="blog-info-text">
                        <div class="col-12 order-2">
                            <div class="poster-box summary-block"
                                 style="background: rgba(251, 251, 251, 0.9); display: block !important;">
                                <div>
                                    <div class="header-avatar header-preview">
                                        <img id="avatar"
                                             data-target="series"
                                             src="{{ isset($series->preview) && Storage::disk('public')->exists($series->preview) ? Storage::url($series->preview) : asset('images/694.png') }}?timestamp={{ $series->updated_at->timestamp }}"
                                             class="img-responsive"
                                             alt="preview"/>
                                    </div>
                                    <label for="detailPreviewInput" class="footer-avatar">
                                        <svg fill="#000000" viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg">
                                            <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                            <g id="SVGRepo_tracerCarrier" stroke-linecap="round"
                                               stroke-linejoin="round"></g>
                                            <g id="SVGRepo_iconCarrier">
                                                <path d="M15.331 6H8.5v20h15V14.154h-8.169z"></path>
                                                <path d="M18.153 6h-.009v5.342H23.5v-.002z"></path>
                                            </g>
                                        </svg>
                                        <p id="avatar-name">{{ $series->preview ?? 'Обложка не выбрана' }}</p>
                                        <svg id="avatar-remove" viewBox="0 0 24 24" fill="none"
                                             xmlns="http://www.w3.org/2000/svg">
                                            <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                            <g id="SVGRepo_tracerCarrier" stroke-linecap="round"
                                               stroke-linejoin="round"></g>
                                            <g id="SVGRepo_iconCarrier">
                                                <path
                                                    d="M5.16565 10.1534C5.07629 8.99181 5.99473 8 7.15975 8H16.8402C18.0053 8 18.9237 8.9918 18.8344 10.1534L18.142 19.1534C18.0619 20.1954 17.193 21 16.1479 21H7.85206C6.80699 21 5.93811 20.1954 5.85795 19.1534L5.16565 10.1534Z"
                                                    stroke="#000000" stroke-width="2"></path>
                                                <path d="M19.5 5H4.5" stroke="#000000" stroke-width="2"
                                                      stroke-linecap="round"></path>
                                                <path
                                                    d="M10 3C10 2.44772 10.4477 2 11 2H13C13.5523 2 14 2.44772 14 3V5H10V3Z"
                                                    stroke="#000000" stroke-width="2"></path>
                                            </g>
                                        </svg>
                                    </label>
                                    <input id="detailPreviewInput" type="file" name="avatar" accept="{{ $mimeTypeImage }}">
                                </div>

                                <div id="grid-item-edit" class="summary">
                                    <ul class="requirement-list">
                                        <li>
                                            <input id="series-name" type="text" class="detail-summary-input"
                                                   style="width: 100%; margin-bottom: 10px;" placeholder="Название серии"
                                                   value="{{ $series->name }}">
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            @foreach(\App\Http\Helpers\DetailHelper::getFilmPreviews($series->preview) as $files)
                                <label class="checkbox-container preview-grid-files"
                                       data-uri="{{ $series->preview }}"
                                       data-code="{{ base64_encode($series->id) }}">
                                    {{ $files }}
                                    <i class="fas fa-times fa-lg remove summary" style="margin-left: 5px;"></i>
                                </label>
                            @endforeach
                        </div>

                    </div>
                    <div class="clearfix"></div>
                </div>
            </div>

            <textarea id="edit-description"></textarea>

            <h4>Описание серии</h4>
            <div class="text-show">
                {!! $series->description !!}
            </div>

            <div class="error error_series">
                <h3></h3>
            </div>

            <div style="display: flex; justify-content: flex-end; margin-top: 10px;">
                <button id="save-detail" class="btn btn-orange" data-code="{{ base64_encode($series->id) }}">
                    Сохранить
                </button>
            </div>

        </div>
    </div>
    @endif
    <link rel="stylesheet" href="../../../../node_modules/summernote/dist/summernote-bs4.min.css">
    <script src="../../../../node_modules/summernote/dist/summernote.min.js"></script>
    <script type="module" src="{{ asset('Modules/SeriesModule/resources/assets/js/edit.js') }}?version={{ config('app.version') }}"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.5.7/jquery.fancybox.min.css">
@endsection
