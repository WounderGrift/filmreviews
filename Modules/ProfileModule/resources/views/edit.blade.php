@extends('main::layouts.main')
@section('content')

    <div class="error error-profile">
        <h3></h3>
    </div>

    <div class="about">
        <div class="container">
            <form id="profile-update" action="{{ route('profile.update') }}" method="POST" data-profile-id="{{ base64_encode($profile->id) }}" onsubmit="return false;">
                <div class="input-container profile-center">
                    <input type="text" class="custom-input" id="profile-name" name="name"
                       value="{{ $profile->name }}" placeholder="{{ $profile->name }}">
                </div>
                <div class="row about-info-grids">
                    <div class="col-md-5 col-sm-12 abt-pic profile-center">
                        <div class="header-avatar">
                            <img id="avatar" src="{{ $profile->avatar_path ? Storage::url($profile->avatar_path) : asset('images/350.png') }}?timestamp={{ $profile->updated_at->timestamp }}"
                                 class="img-responsive profile-avatar" alt="{{ $profile->avatar_name ?? 'avatar' }}"/>
                        </div>
                        <label for="fileInput" class="footer-avatar">
                            <svg fill="#000000" viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg">
                                <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                                <g id="SVGRepo_iconCarrier">
                                    <path d="M15.331 6H8.5v20h15V14.154h-8.169z"></path>
                                    <path d="M18.153 6h-.009v5.342H23.5v-.002z"></path>
                                </g>
                            </svg>
                            <p id="avatar-name">{{ $profile->avatar_name ?? 'Аватар не выбран' }}</p>
                            <svg id="avatar-remove" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                                <g id="SVGRepo_iconCarrier">
                                    <path
                                        d="M5.16565 10.1534C5.07629 8.99181 5.99473 8 7.15975 8H16.8402C18.0053 8 18.9237 8.9918 18.8344 10.1534L18.142 19.1534C18.0619 20.1954 17.193 21 16.1479 21H7.85206C6.80699 21 5.93811 20.1954 5.85795 19.1534L5.16565 10.1534Z"
                                        stroke="#000000" stroke-width="2"></path>
                                    <path d="M19.5 5H4.5" stroke="#000000" stroke-width="2"
                                          stroke-linecap="round"></path>
                                    <path d="M10 3C10 2.44772 10.4477 2 11 2H13C13.5523 2 14 2.44772 14 3V5H10V3Z"
                                          stroke="#000000" stroke-width="2"></path>
                                </g>
                            </svg>
                        </label>
                        <input id="fileInput" type="file" name="avatar"
                           accept="{{ $mimeTypeImage }}">
                    </div>

                    <div class="col-md-7 col-sm-12 abt-info-pic profile-center">
                        <div class="input-container profile-center" style="width: 100%">
                            <input id="status" type="text" class="custom-input" name="status" value="{{ $profile->status }}"
                                placeholder="{{ 'Статус' }}">
                        </div>

                        <div class="input-container profile-center" style="width: 100%">
                            <input id="about" type="text" class="custom-input" name="about_me" value="{{ $profile->about_me }}"
                               placeholder="{{ 'Расскажи немного о себе, пожалуйста' }}">
                        </div>

                        <div class="input-container profile-center" style="width: 100%">
                            <input id="cid" type="text" class="custom-input" name="cid"
                               placeholder="{{ 'CID: ' . $profile->cid }}">
                        </div>

                        <div class="input-container profile-center" style="width: 100%">
                            <input id="email" type="text" class="custom-input" name="email"
                               placeholder="{{ $profile->email }}">
                        </div>

                        <div class="input-container profile-center" style="width: 100%">
                            <input id="password" type="text" class="custom-input" name="password"
                               placeholder="Пароль">
                        </div>

                        <label class="checkbox-container" for="mailing">
                            Хочу получать письма о новинках
                            <input type="checkbox" id="mailing" name="get_letter_release" value="1"
                                {{$profile->get_letter_release ? 'checked' : ''}}>
                            <span class="checkmark"></span>
                        </label>

                        @if ($profile->id != Auth::check() && Auth::user()->id && Auth::user()->checkOwnerOrAdmin())
                            <div>
                                @foreach($profile->getRoleOption() as $key => $role)
                                    <label class="checkbox-container preview-detail-files" data-role="{{ $key }}"
                                           style="color: {{ $key == $profile->role ? "var(--pink)" : "black" }};">
                                        {{ $role }}
                                    </label>
                                @endforeach
                            </div>
                        @endif

                        <ul>
                            <li>Дата регистрации: {{ \App\Http\Helpers\DateHelper::dateFormatterJFY($profile->created_at, $profile->timezone) }}</li>
                            @if ($profile->last_activity && $profile->timezone)
                                <li>Последний онлайн: {{ \App\Http\Helpers\DateHelper::getLastActivity($profile->last_activity, $profile->timezone) }}</li>
                            @endif
                        </ul>
                        <button type="submit" class="btn btn-orange" style="color: #000">Сохранить</button>
                    </div>
                </div>
            </form>

