<div class="combo-detail">
    <div class="row">
        <div class="col-md-4">
            @if($combo->image)
                <img src="{{ asset('legacy/images/products/' . $combo->image) }}" alt="{{ $combo->name }}" class="img-fluid rounded mb-3">
            @else
                <div class="bg-light rounded p-5 text-center mb-3">
                    <i class="fas fa-image fa-3x text-muted"></i>
                    <p class="text-muted mt-2">Không có ảnh</p>
                </div>
            @endif
        </div>
        <div class="col-md-8">
            <h4 class="mb-3">{{ $combo->name }}</h4>
            <table class="table table-bordered">
                <tr>
                    <th width="30%">Mã combo:</th>
                    <td>COMBO-{{ $combo->id }}</td>
                </tr>
                <tr>
                    <th>Loại:</th>
                    <td>
                        <span class="badge badge-warning">
                            <i class="fas fa-box"></i> Combo ({{ $combo->comboItems->count() }} dịch vụ)
                        </span>
                    </td>
                </tr>
                <tr>
                    <th>Nhóm dịch vụ:</th>
                    <td>{{ $combo->category->name ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <th>Giá combo:</th>
                    <td><strong class="text-primary">{{ number_format($combo->price, 0, ',', '.') }} đ</strong></td>
                </tr>
                <tr>
                    <th>Trạng thái:</th>
                    <td>
                        <span class="badge badge-{{ $combo->status == 'Hoạt động' ? 'success' : 'secondary' }}">
                            {{ $combo->status }}
                        </span>
                    </td>
                </tr>
                @if($combo->description)
                <tr>
                    <th>Mô tả:</th>
                    <td>{{ $combo->description }}</td>
                </tr>
                @endif
            </table>
        </div>
    </div>

    @if($combo->comboItems->count() > 0)
    <div class="mt-4">
        <h5 class="mb-3">
            <i class="fas fa-list"></i> Danh sách dịch vụ trong combo ({{ $combo->comboItems->count() }})
        </h5>
        <div class="table-responsive">
            <table class="table table-bordered table-sm">
                <thead class="thead-light">
                    <tr>
                        <th>STT</th>
                        <th>Tên dịch vụ</th>
                        <th>Biến thể</th>
                        <th>Số lượng</th>
                        <th>Giá ghi chú</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($combo->comboItems as $index => $item)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>
                                <strong>{{ $item->service->name ?? 'N/A' }}</strong>
                                @if($item->service)
                                    <br><small class="text-muted">({{ $item->service->service_code ?? 'N/A' }})</small>
                                @endif
                            </td>
                            <td>
                                @if($item->service_variant_id && $item->service)
                                    @php
                                        $variant = $item->service->serviceVariants->firstWhere('id', $item->service_variant_id);
                                    @endphp
                                    @if($variant)
                                        {{ $variant->name }}
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>{{ $item->quantity ?? 1 }}</td>
                            <td>
                                @if($item->price_override)
                                    <strong class="text-warning">{{ number_format($item->price_override, 0, ',', '.') }} đ</strong>
                                    <br><small class="text-muted">(Giá ghi đè)</small>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>

