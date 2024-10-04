@extends('main::layouts.main')
@section('content')

    @if (!$profile)
        <div class="info-block">
            <div class="info_title"><b>Профиль не найден</b></div>
            <div class="news_content">Мы не нашли такого профиля у нас.</div>
        </div>
    @else
        @if ($profile->is_banned)
            <div class="info-block">
                <div class="info_title"><b>Блокировка</b></div>
                <div class="news_content">Была выдана вам, за неадекватное поведение. Научитесь себя вести.</div>
            </div>
        @endif

        <div class="error error-verify">
            <h3></h3>
        </div>

        @if (Auth::check() && Auth::user()->id === $profile->id && !$profile->is_verify && !$profile->is_banned)
            <form id="verify-email"
                  action="{{ route('profile.send-email-verify', ['name' => $profile->name, 'email' => $profile->email]) }}"
                  method="GET" onsubmit="return false;">
                <div class="info-block">
                    <div class="info_title" data-name="{{ $profile->name }}">
                        <b>Спасибо за создание профиля на моем сайте!</b>
                    </div>
                    <div class="news_content" data-email="{{ $profile->email }}">
                        Мы отправили письмо для верификации вашей электронной почты: {{ $profile->email }}</div>
                    <button type="submit" class="btn btn-orange" style="color: #000">Отправить еще раз</button>
                </div>
            </form>
        @endif

        <div class="about">
            <div class="container">
                <h2>{{ $profile->name }}</h2>
                <div class="row about-info-grids">
                    <div class="col-md-5 col-sm-12 abt-pic profile-center">
                        <div class="profile-avatar" style="display:none;">
                            @if ($profile->is_banned)
                                <img src="{{ asset('images/banned.png') }}"
                                     class="img-responsive profile-avatar"
                                     alt="{{ $profile->avatar_name ?? 'images/banned.png' }}"/>
                            @else
                                <img
                                    src="{{ $profile->avatar_path ? Storage::url($profile->avatar_path) : asset('images/350.png') }}?timestamp={{ $profile->updated_at->timestamp }}"
                                    class="img-responsive profile-avatar"
                                    alt="{{ $profile->avatar_name ?? 'images/350.png' }}"/>
                            @endif
                        </div>

                        <div class="profile-skeleton-avatar">
                            <x-skeleton-loader style="width: 350px; height: 350px"></x-skeleton-loader>
                        </div>

                        @if (Auth::check())
                            @if (($profile->id === Auth::user()->id && !$profile->is_banned) || ($profile->role !== $profile::ROLE_OWNER && Auth::user()->checkAdmin()) || Auth::user()->checkOwner())
                                <a href="{{ route('profile.edit', ['cid' => $profile->cid]) }}"
                                   class="btn btn-orange edit-button">
                                    Редактировать
                                </a>
                            @endif
                            @if (($profile->id !== Auth::user()->id) && ($profile->role !== $profile::ROLE_OWNER && Auth::user()->checkAdmin() || Auth::user()->checkOwner()))
                                <button id="ban-button" class="btn btn-danger ban-button"
                                        data-code="{{ base64_encode($profile->id) }}">
                                    {{ $profile->is_banned ? 'Разблокировать' : 'Заблокировать' }}
                                </button>
                            @endif
                        @endif
                    </div>
                    <div class="col-md-7 col-sm-12 abt-info-pic profile-center">
                        <h3>{{ $profile->status ?? 'Статус' }}</h3>
                        <p>{{ $profile->about_me ?? 'Расскажи немного о себе, пожалуйста' }}</p>

                        <ul>
                            <li>Дата
                                регистрации: {{ \App\Http\Helpers\DateHelper::dateFormatterJFY($profile->created_at, $profile->timezone) }}</li>
                            @if ($profile->last_activity && $profile->timezone)
                                <li>Последний
                                    онлайн: {{ \App\Http\Helpers\DateHelper::getLastActivity($profile->last_activity, Auth::user()->timezone) }}</li>
                            @endif
                        </ul>
                        <div id="chartContainer" style="height: 300px; width: 100%;"
                             data-code="{{ base64_encode($profile->id) }}"></div>
                    </div>
                </div>

                {{--            TODO achivement--}}
                @if (false)
                    <div class="testimonals">
                        <h3>Витрина достижений</h3>
                        <div class="testimonal-grids">
                            <div class="col-md-4 testimonal-grid">
                                <div class="testi-info">
                                    <p>""..Mauris congue, dolor at vehicula scelerisque, enim odio vehicula turpis,
                                        vitae
                                        luctus dolor nisi eget est. Aliquam maximus felis eget varius mattis. Quisque
                                        tristique nibh imperdiet dignissim molestie.""</p>
                                    <h4>Mark Johnson</h4>
                                    <a href="mailto:example@gmail.com">http://www.example.com</a>
                                </div>
                            </div>
                            <div class="col-md-4 testimonal-grid">
                                <div class="testi-info">
                                    <p>""..Mauris congue, dolor at vehicula scelerisque, enim odio vehicula turpis,
                                        vitae
                                        luctus dolor nisi eget est. Aliquam maximus felis eget varius mattis. Quisque
                                        tristique nibh imperdiet dignissim molestie.""</p>
                                    <h4>Wiiams Deo</h4>
                                    <a href="mailto:example@gmail.com">http://www.example.com</a>
                                </div>
                            </div>
                            <div class="col-md-4 testimonal-grid">
                                <div class="testi-info">
                                    <p>""..Mauris congue, dolor at vehicula scelerisque, enim odio vehicula turpis,
                                        vitae
                                        luctus dolor nisi eget est. Aliquam maximus felis eget varius mattis. Quisque
                                        tristique nibh imperdiet dignissim molestie.""</p>
                                    <h4>Mark Johnson</h4>
                                    <a href="mailto:example@gmail.com">http://www.example.com</a>
                                </div>
                            </div>
                            <div class="clearfix"></div>
                        </div>
                    </div>

                    <div class="testimonals">
                        <h3>Награды профиля</h3>
                        <div class="testimonal-grids">
                            <div class="col-md-4 testimonal-grid">
                                <div class="testi-info">
                                    <p>""..Mauris congue, dolor at vehicula scelerisque, enim odio vehicula turpis,
                                        vitae
                                        luctus dolor nisi eget est. Aliquam maximus felis eget varius mattis. Quisque
                                        tristique nibh imperdiet dignissim molestie.""</p>
                                    <h4>Mark Johnson</h4>
                                    <a href="mailto:example@gmail.com">http://www.example.com</a>
                                </div>
                            </div>
                            <div class="col-md-4 testimonal-grid">
                                <div class="testi-info">
                                    <p>""..Mauris congue, dolor at vehicula scelerisque, enim odio vehicula turpis,
                                        vitae
                                        luctus dolor nisi eget est. Aliquam maximus felis eget varius mattis. Quisque
                                        tristique nibh imperdiet dignissim molestie.""</p>
                                    <h4>Wiiams Deo</h4>
                                    <a href="mailto:example@gmail.com">http://www.example.com</a>
                                </div>
                            </div>
                            <div class="col-md-4 testimonal-grid">
                                <div class="testi-info">
                                    <p>""..Mauris congue, dolor at vehicula scelerisque, enim odio vehicula turpis,
                                        vitae
                                        luctus dolor nisi eget est. Aliquam maximus felis eget varius mattis. Quisque
                                        tristique nibh imperdiet dignissim molestie.""</p>
                                    <h4>Mark Johnson</h4>
                                    <a href="mailto:example@gmail.com">http://www.example.com</a>
                                </div>
                            </div>
                            <div class="clearfix"></div>
                        </div>
                    </div>

                    <div class="testimonals">
                        <h3>Знаки отличия</h3>
                        <div class="testimonal-grids">
                            <div class="col-md-4 testimonal-grid">
                                <div class="testi-info">
                                    <p>""..Mauris congue, dolor at vehicula scelerisque, enim odio vehicula turpis,
                                        vitae
                                        luctus dolor nisi eget est. Aliquam maximus felis eget varius mattis. Quisque
                                        tristique nibh imperdiet dignissim molestie.""</p>
                                    <h4>Mark Johnson</h4>
                                    <a href="mailto:example@gmail.com">http://www.example.com</a>
                                </div>
                            </div>
                            <div class="col-md-4 testimonal-grid">
                                <div class="testi-info">
                                    <p>""..Mauris congue, dolor at vehicula scelerisque, enim odio vehicula turpis,
                                        vitae
                                        luctus dolor nisi eget est. Aliquam maximus felis eget varius mattis. Quisque
                                        tristique nibh imperdiet dignissim molestie.""</p>
                                    <h4>Wiiams Deo</h4>
                                    <a href="mailto:example@gmail.com">http://www.example.com</a>
                                </div>
                            </div>
                            <div class="col-md-4 testimonal-grid">
                                <div class="testi-info">
                                    <p>""..Mauris congue, dolor at vehicula scelerisque, enim odio vehicula turpis,
                                        vitae
                                        luctus dolor nisi eget est. Aliquam maximus felis eget varius mattis. Quisque
                                        tristique nibh imperdiet dignissim molestie.""</p>
                                    <h4>Mark Johnson</h4>
                                    <a href="mailto:example@gmail.com">http://www.example.com</a>
                                </div>
                            </div>
                            <div class="clearfix"></div>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <script src="https://cdn.canvasjs.com/jquery.canvasjs.min.js"></script>
        <script type="module" src="{{ asset('modules/profilemodule/resources/assets/js/profile.js') }}?version={{config('app.version')}}"></script>
    @endif
@endsection
