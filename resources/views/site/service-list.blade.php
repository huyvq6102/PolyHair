@extends('layouts.site')

@section('title', 'Dịch vụ')

@section('content')

    <!-- service_area_start -->
    <div style="padding-top: 120px;"></div>
    <section class="service-section py-5">
  <div class="container">
    <div class="d-flex align-items-start mb-3">
      <span class="bar mr-2"></span>
      <div>
        <h3 class="title ba-title mb-0">DỊCH VỤ TÓC & COMBO</h3>
        <p class="desc">
            Chào mừng Quý khách hàng đến với POLY HAIR ,
            nơi mang đến cho bạn trải nghiệm làm đẹp tinh tế và độc đáo.
            Dưới đây là danh sách tất cả các dịch vụ làm tóc của chúng tôi.
        </p>
      </div>
    </div>
  </div>
  <div class="container service-wrapper">
    <!-- Sidebar Filter -->
    <div class="service_left">
      <div class="service-left-inner">
        <h4 class="service-title mb-3">Bộ lọc</h4>

        <form method="GET" action="{{ route('site.services.index') }}" id="filterForm">
          <!-- Search by Name -->
          <div class="filter-group mb-3">
            <h5 class="filter-title mb-2" style="font-size: 12px; margin-bottom: 6px;">Tìm kiếm</h5>
            <input type="text" name="keyword" class="form-control filter-select" placeholder="Nhập tên dịch vụ..." value="{{ $keyword ?? '' }}" id="keywordInput" style="padding: 6px 10px; font-size: 13px;">
          </div>

          <!-- Filter by Type -->
          <div class="filter-group mb-3">
            <h5 class="filter-title mb-2" style="font-size: 12px; margin-bottom: 6px;">Loại dịch vụ</h5>
            <select name="filter_type" class="form-control filter-select" id="filterType" style="padding: 6px 10px; font-size: 13px;">
              <option value="all" {{ ($filterType ?? 'all') == 'all' ? 'selected' : '' }}>Tất cả</option>
              <option value="service_single" {{ ($filterType ?? '') == 'service_single' ? 'selected' : '' }}>Dịch vụ lẻ</option>
              <option value="service_variant" {{ ($filterType ?? '') == 'service_variant' ? 'selected' : '' }}>Gói dịch vụ</option>
              <option value="combo" {{ ($filterType ?? '') == 'combo' ? 'selected' : '' }}>Combo</option>
            </select>
          </div>

          <!-- Filter by Category -->
          @if(isset($categories) && $categories->count() > 0)
          <div class="filter-group mb-3">
            <h5 class="filter-title mb-2" style="font-size: 12px; margin-bottom: 6px;">Danh mục</h5>
            <select name="category" class="form-control filter-select" id="categorySelect" style="padding: 6px 10px; font-size: 13px;">
              <option value="">Tất cả danh mục</option>
              @foreach($categories as $category)
                <option value="{{ $category->id }}" {{ (isset($categoryId) && $categoryId == $category->id) ? 'selected' : '' }}>
                  {{ $category->name }}
                </option>
              @endforeach
            </select>
          </div>
          @endif

          <!-- Filter by Price -->
          <div class="filter-group mb-3">
            <h5 class="filter-title mb-2" style="font-size: 12px; margin-bottom: 6px;">Khoảng giá</h5>
            <select name="price_range" class="form-control filter-select mb-2" id="priceRange" style="padding: 6px 10px; font-size: 13px;">
              <option value="">Tất cả mức giá</option>
              <option value="0-50000" {{ (isset($minPrice) && $minPrice == 0 && isset($maxPrice) && $maxPrice == 50000) ? 'selected' : '' }}>Dưới 50.000 VNĐ</option>
              <option value="50000-100000" {{ (isset($minPrice) && $minPrice == 50000 && isset($maxPrice) && $maxPrice == 100000) ? 'selected' : '' }}>50.000 - 100.000 VNĐ</option>
              <option value="100000-200000" {{ (isset($minPrice) && $minPrice == 100000 && isset($maxPrice) && $maxPrice == 200000) ? 'selected' : '' }}>100.000 - 200.000 VNĐ</option>
              <option value="200000-300000" {{ (isset($minPrice) && $minPrice == 200000 && isset($maxPrice) && $maxPrice == 300000) ? 'selected' : '' }}>200.000 - 300.000 VNĐ</option>
              <option value="300000-500000" {{ (isset($minPrice) && $minPrice == 300000 && isset($maxPrice) && $maxPrice == 500000) ? 'selected' : '' }}>300.000 - 500.000 VNĐ</option>
              <option value="500000-1000000" {{ (isset($minPrice) && $minPrice == 500000 && isset($maxPrice) && $maxPrice == 1000000) ? 'selected' : '' }}>500.000 - 1.000.000 VNĐ</option>
              <option value="1000000-999999999" {{ (isset($minPrice) && $minPrice == 1000000) ? 'selected' : '' }}>Trên 1.000.000 VNĐ</option>
              <option value="custom">Tùy chỉnh</option>
            </select>
            <div class="price-inputs" id="customPriceInputs" style="display: none;">
              <input type="text" name="min_price" class="form-control filter-select mb-2" placeholder="Giá tối thiểu (vnđ)" value="{{ $minPrice ?? '' }}" id="minPrice" data-price-input style="padding: 6px 10px; font-size: 13px;">
              <input type="text" name="max_price" class="form-control filter-select" placeholder="Giá tối đa (vnđ)" value="{{ $maxPrice ?? '' }}" id="maxPrice" data-price-input style="padding: 6px 10px; font-size: 13px;">
              <div id="priceError" class="text-danger mt-2" style="display: none; font-size: 12px;">
                <i class="fa fa-exclamation-circle"></i> Vui lòng điền khoảng giá phù hợp (Giá tối thiểu phải nhỏ hơn hoặc bằng giá tối đa)
              </div>
            </div>
          </div>

          <!-- Sort -->
          <div class="filter-group mb-3">
            <h5 class="filter-title mb-2" style="font-size: 12px; margin-bottom: 6px;">Sắp xếp</h5>
            <select name="sort_by" class="form-control filter-select" id="sortBy" style="padding: 6px 10px; font-size: 13px;">
              <option value="id_desc" {{ ($sortBy ?? 'id_desc') == 'id_desc' ? 'selected' : '' }}>Mới nhất</option>
              <option value="name_asc" {{ ($sortBy ?? '') == 'name_asc' ? 'selected' : '' }}>Tên A-Z</option>
              <option value="name_desc" {{ ($sortBy ?? '') == 'name_desc' ? 'selected' : '' }}>Tên Z-A</option>
              <option value="price_asc" {{ ($sortBy ?? '') == 'price_asc' ? 'selected' : '' }}>Giá tăng dần</option>
              <option value="price_desc" {{ ($sortBy ?? '') == 'price_desc' ? 'selected' : '' }}>Giá giảm dần</option>
            </select>
          </div>

          <!-- Filter Buttons -->
          <div class="filter-actions">
            <button type="submit" class="btn btn-primary btn-block mb-2" style="padding: 8px 12px; font-size: 13px;">Áp dụng</button>
            <a href="{{ route('site.services.index') }}" class="btn btn-secondary btn-block">Xóa bộ lọc</a>
          </div>

          <!-- Keep existing type parameter for backward compatibility -->
          @if(isset($typeId))
            <input type="hidden" name="type" value="{{ $typeId }}">
          @endif
        </form>
      </div>
    </div>

    <div class="service_right">

      <div class="service-grid" id="serviceGrid" style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px;">
        @include('site.partials.service-list-items', ['items' => $items])
      </div>

      <div id="servicePagination">
        @include('site.partials.service-pagination', ['items' => $items])
      </div>

    </div>
  </div>
