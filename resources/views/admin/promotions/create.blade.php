@extends('admin.layouts.app')

@section('title', 'Thêm khuyến mãi')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">Thêm khuyến mãi mới</h6>
                <a href="{{ route('admin.promotions.index') }}" class="btn btn-sm btn-secondary">
                    <i class="fas fa-arrow-left"></i> Quay lại
                </a>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.promotions.store') }}" method="POST">
                    @csrf
                    @include('admin.promotions._form', [
                        'promotion' => null, 
                        'statuses' => $statuses,
                        'services' => $services ?? [],
                        'combos' => $combos ?? [],
                        'serviceVariants' => $serviceVariants ?? [],
                        'selectedServiceIds' => [],
                        'selectedComboIds' => [],
                        'selectedVariantIds' => []
                    ])
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Lưu thông tin
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