{{--            TODO achivement--}}
            @if (false)
                <div class="testimonals">
                    <h3>Витрина достижений</h3>
                    <div class="testimonal-grids">
                        <div class="col-md-4 testimonal-grid">
                            <div class="testi-info">
                                <p>""..Mauris congue, dolor at vehicula scelerisque, enim odio vehicula turpis, vitae
                                    luctus dolor nisi eget est. Aliquam maximus felis eget varius mattis. Quisque
                                    tristique nibh imperdiet dignissim molestie.""</p>
                                <h4>Mark Johnson</h4>
                                <a href="mailto:example@gmail.com">http://www.example.com</a>
                            </div>
                        </div>
                        <div class="col-md-4 testimonal-grid">
                            <div class="testi-info">
                                <p>""..Mauris congue, dolor at vehicula scelerisque, enim odio vehicula turpis, vitae
                                    luctus dolor nisi eget est. Aliquam maximus felis eget varius mattis. Quisque
                                    tristique nibh imperdiet dignissim molestie.""</p>
                                <h4>Wiiams Deo</h4>
                                <a href="mailto:example@gmail.com">http://www.example.com</a>
                            </div>
                        </div>
                        <div class="col-md-4 testimonal-grid">
                            <div class="testi-info">
                                <p>""..Mauris congue, dolor at vehicula scelerisque, enim odio vehicula turpis, vitae
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
                                <p>""..Mauris congue, dolor at vehicula scelerisque, enim odio vehicula turpis, vitae
                                    luctus dolor nisi eget est. Aliquam maximus felis eget varius mattis. Quisque
                                    tristique nibh imperdiet dignissim molestie.""</p>
                                <h4>Mark Johnson</h4>
                                <a href="mailto:example@gmail.com">http://www.example.com</a>
                            </div>
                        </div>
                        <div class="col-md-4 testimonal-grid">
                            <div class="testi-info">
                                <p>""..Mauris congue, dolor at vehicula scelerisque, enim odio vehicula turpis, vitae
                                    luctus dolor nisi eget est. Aliquam maximus felis eget varius mattis. Quisque
                                    tristique nibh imperdiet dignissim molestie.""</p>
                                <h4>Wiiams Deo</h4>
                                <a href="mailto:example@gmail.com">http://www.example.com</a>
                            </div>
                        </div>
                        <div class="col-md-4 testimonal-grid">
                            <div class="testi-info">
                                <p>""..Mauris congue, dolor at vehicula scelerisque, enim odio vehicula turpis, vitae
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
                                <p>""..Mauris congue, dolor at vehicula scelerisque, enim odio vehicula turpis, vitae
                                    luctus dolor nisi eget est. Aliquam maximus felis eget varius mattis. Quisque
                                    tristique nibh imperdiet dignissim molestie.""</p>
                                <h4>Mark Johnson</h4>
                                <a href="mailto:example@gmail.com">http://www.example.com</a>
                            </div>
                        </div>
                        <div class="col-md-4 testimonal-grid">
                            <div class="testi-info">
                                <p>""..Mauris congue, dolor at vehicula scelerisque, enim odio vehicula turpis, vitae
                                    luctus dolor nisi eget est. Aliquam maximus felis eget varius mattis. Quisque
                                    tristique nibh imperdiet dignissim molestie.""</p>
                                <h4>Wiiams Deo</h4>
                                <a href="mailto:example@gmail.com">http://www.example.com</a>
                            </div>
                        </div>
                        <div class="col-md-4 testimonal-grid">
                            <div class="testi-info">
                                <p>""..Mauris congue, dolor at vehicula scelerisque, enim odio vehicula turpis, vitae
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

    <script type="module" src="{{ asset('Modules/ProfileModule/resources/assets/js/edit.js') }}?version={{ config('app.version') }}"></script>
@endsection