</section>

    <!-- service_area_end -->

<script>
document.addEventListener('DOMContentLoaded', function() {
    const filterForm = document.getElementById('filterForm');
    const serviceGrid = document.getElementById('serviceGrid');
    const servicePagination = document.getElementById('servicePagination');
    let isLoading = false;

    // Hàm load data bằng AJAX
    function loadServices(url, isPagination = false) {
        if (isLoading) return;
        isLoading = true;

        // Hiển thị loading indicator
        serviceGrid.innerHTML = '<div class="col-12 text-center py-5"><p>Đang tải...</p></div>';
        servicePagination.innerHTML = '';

        // Lưu vị trí scroll hiện tại (chỉ khi không phải pagination)
        const currentScrollPosition = !isPagination ? (window.pageYOffset || document.documentElement.scrollTop) : null;

        // Gửi AJAX request
        fetch(url, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Cập nhật grid và pagination
                serviceGrid.innerHTML = data.html;
                servicePagination.innerHTML = data.pagination || '';

                // Cập nhật URL mà không reload trang
                window.history.pushState({}, '', url);

                // Nếu là pagination, scroll lên đầu phần service grid
                if (isPagination) {
                    setTimeout(function() {
                        const serviceSection = document.querySelector('.service-section');
                        if (serviceSection) {
                            const sectionTop = serviceSection.offsetTop - 120; // Trừ header height
                            window.scrollTo({
                                top: sectionTop,
                                behavior: 'smooth'
                            });
                        } else {
                            // Fallback: scroll lên đầu trang
                            window.scrollTo({
                                top: 0,
                                behavior: 'smooth'
                            });
                        }
                    }, 100);
                } else {
                    // Giữ nguyên vị trí scroll khi filter
                    if (currentScrollPosition !== null) {
                        window.scrollTo(0, currentScrollPosition);
                    }
                }

                // Re-attach pagination event listeners
                attachPaginationListeners();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            serviceGrid.innerHTML = '<div class="col-12 text-center py-5"><p>Có lỗi xảy ra. Vui lòng thử lại.</p></div>';
        })
        .finally(() => {
            isLoading = false;
        });
    }

    // Hàm build URL từ form
    function buildUrl(page = 1) {
        const formData = new FormData(filterForm);
        const params = new URLSearchParams();

        // Xử lý price range
        const priceRange = document.getElementById('priceRange').value;
        if (priceRange && priceRange !== 'custom') {
            // Nếu chọn khoảng giá có sẵn, gửi price_range
            params.append('price_range', priceRange);
        } else if (priceRange === 'custom') {
            // Nếu chọn tùy chỉnh, lấy giá trị từ input (loại bỏ dấu chấm)
            const minPrice = document.getElementById('minPrice').value.replace(/[^\d]/g, '');
            const maxPrice = document.getElementById('maxPrice').value.replace(/[^\d]/g, '');
            if (minPrice) params.append('min_price', minPrice);
            if (maxPrice) params.append('max_price', maxPrice);
        }

        // Thêm các tham số từ form (trừ price_range, min_price, max_price vì đã xử lý ở trên)
        for (const [key, value] of formData.entries()) {
            if (value && key !== 'page' && key !== 'price_range' && key !== 'min_price' && key !== 'max_price') {
                params.append(key, value);
            }
        }

        // Thêm page
        if (page > 1) {
            params.append('page', page);
        }

        const baseUrl = '{{ route("site.services.index") }}';
        return baseUrl + (params.toString() ? '?' + params.toString() : '');
    }

    // Xử lý form submit
    filterForm.addEventListener('submit', function(e) {
        e.preventDefault();

        // Validate giá nếu đang ở chế độ custom
        const priceRange = document.getElementById('priceRange').value;
        if (priceRange === 'custom') {
            if (!validatePriceRange()) {
                return false;
            }
        }

        loadServices(buildUrl(1));
    });

    // Xử lý thay đổi select (auto submit)
    const autoSubmitSelects = ['filterType', 'categorySelect', 'sortBy', 'priceRange'];
    autoSubmitSelects.forEach(selectId => {
        const select = document.getElementById(selectId);
        if (select) {
            select.addEventListener('change', function() {
                if (selectId === 'priceRange') {
                    // Xử lý price range
                    const priceRange = this.value;
                    const customInputs = document.getElementById('customPriceInputs');
                    const minPriceInput = document.getElementById('minPrice');
                    const maxPriceInput = document.getElementById('maxPrice');
                    const priceError = document.getElementById('priceError');

                    if (priceRange === 'custom') {
                        customInputs.style.display = 'block';
                        // Xóa giá trị từ khoảng giá đã chọn trước đó
                        minPriceInput.value = '';
                        maxPriceInput.value = '';
                        // Ẩn thông báo lỗi nếu có
                        if (priceError) {
                            priceError.style.display = 'none';
                        }
                        // Reset border color
                        minPriceInput.style.borderColor = '';
                        maxPriceInput.style.borderColor = '';
                    } else {
                        customInputs.style.display = 'none';
                        if (priceRange) {
                            const [min, max] = priceRange.split('-');
                            minPriceInput.value = min;
                            maxPriceInput.value = max === '999999999' ? '' : max;
                        } else {
                            minPriceInput.value = '';
                            maxPriceInput.value = '';
                        }
                        loadServices(buildUrl(1));
                    }
                } else {
                    loadServices(buildUrl(1));
                }
            });
        }
    });

    // Hàm validate giá
    function validatePriceRange() {
        const minPriceInput = document.getElementById('minPrice');
        const maxPriceInput = document.getElementById('maxPrice');
        const priceError = document.getElementById('priceError');

        const minPrice = parseInt(minPriceInput.value.replace(/[^\d]/g, '')) || 0;
        const maxPrice = parseInt(maxPriceInput.value.replace(/[^\d]/g, '')) || 0;

        // Chỉ validate nếu cả 2 đều có giá trị
        if (minPrice > 0 && maxPrice > 0 && minPrice > maxPrice) {
            priceError.style.display = 'block';
            minPriceInput.style.borderColor = '#dc3545';
            maxPriceInput.style.borderColor = '#dc3545';
            return false;
        } else {
            priceError.style.display = 'none';
            minPriceInput.style.borderColor = '';
            maxPriceInput.style.borderColor = '';
            return true;
        }
    }

    // Format số khi nhập vào input giá
    function formatPriceInput(input) {
        // Lấy giá trị, loại bỏ tất cả ký tự không phải số
        let value = input.value.replace(/[^\d]/g, '');

        // Format với dấu chấm phân cách hàng nghìn
        if (value) {
            value = parseInt(value).toLocaleString('vi-VN');
            input.value = value;
        }

        // Validate sau khi format
        validatePriceRange();
    }

    // Xử lý format cho các input giá
    const priceInputs = document.querySelectorAll('[data-price-input]');
    priceInputs.forEach(input => {
        // Format khi blur (rời khỏi input)
        input.addEventListener('blur', function() {
            formatPriceInput(this);
        });

        // Format khi nhập
        input.addEventListener('input', function() {
            formatPriceInput(this);
        });

        // Lấy số thực khi submit (loại bỏ dấu chấm)
        input.addEventListener('keyup', function(e) {
            if (e.key === 'Enter') {
                if (validatePriceRange()) {
                    this.value = this.value.replace(/[^\d]/g, '');
                    loadServices(buildUrl(1));
                }
            }
        });
    });

    // Xử lý khi chọn price range
    const priceRangeSelect = document.getElementById('priceRange');
    if (priceRangeSelect && priceRangeSelect.value === 'custom') {
        document.getElementById('customPriceInputs').style.display = 'block';
    }

    // Xử lý Enter trong input keyword
    const keywordInput = document.getElementById('keywordInput');
    if (keywordInput) {
        keywordInput.addEventListener('keyup', function(e) {
            if (e.key === 'Enter') {
                loadServices(buildUrl(1));
            }
        });
    }

    // Hàm attach pagination listeners
    function attachPaginationListeners() {
        const paginationLinks = document.querySelectorAll('[data-ajax-pagination]');
        paginationLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const url = this.getAttribute('href');
                if (url) {
                    loadServices(url, true); // true = isPagination
                }
            });
        });
    }

    // Attach pagination listeners khi trang load
    attachPaginationListeners();

    // Xử lý nút "Áp dụng"
    const applyButton = filterForm.querySelector('button[type="submit"]');
    if (applyButton) {
        applyButton.addEventListener('click', function(e) {
            e.preventDefault();

            // Validate giá nếu đang ở chế độ custom
            const priceRange = document.getElementById('priceRange').value;
            if (priceRange === 'custom') {
                if (!validatePriceRange()) {
                    return false;
                }
            }

            loadServices(buildUrl(1));
        });
    }
});
</script>
@endsection

