@php
    // TODO: Implement slider service when library/slider model is created
    // Default slider data if no sliders are provided
    $sliders = $sliders ?? [];
    
    // If no sliders, use default with actual image
    if (count($sliders) == 0) {
        $sliders = [
            [
                'name' => 'Chăm sóc chuyên nghiệp',
                'images' => 'banner.png',
                'description' => 'Dịch vụ chăm sóc tóc và làm đẹp hàng đầu'
            ]
        ];
    }
@endphp

@if(count($sliders) > 0)
<!-- slider_area_start -->
<div class="slider_area" style="margin-top: 80px;">
    <div class="container-fluid p-0">
        <div id="carouselExampleControls" class="carousel slide" data-ride="carousel">
            <div class="carousel-inner">
                @foreach($sliders as $index => $slider)
                    <div class="carousel-item {{ $index == 0 ? 'active' : '' }}">
                        <div class="overlay2">
                            @php
                                $imagePath = isset($slider['images']) ? 'legacy/images/sliders/' . $slider['images'] : null;
                                // Check if image exists, if not try to use a default one
                                $imageExists = false;
                                if ($imagePath && file_exists(public_path($imagePath))) {
                                    $imageExists = true;
                                } elseif ($imagePath) {
                                    // Try alternative images if the specified one doesn't exist
                                    $altImages = ['banner.png', '1.png', 'banner_service.png', 'bradcam.png'];
                                    foreach ($altImages as $altImg) {
                                        $altPath = 'legacy/images/sliders/' . $altImg;
                                        if (file_exists(public_path($altPath))) {
                                            $imagePath = $altPath;
                                            $imageExists = true;
                                            break;
                                        }
                                    }
                                }
                            @endphp
                            
                            @if($imageExists)
                                <img style="height: 100vh; object-fit: cover;" src="{{ asset($imagePath) }}" class="img-fluid d-block w-100" alt="{{ $slider['name'] ?? 'Slider' }}">
                                <div class="slider_text text-center">
                                    <h3 class="">{{ $slider['name'] ?? 'Chăm sóc chuyên nghiệp' }}</h3>
                                    <p>{{ $slider['description'] ?? 'Dịch vụ chăm sóc tóc và làm đẹp hàng đầu' }}</p>
                                    <div class="book_room">
                                        <div class="book_btn d-lg-block">
                                            <a class="popup-with-form" href="#test-form">Đặt lịch ngay</a>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <!-- Placeholder background if image doesn't exist -->
                                <div style="height: 100vh; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center;">
                                    <div class="slider_text text-center" style="position: relative; z-index: 2;">
                                        <h3 class="text-white" style="font-size: 3rem; margin-bottom: 1rem;">{{ $slider['name'] ?? 'Chăm sóc chuyên nghiệp' }}</h3>
                                        <p class="text-white" style="font-size: 1.5rem; margin-bottom: 2rem;">{{ $slider['description'] ?? 'Dịch vụ chăm sóc tóc và làm đẹp hàng đầu' }}</p>
                                        <div class="book_room">
                                            <div class="book_btn d-lg-block">
                                                <a class="popup-with-form" href="#test-form">Đặt lịch ngay</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
            @if(count($sliders) > 1)
            <a class="carousel-control-prev" href="#carouselExampleControls" role="button" data-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="sr-only">Previous</span>
            </a>
            <a class="carousel-control-next" href="#carouselExampleControls" role="button" data-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="sr-only">Next</span>
            </a>
            @endif
        </div>
    </div>
</div>
<!-- slider_area_end -->
@endif

