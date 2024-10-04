<div class="banner-slider" style="margin-top: 1em;">
    <div class="swiper" data-interval="3000">
        <div class="swiper-wrapper">
            @foreach ($banners as $index => $banner)
                <div class="swiper-slide">
                    @if ($banner->media_type == 'image')
                        <a data-code="{{ base64_encode($banner->id) }}" class="itc-slider-item banner-jump"
                           style="cursor: pointer;">
                            <div class="image-container">
                                <img src="{{ Storage::url($banner->banner_path) }}" alt="Banner Image" class="image">
                            </div>
                        </a>
                    @elseif ($banner->media_type == 'video')
                        <a data-code="{{ base64_encode($banner->id) }}" style="cursor: pointer"
                           class="itc-slider-item banner-jump">
                            <div class="video-container">
                                <video autoplay muted loop
                                       style="object-fit: cover; width: 100%; height: 100%; cursor: pointer;">
                                    <source src="{{ Storage::url($banner->banner_path) }}" type="video/webm">
                                </video>
                            </div>
                        </a>
                    @endif
                </div>
            @endforeach
        </div>

        <div class="swiper-scrollbar"></div>
    </div>
</div>
