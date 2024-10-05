@extends('layouts.main')
@section('content')

    <div class="blog">
        <div class="container">
            <h2 class="title">{{ $title }}</h2>
            <div class="requirements-container">
                <div class="system-requirements categories">
                    <h2 class="section-title">Категории</h2>
                    <ul class="requirement-list">
                        @foreach($categories as $category)
                            <li class="requirement-edit category" data-code="{{ base64_encode($category->id) }}">
                                @if (Auth::check() && Auth::user()->checkOwner())
                                    <input id="category-label-{{ $category->id }}" type="text" class="category-label detail-summary-input"
                                           value="{{ $category->label }}">
                                    <input id="category-url-{{ $category->id }}" type="text" class="category-url detail-summary-input"
                                           value="{{ $category->url }}">
                                    <label class="checkbox-container soft-checkbox" for="is_soft_{{ $category->id }}" style="margin-left: 5px; margin-top: 11px;">
                                        Софт
                                        <input type="checkbox" id="is_soft_{{ $category->id }}" value="1" {{ $category->for_soft ? 'checked' : '' }}>
                                        <span class="checkmark"></span>
                                    </label>
                                    <i class="fas fa-times fa-lg remove-category remove" style="padding: 15px;"></i>
                                @endif

                                @if (Auth::check() && Auth::user()->checkAdmin())
                                    <label id="category-label-{{ $category->id }}" type="text" class="category-label detail-summary-input">
                                        {{ $category->label }}
                                    </label>
                                    <label id="category-url-{{ $category->id }}" type="text" class="category-url detail-summary-input">
                                        {{ $category->url }}
                                    </label>

                                    <label class="checkbox-container soft-checkbox" for="is_soft_{{ $category->id }}" style="margin-left: 5px; margin-top: 11px;">
                                        Софт
                                        <input type="checkbox" id="is_soft_{{ $category->id }}" value="1" {{ $category->for_soft ? 'checked' : '' }} disabled>
                                        <span class="checkmark"></span>
                                    </label>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                    <div class="button-group-require">
                        <button id="add-category" class="btn btn-orange" style="margin-top: 10px; width: 100%;">
                            Добавить строчку
                        </button>
                    </div>
                </div>

                <div class="system-requirements repacks">
                    <h2 class="section-title">Репаки</h2>
                    <ul class="requirement-list">
                        @foreach($repacks as $repack)
                            <li class="requirement-edit repack" data-code="{{ base64_encode($repack->id) }}">
                                @if (Auth::check() && Auth::user()->checkOwner())
                                    <input id="repack-label-{{ $repack->id }}" type="text" class="repack-label detail-summary-input"
                                           value="{{ $repack->label }}">
                                    <input id="repack-url-{{ $repack->id }}" type="text" class="repack-url detail-summary-input"
                                           value="{{ $repack->url }}">
                                    <i class="fas fa-times fa-lg remove-repack remove" style="padding: 15px;"></i>
                                @endif

                                @if (Auth::check() && Auth::user()->checkAdmin())
                                    <label id="repack-label-{{ $repack->id }}" type="text" class="repack-label detail-summary-input">
                                        {{ $repack->label }}
                                    </label>
                                    <label id="repack-url-{{ $repack->id }}" type="text" class="repack-url detail-summary-input">
                                        {{ $repack->url }}
                                    </label>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                    <div class="button-group-require">
                        <button id="add-repack" class="btn btn-orange" style="margin-top: 10px; width: 100%;">
                            Добавить строчку
                        </button>
                    </div>
                </div>
            </div>

            <div class="error">
                <h3></h3>
            </div>

            <button id="save" class="btn btn-orange"
                    style="margin-top: 10px; float: right;">
                Сохранить
            </button>
        </div>
    </div>

    <script type="module" src="{{ asset('Modules/ListModule/resources/assets/js/dynamic-menu.js') }}?version={{ config('app.version') }}"></script>
@endsection
