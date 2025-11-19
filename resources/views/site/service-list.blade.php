@extends('layouts.site')

@section('title', 'Dịch vụ')

@section('content')
    @php
        $sliders = [
            [
                'name' => 'Dịch vụ của chúng tôi',
                'images' => 'banner_service.png',
                'description' => 'Danh sách tất cả dịch vụ chăm sóc tóc và làm đẹp'
            ]
        ];
    @endphp
    @include('site.partials.slider')

    <!-- service_area_start -->
    <div style="padding: 60px 0; margin-bottom: 100px;">
        <div class="container">
            @if($types->count() > 0)
            <div class="row mb-4">
                <div class="col-xl-12">
                    <div class="text-center mb-4">
                        <a href="{{ route('site.services.index') }}" class="boxed-btn3 {{ !$typeId ? 'active' : '' }}" style="margin: 5px; display: inline-block; padding: 10px 20px; background: {{ !$typeId ? '#4A3600' : '#fff' }}; color: {{ !$typeId ? '#fff' : '#4A3600' }}; border: 1px solid #4A3600; text-decoration: none; border-radius: 5px;">
                            Tất cả
                        </a>
                        @foreach($types as $type)
                            <a href="{{ route('site.services.index', ['type' => $type->id]) }}" class="boxed-btn3 {{ $typeId == $type->id ? 'active' : '' }}" style="margin: 5px; display: inline-block; padding: 10px 20px; background: {{ $typeId == $type->id ? '#4A3600' : '#fff' }}; color: {{ $typeId == $type->id ? '#fff' : '#4A3600' }}; border: 1px solid #4A3600; text-decoration: none; border-radius: 5px;">
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
                        <table class="table table-bordered table-hover" style="background: #fff; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                            <thead style="background: #4A3600; color: #fff;">
                                <tr>
                                    <th style="padding: 15px; text-align: center; width: 60px;">STT</th>
                                    <th style="padding: 15px;">Tên dịch vụ</th>
                                    <th style="padding: 15px;">Danh mục</th>
                                    <th style="padding: 15px;">Mô tả</th>
                                    <th style="padding: 15px; text-align: center; width: 150px;">Giá</th>
                                    <th style="padding: 15px; text-align: center; width: 120px;">Hành động</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($services as $index => $service)
                                    <tr>
                                        <td style="padding: 15px; text-align: center; vertical-align: middle;">{{ $index + 1 }}</td>
                                        <td style="padding: 15px; vertical-align: middle;">
                                            <strong style="color: #4A3600; font-size: 16px;">{{ $service->name }}</strong>
                                        </td>
                                        <td style="padding: 15px; vertical-align: middle;">
                                            <span style="background: #f0f0f0; padding: 5px 10px; border-radius: 3px; font-size: 14px;">
                                                {{ $service->category->name ?? 'Chưa phân loại' }}
                                            </span>
                                        </td>
                                        <td style="padding: 15px; vertical-align: middle;">
                                            <p style="margin: 0; color: #666; font-size: 14px;">
                                                {{ Str::limit($service->description ?? 'Chưa có mô tả', 100) }}
                                            </p>
                                        </td>
                                        <td style="padding: 15px; text-align: center; vertical-align: middle;">
                                            @if($service->serviceVariants && $service->serviceVariants->count() > 0)
                                                @php
                                                    $minPrice = $service->serviceVariants->min('price');
                                                    $maxPrice = $service->serviceVariants->max('price');
                                                @endphp
                                                <strong style="color: #BC9321; font-size: 16px;">
                                                    @if($minPrice == $maxPrice)
                                                        {{ number_format($minPrice, 0, ',', '.') }}đ
                                                    @else
                                                        {{ number_format($minPrice, 0, ',', '.') }}đ - {{ number_format($maxPrice, 0, ',', '.') }}đ
                                                    @endif
                                                </strong>
                                            @else
                                                <span style="color: #999;">Liên hệ</span>
                                            @endif
                                        </td>
                                        <td style="padding: 15px; text-align: center; vertical-align: middle;">
                                            <a href="{{ route('site.services.show', $service->id) }}" class="boxed-btn3" style="padding: 8px 15px; background: #4A3600; color: #fff; text-decoration: none; border-radius: 5px; font-size: 14px; display: inline-block;">
                                                Xem chi tiết
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" style="padding: 60px 15px; text-align: center;">
                                            <h3 style="color: #999; margin-bottom: 10px;">Không có dịch vụ nào</h3>
                                            <p style="color: #999;">Hiện tại chưa có dịch vụ nào trong danh mục này.</p>
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

