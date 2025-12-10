<div class="service-detail">
    <div class="row">
        <div class="col-md-4">
            @if($service->image)
                <img src="{{ asset('legacy/images/products/' . $service->image) }}" alt="{{ $service->name }}" class="img-fluid rounded mb-3">
            @else
                <div class="bg-light rounded p-5 text-center mb-3">
                    <i class="fas fa-image fa-3x text-muted"></i>
                    <p class="text-muted mt-2">Không có ảnh</p>
                </div>
            @endif
        </div>
        <div class="col-md-8">
            <h4 class="mb-3">{{ $service->name }}</h4>
            <table class="table table-bordered">
                <tr>
                    <th width="30%">Mã dịch vụ:</th>
                    <td>{{ $service->service_code ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <th>Loại:</th>
                    <td>
                        @if($service->serviceVariants->count() > 0)
                            <span class="badge badge-info">
                                <i class="fas fa-layer-group"></i> Dịch vụ biến thể ({{ $service->serviceVariants->count() }} biến thể)
                            </span>
                        @else
                            <span class="badge badge-primary">
                                <i class="fas fa-tag"></i> Dịch vụ đơn
                            </span>
                        @endif
                    </td>
                </tr>
                <tr>
                    <th>Nhóm dịch vụ:</th>
                    <td>{{ $service->category->name ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <th>Giá:</th>
                    <td>
                        @if($service->base_price)
                            <strong class="text-primary">{{ number_format($service->base_price, 0, ',', '.') }} đ</strong>
                        @elseif($service->serviceVariants->count() > 0)
                            @php
                                $minPrice = $service->serviceVariants->min('price');
                                $maxPrice = $service->serviceVariants->max('price');
                            @endphp
                            @if($minPrice == $maxPrice)
                                <strong class="text-primary">{{ number_format($minPrice, 0, ',', '.') }} đ</strong>
                            @else
                                <strong class="text-primary">{{ number_format($minPrice, 0, ',', '.') }} - {{ number_format($maxPrice, 0, ',', '.') }} đ</strong>
                            @endif
                        @else
                            N/A
                        @endif
                    </td>
                </tr>
                <tr>
                    <th>Thời gian:</th>
                    <td>
                        @if($service->base_duration)
                            <strong class="text-info">{{ $service->base_duration }} phút</strong>
                        @elseif($service->serviceVariants->count() > 0)
                            @php
                                $minDuration = $service->serviceVariants->min('duration');
                                $maxDuration = $service->serviceVariants->max('duration');
                            @endphp
                            @if($minDuration == $maxDuration)
                                <strong class="text-info">{{ $minDuration }} phút</strong>
                            @else
                                <strong class="text-info">{{ $minDuration }} - {{ $maxDuration }} phút</strong>
                            @endif
                        @else
                            N/A
                        @endif
                    </td>
                </tr>
                <tr>
                    <th>Trạng thái:</th>
                    <td>
                        <span class="badge badge-{{ $service->status == 'Hoạt động' ? 'success' : 'secondary' }}">
                            {{ $service->status }}
                        </span>
                    </td>
                </tr>
                @if($service->description)
                <tr>
                    <th>Mô tả:</th>
                    <td>{{ $service->description }}</td>
                </tr>
                @endif
            </table>
        </div>
    </div>

    @if($service->serviceVariants->count() > 0)
    <div class="mt-4">
        <h5 class="mb-3">
            <i class="fas fa-layer-group"></i> Danh sách biến thể ({{ $service->serviceVariants->count() }})
        </h5>
        <div class="table-responsive">
            <table class="table table-bordered table-sm">
                <thead class="thead-light">
                    <tr>
                        <th>Tên biến thể</th>
                        <th>Giá</th>
                        <th>Thời lượng</th>
                        <th>Thuộc tính</th>
                        <th>Trạng thái</th>
                        <th>Ghi chú</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($service->serviceVariants as $variant)
                        <tr>
                            <td>
                                <strong>{{ $variant->name }}</strong>
                                @if($variant->is_default)
                                    <span class="badge badge-warning">Mặc định</span>
                                @endif
                            </td>
                            <td><strong class="text-primary">{{ number_format($variant->price, 0, ',', '.') }} đ</strong></td>
                            <td>{{ $variant->duration }} phút</td>
                            <td>
                                @if($variant->variantAttributes->count() > 0)
                                    @foreach($variant->variantAttributes as $attr)
                                        <span class="badge badge-secondary mr-1">
                                            {{ $attr->attribute_name }}: {{ $attr->attribute_value }}
                                        </span>
                                    @endforeach
                                @else
                                    <span class="text-muted">Không có</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge badge-{{ $variant->is_active ? 'success' : 'secondary' }}">
                                    {{ $variant->is_active ? 'Hoạt động' : 'Vô hiệu hóa' }}
                                </span>
                            </td>
                            <td>{{ $variant->notes ?? '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>

