## Kế hoạch chuyển đổi sang Laravel 12

### 1. Chuẩn bị môi trường
- Cấu hình `.env` cho kết nối MySQL hiện tại; import `polybarber.sql` vào database mới.
- Cài đặt các package hỗ trợ: `laravel/breeze` (auth cơ bản), `spatie/laravel-permission` (phân quyền), `barryvdh/laravel-debugbar` (debug trong giai đoạn chuyển đổi).
- Thiết lập Vite để biên dịch asset; chuyển toàn bộ `content/css`, `content/js`, `content/fonts` sang `resources/` và `public/`.

### 2. Thiết kế kiến trúc ứng dụng
- Domain chính: `Appointments`, `Barbers`, `Categories`, `Products`, `Services`, `Orders`, `Users`, `Feedback (Comments/Contacts/Evaluates)`, `News`, `Settings`.
- Tạo các Model + Migration tương ứng; quy đổi logic từ các file trong `libs/` thành Repository/Service class trong `app/Services`.
- Tổ chức module quản trị trong namespace `App\Http\Controllers\Admin` (route prefix `admin`, middleware auth + permission).
- Giao diện người dùng đặt trong namespace `App\Http\Controllers\Site` với layout Blade và component hóa từng phần (`layout/header.php` → `resources/views/layouts/app.blade.php`, v.v.).

### 3. Lập trình tính năng
- **Xác thực người dùng**: Sử dụng Breeze cho phần frontend, mở rộng guard thứ hai cho admin nếu cần. Di chuyển các chức năng `login.php`, `forgot-password.php`, `resetPass/`.
- **Quản trị**: Một route group `admin` với dashboard, CRUD cho `categories`, `products`, `services`, `news`, `users`, `appointments`, `orders`, `feedback`. Tách các form hiện tại thành Request class để validate.
- **Đặt lịch (booking)**: Xây dựng flow đặt lịch dùng Eloquent (`appointments`, `times`, `services`). Áp dụng transaction khi tạo đơn.
- **Sản phẩm & giỏ hàng**: Port logic từ `libs/cart.php`, `site/cart.php`, `site/checkout.php` sang Controller + Service; dùng session/DB theo chuẩn Laravel.
- **Blog/Tin tức**: Migration + seeder cho `news`; tạo `NewsController` cho frontend và admin.
- **Thống kê/Báo cáo**: Sử dụng Query Builder để tổng hợp dữ liệu, chuyển các trang `admin/statistic` thành controller riêng + view Blade.

### 4. Quản lý dữ liệu & seed
- Chuyển cấu trúc bảng từ `polybarber.sql` sang Laravel migration (ưu tiên bảng `users`, `roles`, `appointments`, `services`, `products`, `orders`, `order_details`, `categories`, `feedback`, `news`).
- Viết Seeder để nhập dữ liệu mẫu/hiện có; xem xét Artisan command để import dữ liệu legacy nếu cần.

### 5. Asset & giao diện
- Sao chép hình ảnh sang `public/images`.
- Chuyển các file SCSS hiện trong `admin/resource/scss` vào `resources/scss`, cấu hình Vite để build CSS cho admin+frontend.
- Tái sử dụng HTML hiện có nhưng tách thành Blade layout, component và partial (`@include`).

### 6. Kiểm thử & triển khai
- Viết Feature test cho các flow quan trọng (đăng nhập, đặt lịch, giỏ hàng, CRUD admin).
- Thiết lập CI (GitHub Actions/GitLab CI) chạy `phpunit`, `phpstan`, `laravel pint`.
- Chuẩn bị hướng dẫn triển khai (env production, queue, schedule nếu cần cho nhắc lịch).

### 7. Lộ trình thực hiện
1. Import DB + viết migration.
2. Setup Auth (Breeze + phân quyền).
3. Chuyển module Admin (CRUD theo ưu tiên: categories → services → appointments → orders).
4. Chuyển module Site (home, product, booking).
5. Hoàn thiện phần còn lại (blog, feedback, thống kê).
6. Viết test + tối ưu asset.

