@extends('layouts.main')
@section('content')

    @if (isset($filmOriginal) && $filmOriginal)
        <div class="info-block">
            <div class="info_title"><b>Обнаружен фильм, с похожим названием</b></div>
            <div class="news_content">Клик по кнопке откроет редактирование оригинала в новом окне</div>
            <a href="{{ route('detail.edit.index', ['uri' => $filmOriginal->uri]) }}" target="_blank"
               class="btn btn-orange">
                Открыть оригинал
            </a>
        </div>
    @endif

    @if (!isset($film) || (Auth::check() && !Auth::user()->checkOwnerOrAdmin()))
        <div class="blog">
            <h2 class="title">Ошибка адреса</h2>
            <div class="info-block">
                <div class="info_title"><b>Извините! Обнаружена ошибка</b></div>
                <div class="news_content">По данному адресу публикации на сайте не найдено</div>
            </div>
        </div>
    @else
        <div id="loading-model" class="info-block" style="display: none;">
            <div class="info_title"><b>Данные были загружены из хранилища ({{ base64_encode($film->id) }})</b></div>
            <div class="news_content">Нажмите Ctrl+R, чтобы обнулить анкету</div>
            <button id="clear-model" class="btn btn-orange">
                Или нажмите тут
            </button>
        </div>

        <div class="blog">
            <div class="container" data-film-id="{{ base64_encode($film->id) }}">
                <h2 class="title">Редактировать
                    @if ($film->status === $film::STATUS_UNPUBLISHED && !$film->trashed())
                        (Неопубликованная)
                    @endif
                    {{ $film->name }}
                    <a href="{{ route('detail.index.uri', ['uri' => $film->uri]) }}">
                        <i class="fas fa-arrow-left"></i></a>
                </h2>

                <div class="download-container grid-preview">
                    <div class="spoiler">
                        <div class="spoiler-header" style="cursor: pointer;">
                            <div style="display: flex; align-items: flex-start;">
                                <h2 class="download-title">
                                    Отображение в сетке
                                </h2>
                                <span class="toggle-icon">▲</span>
                            </div>
                        </div>
                        <div class="spoiler-content" style="text-align: center">
                            <div class="spoiler-description">
                                <div class="grid-box grid-block"
                                     style="background: rgba(251, 251, 251, 0.9) url({{Storage::disk('public')->exists($film->preview_grid) ? Storage::url($film->preview_grid) : asset('images/440.png')}}) center center; background-size: cover;">
                                    <div class="make-text-smaller">
                                        <div class="header-avatar header-preview" style="border: none !important;">
                                            <img id="avatar" data-target="preview"
                                                 src="{{ Storage::disk('public')->exists($film->preview_grid) ? Storage::url($film->preview_grid) : asset('images/440.png') }}?timestamp={{ $film->updated_at->timestamp }}"
                                                 class="img-responsive" alt="{{ $film->name ?? 'preview' }}"
                                                 style="width: 275px; height: 310px; border: 2px dashed var(--pink);"/>
                                        </div>
                                        <label for="gridPreviewInput" class="footer-avatar">
                                            <svg fill="#000000" viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg">
                                                <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                                <g id="SVGRepo_tracerCarrier" stroke-linecap="round"
                                                   stroke-linejoin="round"></g>
                                                <g id="SVGRepo_iconCarrier">
                                                    <path d="M15.331 6H8.5v20h15V14.154h-8.169z"></path>
                                                    <path d="M18.153 6h-.009v5.342H23.5v-.002z"></path>
                                                </g>
                                            </svg>
                                            <p id="avatar-name" class="make-text-smaller"
                                               style="line-height: 1.7em; font-size: 0.9em; color: #777;">
                                                {{ $film->preview_grid ?? 'Обложка не выбрана' }}
                                            </p>
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
                                        <input id="gridPreviewInput" type="file" name="avatar"
                                               accept="{{ $mimeTypeImage }}">
                                    </div>
                                    <div id="grid-item-edit" class="summary">
                                        <ul class="requirement-list">
                                            <li>
                                                <input id="film-name" type="text" class="detail-summary-input"
                                                       style="width: 100%; margin-bottom: 10px;"
                                                       value="{{ $film->name }}">
                                            </li>
                                            <li>
                                                <label class="checkbox-container grid-checkbox" for="is_sponsor">
                                                    Спонсорство
                                                    <input type="checkbox" id="is_sponsor"
                                                           value="1" {{ $film->is_sponsor ? 'checked' : '' }}>
                                                    <span class="checkmark"></span>
                                                </label>
                                            </li>
                                            <li>
                                                <label class="checkbox-container grid-checkbox" for="is_weak">
                                                    Для слабых ПК
                                                    <input type="checkbox" id="is_weak"
                                                           value="1" {{ $film->is_weak_pc ? 'checked' : '' }}>
                                                    <span class="checkmark"></span>
                                                </label>
                                            </li>
                                            <li>
                                                <label class="checkbox-container grid-checkbox" for="is_waiting">
                                                    Еще не вышла
                                                    <input type="checkbox" id="is_waiting"
                                                           value="1" {{ $film->is_waiting ? 'checked' : '' }}>
                                                    <span class="checkmark"></span>
                                                </label>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                                @foreach(\App\Http\Helpers\DetailHelper::getFilmPreviews($film->preview_grid) as $files)
                                    <label class="checkbox-container preview-grid-files"
                                           data-uri="{{ $film->preview_grid }}"
                                           style="{{ basename($film->preview_grid) !== $files ?: 'color: var(--green)' }}">
                                        {{ $files }}
                                        <i class="fas fa-times fa-lg remove summary" style="margin-left: 5px;"></i>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <div class="blog-left">
                    <div class="blog-info">
                        <div class="blog-info-text">
                            <div class="col-12 order-2">
                                <div class="poster-box summary-block"
                                     style="background: rgba(251, 251, 251, 0.9) url({{isset($detail->preview_detail) && Storage::disk('public')->exists($detail->preview_detail) ? Storage::url($detail->preview_detail) : asset('images/730.png')}}) center center; background-size: cover;">
                                    <div class="poster-side" style="width: 60%">
                                        <div class="header-avatar header-preview">
                                            <img id="avatar" data-target="detail"
                                                 src="{{ isset($detail->preview_detail) && Storage::disk('public')->exists($detail->preview_detail) ? Storage::url($detail->preview_detail) : asset('images/730.png') }}?timestamp={{ $detail?->updated_at?->timestamp }}"
                                                 class="img-responsive"
                                                 alt={{ $film->name ?? 'preview' }}/>
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
                                            <p id="avatar-name"
                                               class="make-text-smaller">{{ $detail->preview_detail ?? 'Обложка не выбрана' }}</p>
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
                                        <input id="detailPreviewInput" type="file" name="avatar"
                                               accept="{{ $mimeTypeImage }}">
                                        @foreach(\App\Http\Helpers\DetailHelper::getFilmPreviews($detail->preview_detail) as $files)
                                            <label class="checkbox-container preview-detail-files"
                                                   data-uri="{{ $detail->preview_detail }}"
                                                   style="{{ basename($detail->preview_detail) !== $files ?: 'color: var(--green)' }}">
                                                {{ $files }}
                                                <i class="fas fa-times fa-lg remove summary"
                                                   style="margin-left: 5px;"></i>
                                            </label>
                                        @endforeach

                                        <button id="add-summary" class="btn btn-orange"
                                                style="margin-top: 10px; float: right;">
                                            Добавить поле
                                        </button>
                                    </div>
                                    <div id="media-edit" class="summary">
                                        <ul class="requirement-list">
                                            <span class="requirement-label">Дата выпуска:</span>
                                            <li>
                                                <input type="text" id="datepicker" name="datepicker_name"
                                                       style="width: 100%; text-align: center; margin-bottom: 10px;"
                                                       value="{{ $film->date_release }}">
                                            </li>
                                            ИЛИ
                                            <li>
                                                <input id="datepicker_text" type="text" class="detail-summary-input"
                                                       style="width: 100%; margin-bottom: 10px; text-align: center;"
                                                       value="{{ $film->date_release }}">
                                            </li>
                                            <span class="requirement-label">Серия:</span>
                                            <li class="requirement-edit">
                                                <div class="custom-dropdown series-dropdown" style="width: 100%;">
                                                    <div id="series-list" data-default-value="null">
                                                        <span id="series-answer" class="placeholder series-select">
                                                            <input type="text" id="searchSeries"
                                                                   class="selected-options"
                                                                   style="width: 100%; text-align: center;"
                                                                   value="{{ $film->series->name ?? 'null' }}">
                                                        </span>
                                                    </div>
                                                    <div class="options" style="z-index: 100;">
                                                        <div
                                                            class="option {{ !isset($film->series->name) ? 'selected' : ''}}"
                                                            data-value="null">null
                                                        </div>
                                                        @foreach (\App\Models\Series::withTrashed()->orderBy('created_at', 'DESC')->get() as $series)
                                                            <div
                                                                class="option {{ isset($film->series->name) && $series->name === $film->series->name ? 'selected' : ''}}"
                                                                data-value="{{ $series->id }}">{{ $series->name }}</div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </li>
                                            <span class="requirement-label">Категории:</span>
                                            <li class="requirement-edit">
                                                <div class="custom-dropdown category-dropdown" style="width: 100%;">
                                                    <div id="categories-list" class="selected-options"
                                                         data-default-value="{{ implode(', ', $detail->categories->pluck('label')->all()) }}">
                                                        <span class="placeholder">
                                                            {{ implode(', ', $detail->categories->pluck('label')->all()) }}
                                                        </span>
                                                    </div>
                                                    <div class="options">
                                                        @foreach (\App\Models\Categories::query()->get() as $category)
                                                            <div
                                                                class="option {{ in_array($category->label, $detail->categories->pluck('label')->all()) ? 'selected' : ''}}"
                                                                data-value="{{ $category->id }}">{{ $category->label }}</div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </li>
                                            <div id="summary">
                                                @foreach ($info->summary as $key => $value)
                                                    <li class="requirement-edit summary-fields">
                                                        <input id="summary-key" type="text"
                                                               class="detail-summary-input summary-key"
                                                               value="{{ $key }}">
                                                        <input id="summary-val" type="text"
                                                               class="detail-summary-input summary-val"
                                                               value="{{ $value }}">
                                                        <i class="fas fa-times fa-lg remove summary"></i>
                                                    </li>
                                                @endforeach
                                            </div>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <textarea id="edit-description"></textarea>

                            <h4>Описание фильма</h4>
                            <div class="text-show">
                                {!! $info->description !!}
                            </div>

                            @php
                                $minSysCount = isset($info?->system?->min) ? count((array) $info?->system?->min) : 0;
                                $maxSysCount = isset($info?->system?->max) ? count((array) $info?->system?->max) : 0;
                            @endphp

                            <h4 class="button-group-require"
                                style="justify-content: space-between !important; flex-wrap: wrap;">
                                Системные требования
                                <div class="button-group-require">
                                    <button id="remove-requirements" class="btn btn-orange">
                                        Удалить строчку
                                    </button>
                                    <button id="add-requirements" class="btn btn-orange" style="margin-left: 10px;">
                                        Добавить строчку
                                    </button>
                                </div>
                            </h4>
                            <div class="requirements-container">
                                <div class="system-requirements min-requirements">
                                    <h2 class="section-title">Минимальные</h2>
                                    <ul class="requirement-list">
                                        @if ($minSysCount > 0)
                                            @foreach($info->system->min as $key => $value)
                                                <li class="requirement-edit min-fields">
                                                    <input id="min-key" type="text" class="detail-summary-input"
                                                           value="{{ $key }}">
                                                    <input id="min-val" type="text" class="detail-summary-input"
                                                           value="{{ $value }}">
                                                </li>
                                            @endforeach
                                        @endif
                                    </ul>
                                </div>
                                <div class="system-requirements recommended-requirements">
                                    <h2 class="section-title">Рекомендуемые</h2>
                                    <ul class="requirement-list">
                                        @if ($maxSysCount > 0)
                                            @foreach($info->system->max as $key => $value)
                                                <li class="requirement-edit max-fields">
                                                    <input id="max-key" type="text" class="detail-summary-input"
                                                           value="{{ $key }}">
                                                    <input id="max-val" type="text" class="detail-summary-input"
                                                           value="{{ $value }}">
                                                </li>
                                            @endforeach
                                        @endif
                                    </ul>
                                </div>
                            </div>

                            <h4>Трейлер</h4>
                            <h3>Обложка трейлера</h3>
                            <li class="requirement-edit">
                                <input id="trailerPreviewEdit" type="text" class="detail-summary-input"
                                       style="text-align: center; width: 100%;"
                                       value="{{ isset($detail->preview_trailer) && Storage::disk('public')->exists($detail->preview_trailer) ? Storage::url($detail->preview_trailer) : asset('images/730.png')  }}">
                            </li>
                            @foreach(\App\Http\Helpers\DetailHelper::getFilmPreviews($detail->preview_trailer) as $files)
                                <label class="checkbox-container preview-trailer-files"
                                       data-uri="{{ "films/$film->uri/previewTrailer/$files" }}">
                                    {{ $files }}
                                    <i class="fas fa-times fa-lg remove summary" style="margin-left: 5px;"></i>
                                </label>
                            @endforeach

                            <h3>Трейлер</h3>
                            <li class="requirement-edit">
                                <input id="trailer_edit" type="text" class="detail-summary-input"
                                       style="text-align: center; width: 100%;"
                                       value="{{ $detail->trailer_detail }}">
                            </li>
                            <li class="requirement-edit" style="justify-content: flex-end !important;">
                                <button id="save-trailer" class="btn btn-orange"
                                        style="margin-top: 10px; float: right;">
                                    Просмотр
                                </button>
                            </li>

                            <div class="black-ground-container">
                                <div class="black-ground" id="videoContainer"
                                     data-trailer="{{ $detail->trailer_detail }}">
                                    <img
                                        src="{{ isset($detail->preview_trailer) && Storage::disk('public')->exists($detail->preview_trailer) ? Storage::url($detail->preview_trailer) : asset('images/730.png') }}"
                                        alt="{{ $film->name }}">
                                    <div class="overlay" id="playButton">
                                        <p>Кликните, чтобы начать видео</p>
                                    </div>
                                </div>
                            </div>

                            <h4 style="margin-top: 2em">Скриншоты</h4>
                            <div class="gallery exists" data-target="screenshots">
                                @foreach($detail?->screenshots as $screenshot)
                                    <div class="photo-container" data-id="{{ $screenshot->id }}">
                                        <a href="{{ isset($screenshot->path) && Storage::disk('public')->exists($screenshot->path) ? Storage::url($screenshot->path) : asset('images/350.png') }}"
                                           data-fancybox="gallery" class="photo">
                                            <img
                                                src="{{ isset($screenshot->path) && Storage::disk('public')->exists($screenshot->path) ? Storage::url($screenshot->path) : asset('images/350.png') }}"
                                                alt="{{ $film->name }}">
                                        </a>
                                        <div style="position: absolute; top: 0; right: 0; width: 100%;">
                                            <div style="display: flex; justify-content: space-between;">
                                                <div style="display: flex;">
                                                    <i class="fas fa-times fa-lg remove remove-screen-force"
                                                       style="color: red;" title="Удалить"></i>
                                                </div>
                                                <div style="display: flex;">
                                                    <i class="fas fa-film fa-lg remove select-preview-trailer"
                                                       title="Сделать обложку трейлера"></i>
                                                    <i class="fas fa-arrow-up fa-lg select-preview"
                                                       title="Сделать обложку из скриншота"></i>
                                                    <i class="fas fa-times fa-lg remove remove-screen"
                                                       title="Переместить"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                                <label class="custom-file-upload" style="margin-bottom: 0 !important;"
                                       for="screenshotInput">
                                    <div class="icon">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="" viewBox="0 0 24 24">
                                            <g stroke-width="0" id="SVGRepo_bgCarrier"></g>
                                            <g stroke-linejoin="round" stroke-linecap="round"
                                               id="SVGRepo_tracerCarrier"></g>
                                            <g id="SVGRepo_iconCarrier">
                                                <path fill=""
                                                      d="M10 1C9.73478 1 9.48043 1.10536 9.29289 1.29289L3.29289 7.29289C3.10536 7.48043 3 7.73478 3 8V20C3 21.6569 4.34315 23 6 23H7C7.55228 23 8 22.5523 8 22C8 21.4477 7.55228 21 7 21H6C5.44772 21 5 20.5523 5 20V9H10C10.5523 9 11 8.55228 11 8V3H18C18.5523 3 19 3.44772 19 4V9C19 9.55228 19.4477 10 20 10C20.5523 10 21 9.55228 21 9V4C21 2.34315 19.6569 1 18 1H10ZM9 7H6.41421L9 4.41421V7ZM14 15.5C14 14.1193 15.1193 13 16.5 13C17.8807 13 19 14.1193 19 15.5V16V17H20C21.1046 17 22 17.8954 22 19C22 20.1046 21.1046 21 20 21H13C11.8954 21 11 20.1046 11 19C11 17.8954 11.8954 17 13 17H14V16V15.5ZM16.5 11C14.142 11 12.2076 12.8136 12.0156 15.122C10.2825 15.5606 9 17.1305 9 19C9 21.2091 10.7909 23 13 23H20C22.2091 23 24 21.2091 24 19C24 17.1305 22.7175 15.5606 20.9844 15.122C20.7924 12.8136 18.858 11 16.5 11Z"
                                                      clip-rule="evenodd" fill-rule="evenodd"></path>
                                            </g>
                                        </svg>
                                    </div>
                                    <div class="text">
                                        <span>Загрузить скриншот</span>
                                    </div>
                                    <input type="file" id="screenshotInput" accept="{{ $mimeTypeImage }}" multiple>
                                </label>
                            </div>

                            <h4 style="margin-top: 1em">Удаленные скриншоты</h4>
                            <div class="gallery removed">
                                @foreach($detail?->screenshots()->onlyTrashed()->get() as $screenshot)
                                    <div class="photo-container" data-id="{{ $screenshot->id }}">
                                        <a href="{{ isset($screenshot->path) && Storage::url($screenshot->path) }}"
                                           data-fancybox="gallery" class="photo">
                                            <img
                                                src="{{ isset($screenshot->path) && Storage::url($screenshot->path) ? Storage::url($screenshot->path) : asset('images/350.png') }}"
                                                alt="{{ $film->name }}">
                                        </a>
                                        <div style="position: absolute; top: 0; right: 0; width: 100%;">
                                            <div style="display: flex; justify-content: space-between;">
                                                <div style="display: flex;">
                                                    <i class="fas fa-times fa-lg remove remove-screen-force"
                                                       style="right: 65px; color: red;" title="Удалить"></i>
                                                </div>
                                                <div style="display: flex;">
                                                    <i class="fas fa-arrow-up fa-lg select-preview"
                                                       title="Сделать обложку из скриншота"></i>
                                                    <i class="fas fa-times fa-lg remove remove-screen"
                                                       title="Переместить"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            @php
                                $extraScreens = \App\Http\Helpers\DetailHelper::getExtraScreenshots($detail, $film->preview_grid);
                            @endphp
                            @if (!empty($extraScreens))
                                <h4 class="extra-title" style="margin-top: 1em">Лишние скриншоты</h4>
                                <div class="gallery extra">
                                    @endif
                                    @foreach($extraScreens as $screenshot)
                                        <div class="photo-container">
                                            <a href="{{ $screenshot }}" data-fancybox="gallery" class="photo">
                                                <img src="{{ $screenshot }}" alt="{{ $film->name }}">
                                            </a>
                                            <div style="position: absolute; top: 0; right: 0; width: 100%;">
                                                <div style="display: flex; justify-content: space-between;">
                                                    <div style="display: flex;">
                                                        <i class="fas fa-times fa-lg remove remove-screen-force"
                                                           style="color: red;" title="Удалить"></i>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                    @if (!empty($extraScreens))
                                </div>
                            @endif

                            <h4 style="margin-top: 2em">Файлы</h4>
                            <div style="display: flex; justify-content: flex-end; margin-top: 10px;">
                                <button id="add-file" class="btn btn-orange">
                                    Добавить
                                </button>
                            </div>

                            <div id="files">
                                @foreach($detail?->files()->withTrashed()->get()->sortBy('version') as $file)
                                    <div id="old-{{ $file->id }}" class="download-container old"
                                         data-file-id="{{ $file->id }}">
                                        <i class="fas fa-times fa-lg remove remove-force-file"
                                           style="float: left; margin-right: 10px; color: red;"
                                           title="Жесткое удаление файла или ссылки"></i>

                                        <i class="fas fa-times fa-lg remove remove-file"
                                           style="float: right; margin-left: 10px; margin-top: 3px;"
                                           title="Мягкое удаление файла или ссылки"></i>

                                        <div class="error error_download">
                                            <h3></h3>
                                        </div>

                                        <div class="spoiler">
                                            @if (($film->is_sponsor && !$file->is_link) || (!$film->is_sponsor && $file->is_link))
                                                <h2 class="info-sign" style="color: black">(Не подходит тип ячейки для
                                                    отображения)</h2>
                                            @endif
                                            @if ($file->trashed())
                                                <h2 class="removed-sign" style="color: black">УДАЛЕНО</h2>
                                            @endif
                                            <div class="spoiler-header"
                                                 style="{{ $file?->additional_info ? 'cursor: pointer;' : 'cursor: auto;' }}">
                                                <div style="display: flex; align-items: flex-start;">
                                                    <h2 class="download-title">
                                                        Размер
                                                        <input type="text" class="file-block-input size"
                                                               value="{{ $file?->size }}" style="text-align: center">
                                                        Версия
                                                        <input type="text" class="file-block-input version"
                                                               value="{{ $file->version }}"
                                                               style="text-align: center">
                                                    </h2>

                                                    @if ($file?->additional_info)
                                                        <span class="toggle-icon">▲</span>
                                                    @endif
                                                </div>

                                                <div style="display: flex;">
                                                    @if ($file->is_link)
                                                        <a data-code="{{ base64_encode($file->id) }}"
                                                           class="btn btn-success download"
                                                           style="background-image: url({{ asset('images/download-button-link-bg.png') }});">
                                                            Перейти на сайт
                                                        </a>
                                                    @elseif (pathinfo($file->name, PATHINFO_EXTENSION) == \App\Models\File::getExtendedFile()[0])
                                                        <a data-code="{{ base64_encode($file->id) }}"
                                                           class="btn btn-success download"
                                                           style="background-image: url({{ asset('images/download-button-link-bg.png') }});">
                                                            Скачать .{{ pathinfo($file->name, PATHINFO_EXTENSION) }}
                                                        </a>
                                                    @endif

                                                    <div class="download-count">
                                                        <i class="fa fa-download"
                                                           style="margin-right: 5px; margin-top: 10px;"></i>
                                                        <span>{{ $file->downloadStatistic()->count() }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="spoiler-content" style="text-align: center">
                                                <textarea id="edit-old-spoiler-{{ $file->id }}"></textarea>
                                                <div class="spoiler-description">
                                                    {!! $file?->additional_info !!}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach

                                @foreach(\App\Http\Helpers\DetailHelper::getExtraFiles($detail, $film->preview_grid) as $file)
                                    <div class="download-container extra">
                                        <i class="fas fa-times fa-lg remove remove-force-file"
                                           style="float: left; margin-right: 10px; color: red;"
                                           title="Жесткое удаление файла или ссылки"></i>

                                        <div class="error error_download">
                                            <h3></h3>
                                        </div>

                                        <div class="spoiler">
                                            <h2 class="removed-sign" style="color: black">Лишний файл</h2>
                                            <div class="spoiler-header" style="cursor: auto;">
                                                <div style="display: flex; align-items: flex-start;">
                                                    <h2 class="download-title">
                                                        {{ $file }}
                                                    </h2>
                                                </div>

                                                <div style="display: flex;">
                                                    <a href="{{ $file }}" class="btn btn-success">
                                                        <i class="fa fa-download" style="margin-right: 5px;"
                                                           aria-hidden="true"></i>
                                                        Скачать .file
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <div id="template-download" hidden>
                                <x-detailmodule.spoiler-download isSponsor="{{$film->is_sponsor}}"
                                                                 memeType="{{ $mimeTypeFile }}"></x-detailmodule.spoiler-download>
                            </div>

                            <div class="error error_save_detail">
                                <h3></h3>
                            </div>

                            <div style="display: flex; justify-content: space-between; flex-wrap: wrap;">
                                <div>
                                    <button id="delete-film"
                                            data-film-id="{{ $film->id }}"
                                            class="btn btn-danger" style="margin-top: 10px;">
                                        Удалить фильм
                                    </button>
                                </div>
                                <div>
                                    <a href="{{ route('detail.index.uri', ['uri' => $film->uri]) }}" target="_blank"
                                       class="btn btn-orange" style="margin-top: 10px;">
                                        Просмотр прошедшей версии</a>
                                    <button id="release-detail" class="btn btn-orange" style="margin-top: 10px;">
                                        Сохранить фильм
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="clearfix"></div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <link rel="stylesheet" href="../../../../node_modules/summernote/dist/summernote-bs4.min.css">
    <script src="../../../../node_modules/summernote/dist/summernote.min.js"></script>
    <script type="module"
            src="{{ asset('Modules/DetailModule/resources/assets/js/edit.js') }}?version={{ config('app.version') }}"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.5.7/jquery.fancybox.min.css">
    <link rel="stylesheet" href="{{ asset('public/lib/thedatepicker/dist/the-datepicker.css') }}"/>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.5.7/jquery.fancybox.min.js"></script>
    <script src="{{ asset('public/lib/thedatepicker/dist/the-datepicker.js') }}"></script>
@endsection
