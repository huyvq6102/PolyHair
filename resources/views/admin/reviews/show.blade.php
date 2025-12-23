@extends('admin.layouts.app')

@php
    use Illuminate\Support\Facades\Storage;
@endphp

@section('title', 'Chi tiết bình luận')

@section('content')
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Chi tiết bình luận #{{ $review->id }}</h1>
        <div>
            {{-- <a href="{{ route('admin.reviews.edit', $review->id) }}" class="btn btn-warning">
                <i class="fas fa-edit"></i> Sửa
            </a> --}}
            <a href="{{ route('admin.reviews.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Quay lại
            </a>
        </div>
    </div>

    <!-- Review Info -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Thông tin bình luận</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="font-weight-bold">Nội dung bình luận:</label>
                        <p class="form-control-plaintext border p-3 bg-light rounded">
                            {{ $review->comment ?? 'N/A' }}
                        </p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="font-weight-bold">Đánh giá:</label>
                        <div class="d-flex align-items-center">
                            @if($review->rating)
                                @for($i = 1; $i <= 5; $i++)
                                    @if($i <= $review->rating)
                                        <i class="fas fa-star text-warning fa-2x"></i>
                                    @else
                                        <i class="far fa-star text-secondary fa-2x"></i>
                                    @endif
                                @endfor
                                <span class="ml-3 h5 mb-0">({{ $review->rating }}/5)</span>
                            @else
                                <span class="text-muted">Chưa đánh giá</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="font-weight-bold">Ngày, giờ:</label>
                        <p class="form-control-plaintext">
                            @if($review->created_at)
                                <i class="fas fa-calendar"></i> {{ $review->created_at->format('d/m/Y') }}
                                <br>
                                <i class="fas fa-clock"></i> {{ $review->created_at->format('H:i:s') }}
                            @else
                                N/A
                            @endif
                        </p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="font-weight-bold">Cập nhật lần cuối:</label>
                        <p class="form-control-plaintext">
                            @if($review->updated_at)
                                <i class="fas fa-calendar"></i> {{ $review->updated_at->format('d/m/Y') }}
                                <br>
                                <i class="fas fa-clock"></i> {{ $review->updated_at->format('H:i:s') }}
                            @else
                                N/A
                            @endif
                        </p>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="font-weight-bold">Trạng thái:</label>
                        <p class="form-control-plaintext">
                            @if($review->is_hidden)
                                <span class="badge badge-danger">
                                    <i class="fas fa-eye-slash"></i> Đã ẩn
                                </span>
                            @else
                                <span class="badge badge-success">
                                    <i class="fas fa-eye"></i> Đang hiển thị
                                </span>
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- User Info -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Thông tin người đánh giá</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="font-weight-bold">Tên:</label>
                        <p class="form-control-plaintext">{{ $review->user->name ?? 'N/A' }}</p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="font-weight-bold">Email:</label>
                        <p class="form-control-plaintext">{{ $review->user->email ?? 'N/A' }}</p>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="font-weight-bold">Số điện thoại:</label>
                        <p class="form-control-plaintext">{{ $review->user->phone ?? 'N/A' }}</p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="font-weight-bold">Giới tính:</label>
                        <p class="form-control-plaintext">{{ $review->user->gender ?? 'N/A' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Booking Info -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Thông tin đặt lịch</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="font-weight-bold">Booking ID:</label>
                        <p class="form-control-plaintext">
                            @if($review->appointment_id)
                                <a href="{{ route('admin.appointments.show', $review->appointment_id) }}" class="text-primary">
                                    #{{ $review->appointment_id }}
                                </a>
                            @else
                                N/A
                            @endif
                        </p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="font-weight-bold">Dịch vụ đã dùng:</label>
                        <p class="form-control-plaintext">
                            @if($review->service)
                                {{ $review->service->name }}
                            @elseif($review->appointment && $review->appointment->appointmentDetails->count() > 0)
                                @php
                                    $serviceNames = [];
                                    foreach ($review->appointment->appointmentDetails as $detail) {
                                        if ($detail->serviceVariant && $detail->serviceVariant->service) {
                                            $serviceNames[] = $detail->serviceVariant->service->name;
                                        } elseif ($detail->combo) {
                                            $serviceNames[] = $detail->combo->name . ' (Combo)';
                                        } elseif ($detail->notes) {
                                            $serviceNames[] = $detail->notes;
                                        }
                                    }
                                @endphp
                                @if(!empty($serviceNames))
                                    {{ implode(', ', array_unique($serviceNames)) }}
                                @else
                                    N/A
                                @endif
                            @else
                                N/A
                            @endif
                        </p>
                    </div>
                </div>
            </div>
            @if($review->employee)
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="font-weight-bold">Nhân viên:</label>
                            <p class="form-control-plaintext">
                                {{ $review->employee->user->name ?? 'N/A' }}
                            </p>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Images -->
    @php
        // Get images from review
        $reviewImages = $review->images;

        // Handle different data formats
        if (is_string($reviewImages)) {
            $decoded = json_decode($reviewImages, true);
            $reviewImages = is_array($decoded) ? $decoded : [];
        }

        // Ensure it's an array
        if (!is_array($reviewImages)) {
            $reviewImages = [];
        }

        // Filter out empty values
        $reviewImages = array_filter($reviewImages, function ($img) {
            return !empty($img);
        });
    @endphp

    @if(count($reviewImages) > 0)
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Hình ảnh đính kèm</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    @foreach($reviewImages as $image)
                        @php
                            // Get image filename - handle both string and array
                            $imageName = '';
                            if (is_string($image)) {
                                $imageName = $image;
                            } elseif (is_array($image)) {
                                $imageName = $image['name'] ?? $image['path'] ?? $image[0] ?? '';
                            }

                            // Skip if empty
                            if (empty($imageName)) {
                                continue;
                            }

                            // Build image URL - use asset() for correct base URL
                            $imageUrl = asset('storage/reviews/' . $imageName);
                        @endphp
                        <div class="col-md-3 mb-3">
                            <a href="{{ $imageUrl }}" target="_blank">
                                <img src="{{ $imageUrl }}" alt="Review Image" class="img-thumbnail"
                                    style="width: 100%; height: 200px; object-fit: cover; cursor: pointer;"
                                    onerror="this.style.display='none';">
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    <!-- Actions -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Thao tác</h6>
        </div>
        <div class="card-body">
            <div class="btn-group" role="group">
                {{-- <a href="{{ route('admin.reviews.edit', $review->id) }}" class="btn btn-warning">
                    <i class="fas fa-edit"></i> Sửa bình luận
                </a> --}}
                <form action="{{ route('admin.reviews.hide', $review->id) }}" method="POST" class="d-inline"
                    onsubmit="return confirm('Bạn có chắc muốn {{ $review->is_hidden ? 'hiển thị' : 'ẩn' }} bình luận này không?');">
                    @csrf
                    <button type="submit" class="btn btn-{{ $review->is_hidden ? 'success' : 'secondary' }}">
                        <i class="fas fa-{{ $review->is_hidden ? 'eye' : 'eye-slash' }}"></i>
                        {{ $review->is_hidden ? 'Hiển thị' : 'Ẩn' }} bình luận
                    </button>
                </form>
                <form action="{{ route('admin.reviews.destroy', $review->id) }}" method="POST" class="d-inline"
                    onsubmit="return confirm('Bạn có chắc muốn xóa vĩnh viễn bình luận này không? Hành động này không thể hoàn tác!');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash"></i> Xóa vĩnh viễn
                    </button>
                </form>
            </div>
        </div>
    </div>
@endsection
