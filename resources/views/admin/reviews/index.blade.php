@extends('admin.layouts.app')

@section('title', 'Quản lý bình luận')

@section('content')
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Quản lý bình luận</h1>
</div>

<!-- Filter -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Tìm kiếm và lọc bình luận</h6>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('admin.reviews.index') }}">
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="rating">Số sao</label>
                        <select name="rating" id="rating" class="form-control">
                            <option value="">Tất cả</option>
                            <option value="5" {{ request('rating') == '5' ? 'selected' : '' }}>5 sao</option>
                            <option value="4" {{ request('rating') == '4' ? 'selected' : '' }}>4 sao</option>
                            <option value="3" {{ request('rating') == '3' ? 'selected' : '' }}>3 sao</option>
                            <option value="2" {{ request('rating') == '2' ? 'selected' : '' }}>2 sao</option>
                            <option value="1" {{ request('rating') == '1' ? 'selected' : '' }}>1 sao</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="service_id">Dịch vụ</label>
                        <select name="service_id" id="service_id" class="form-control">
                            <option value="">Tất cả dịch vụ</option>
                            @foreach($services as $service)
                                <option value="{{ $service->id }}" {{ request('service_id') == $service->id ? 'selected' : '' }}>
                                    {{ $service->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label for="date_from">Từ ngày</label>
                        <input type="date" name="date_from" id="date_from" class="form-control" value="{{ request('date_from') }}">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label for="date_to">Đến ngày</label>
                        <input type="date" name="date_to" id="date_to" class="form-control" value="{{ request('date_to') }}">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label for="time_from">Từ giờ</label>
                        <input type="time" name="time_from" id="time_from" class="form-control" value="{{ request('time_from') }}">
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-2">
                    <div class="form-group">
                        <label for="time_to">Đến giờ</label>
                        <input type="time" name="time_to" id="time_to" class="form-control" value="{{ request('time_to') }}">
                    </div>
                </div>
                <div class="col-md-10">
                    <div class="form-group" style="margin-top: 32px;">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> Tìm kiếm
                        </button>
                        <a href="{{ route('admin.reviews.index') }}" class="btn btn-secondary">
                            <i class="fas fa-redo"></i> Làm mới
                        </a>
                        @if(request('show_hidden'))
                            <a href="{{ route('admin.reviews.index', request()->except('show_hidden')) }}" class="btn btn-info">
                                <i class="fas fa-eye"></i> Ẩn bình luận đã ẩn
                            </a>
                        @else
                            <a href="{{ route('admin.reviews.index', array_merge(request()->all(), ['show_hidden' => 1])) }}" class="btn btn-warning">
                                <i class="fas fa-eye-slash"></i> Hiển thị bình luận đã ẩn
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- DataTales Example -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Danh sách bình luận</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>Nội dung</th>
                        <th>Ngày, giờ</th>
                        <th>Tên người bình luận</th>
                        <th>Dịch vụ đã dùng</th>
                        <th>Đánh giá</th>
                        <th>Trạng thái</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reviews as $review)
                        <tr>
                            <td>
                                <div style="max-width: 300px; word-wrap: break-word;">
                                    {{ $review->comment ?? 'N/A' }}
                                </div>
                            </td>
                            <td>
                                @if($review->created_at)
                                    <div>{{ $review->created_at->format('d/m/Y') }}</div>
                                    <div class="text-muted small">{{ $review->created_at->format('H:i:s') }}</div>
                                @else
                                    N/A
                                @endif
                            </td>
                            <td>
                                {{ $review->user->name ?? 'N/A' }}
                            </td>
                            <td>
                                @if($review->service)
                                    {{ $review->service->name }}
                                @elseif($review->appointment && $review->appointment->appointmentDetails->count() > 0)
                                    @php
                                        $serviceNames = [];
                                        foreach($review->appointment->appointmentDetails as $detail) {
                                            if($detail->serviceVariant && $detail->serviceVariant->service) {
                                                $serviceNames[] = $detail->serviceVariant->service->name;
                                            } elseif($detail->combo) {
                                                $serviceNames[] = $detail->combo->name;
                                            } elseif($detail->notes) {
                                                $serviceNames[] = $detail->notes;
                                            }
                                        }
                                    @endphp
                                    {{ !empty($serviceNames) ? implode(', ', array_unique($serviceNames)) : 'N/A' }}
                                @else
                                    N/A
                                @endif
                            </td>
                            <td>
                                @if($review->rating)
                                    <div class="d-flex align-items-center">
                                        @for($i = 1; $i <= 5; $i++)
                                            @if($i <= $review->rating)
                                                <i class="fas fa-star text-warning"></i>
                                            @else
                                                <i class="far fa-star text-secondary"></i>
                                            @endif
                                        @endfor
                                        <span class="ml-2">({{ $review->rating }}/5)</span>
                                    </div>
                                @else
                                    <span class="text-muted">Chưa đánh giá</span>
                                @endif
                            </td>
                            <td>
                                @if($review->is_hidden)
                                    <span class="badge badge-danger">
                                        <i class="fas fa-eye-slash"></i> Đã ẩn
                                    </span>
                                @else
                                    <span class="badge badge-success">
                                        <i class="fas fa-eye"></i> Hiển thị
                                    </span>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="btn-group" role="group">
                                    <a href="{{ route('admin.reviews.show', $review->id) }}" class="btn btn-sm btn-info" title="Xem chi tiết">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    {{-- <a href="{{ route('admin.reviews.edit', $review->id) }}" class="btn btn-sm btn-warning" title="Sửa">
                                        <i class="fas fa-edit"></i>
                                    </a> --}}
                                    <form action="{{ route('admin.reviews.hide', $review->id) }}" method="POST" class="d-inline"
                                          onsubmit="return confirm('Bạn có chắc muốn {{ $review->is_hidden ? 'hiển thị' : 'ẩn' }} bình luận này không?');">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-{{ $review->is_hidden ? 'success' : 'secondary' }}" title="{{ $review->is_hidden ? 'Hiển thị' : 'Ẩn' }}">
                                            <i class="fas fa-{{ $review->is_hidden ? 'eye' : 'eye-slash' }}"></i>
                                        </button>
                                    </form>
                                    <form action="{{ route('admin.reviews.destroy', $review->id) }}" method="POST" class="d-inline"
                                          onsubmit="return confirm('Bạn có chắc muốn xóa vĩnh viễn bình luận này không? Hành động này không thể hoàn tác!');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" title="Xóa vĩnh viễn">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center">Chưa có bình luận nào</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($reviews->hasPages())
            <div class="d-flex justify-content-center mt-4">
                {{ $reviews->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('#dataTable').DataTable({
            "paging": false,
            "info": false,
            "searching": true,
            "ordering": true,
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Vietnamese.json"
            },
            "order": [[1, "desc"]] // Sort by date descending
        });
    });
</script>
@endpush

