@extends('layouts.site')

@section('title', 'Dịch vụ')

@section('content')
    <!-- breadcam_area_start -->
    <div class="breadcam_area breadcam_bg_1 zigzag_bg_2">
        <div class="container">
            <div class="row">
                <div class="col-xl-12">
                    <div class="breadcam_thumb text-center">
                        <h3>Dịch vụ của chúng tôi</h3>
                        <p>Danh sách tất cả dịch vụ chăm sóc tóc và làm đẹp</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- breadcam_area_end -->

    <!-- service_area_start -->
    <div class="service_area custom-spacing">
        <div class="container">
            @if($types->count() > 0)
            <div class="row mb-4">
                <div class="col-xl-12">
                    <div class="text-center mb-4">
                        <a href="{{ route('site.services.index') }}" class="boxed-btn3 service-list-filter {{ !$typeId ? 'active' : '' }}">
                            Tất cả
                        </a>
                        @foreach($types as $type)
                            <a href="{{ route('site.services.index', ['type' => $type->id]) }}" class="boxed-btn3 service-list-filter {{ $typeId == $type->id ? 'active' : '' }}">
                                {{ $type->name }}
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            <div class="row">
                <div class="col-xl-12">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover service-table">
                            <thead>
                                <tr>
                                    <th class="stt-col">STT</th>
                                    <th>Tên dịch vụ</th>
                                    <th>Danh mục</th>
                                    <th>Mô tả</th>
                                    <th class="price-col">Giá</th>
                                    <th class="action-col">Hành động</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($services as $index => $service)
                                    <tr>
                                        <td class="text-center">{{ $index + 1 }}</td>
                                        <td>
                                            <strong class="service-name">{{ $service->name }}</strong>
                                        </td>
                                        <td>
                                            <span class="service-category-badge">
                                                {{ $service->category->name ?? 'Chưa phân loại' }}
                                            </span>
                                        </td>
                                        <td>
                                            <p class="service-description">
                                                {{ Str::limit($service->description ?? 'Chưa có mô tả', 100) }}
                                            </p>
                                        </td>
                                        <td class="text-center">
                                            @if($service->serviceVariants && $service->serviceVariants->count() > 0)
                                                @php
                                                    $minPrice = $service->serviceVariants->min('price');
                                                    $maxPrice = $service->serviceVariants->max('price');
                                                @endphp
                                                <strong class="service-price">
                                                    @if($minPrice == $maxPrice)
                                                        {{ number_format($minPrice, 0, ',', '.') }}đ
                                                    @else
                                                        {{ number_format($minPrice, 0, ',', '.') }}đ - {{ number_format($maxPrice, 0, ',', '.') }}đ
                                                    @endif
                                                </strong>
                                            @else
                                                <span class="service-price-contact">Liên hệ</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <a href="{{ route('site.services.show', $service->id) }}" class="boxed-btn3 service-detail-btn">
                                                Xem chi tiết
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="service-empty">
                                            <h3>Không có dịch vụ nào</h3>
                                            <p>Hiện tại chưa có dịch vụ nào trong danh mục này.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- service_area_end -->
@endsection

