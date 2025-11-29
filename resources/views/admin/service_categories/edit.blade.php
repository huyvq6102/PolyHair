@extends('admin.layouts.app')

@section('title', 'Sửa danh mục dịch vụ')

@section('content')
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Sửa danh mục dịch vụ</h1>
    <a href="{{ route('admin.service-categories.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Quay lại
    </a>
</div>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Thông tin danh mục</h6>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.service-categories.update', $category->id) }}" method="POST" class="needs-validation" novalidate>
            @include('admin.service_categories._form', ['category' => $category])
        </form>
    </div>
</div>
@endsection

