# Hướng dẫn chuyển đổi sang Laravel 12

## Tổng quan

Dự án đã được chuyển đổi từ PHP thuần sang Laravel 12 với cấu trúc chuẩn.

## Thông tin đăng nhập Admin

- **Email**: admin@example.com
- **Password**: admin123
- **URL**: `/admin` hoặc `http://localhost/laravel-app/public/admin`

## Cấu trúc dự án

```
laravel-app/
├── app/
│   ├── Http/Controllers/
│   │   ├── Site/          # Controllers cho frontend
│   │   └── Admin/         # Controllers cho admin panel
│   ├── Models/            # Eloquent Models
│   └── Services/          # Business logic (thay thế libs/)
├── database/
│   ├── migrations/        # Database migrations
│   └── seeders/           # Database seeders
├── resources/views/
│   ├── layouts/           # Layout templates
│   ├── site/              # Frontend views
│   └── admin/             # Admin views
├── routes/
│   ├── web.php            # Site routes
│   └── admin.php          # Admin routes
└── public/
    └── legacy/            # Assets từ dự án cũ
```

## Đã hoàn thành

### 1. Database & Models
- ✅ Tạo migrations cho tất cả các bảng
- ✅ Cấu hình Models với relationships đầy đủ
- ✅ Chạy migrations thành công
- ✅ Import dữ liệu từ polybarber.sql

### 2. Services
- ✅ CategoryService, TypeService
- ✅ ProductService, ServiceService
- ✅ BarberService, AppointmentService
- ✅ OrderService, SettingService
- ✅ NewsService, WordTimeService

### 3. Controllers
- ✅ Site Controllers: Home, Product, Service, Blog, Contact
- ✅ Admin Controllers: Dashboard, Category, Product, Service, Type, Appointment, Order, Setting

### 4. Routes & Middleware
- ✅ Routes cho Site và Admin
- ✅ Middleware `EnsureUserIsAdmin` để bảo vệ admin routes

### 5. Views
- ✅ Layout Site với header/footer
- ✅ View Home
- ✅ Layout Admin với sidebar/topbar
- ✅ Admin Dashboard
- ✅ Admin Categories (index, create, edit)

## Cần hoàn thiện

### Views còn thiếu
- [ ] Admin: Products, Services, Types, Appointments, Orders, Users, Barbers, News (create/edit)
- [ ] Site: Product-list, Product-detail, Service-list, Service-detail, Blog, Contact

### Chức năng cần bổ sung
- [ ] Authentication: Đăng nhập/đăng ký cho Site (đã có Breeze)
- [ ] Cart: Giỏ hàng và checkout
- [ ] Booking: Đặt lịch hẹn
- [ ] Comments & Evaluates: Bình luận và đánh giá
- [ ] Gallery: Quản lý thư viện ảnh
- [ ] Statistics: Thống kê và báo cáo

## Cách sử dụng

### 1. Cấu hình môi trường
```bash
cd laravel-app
cp .env.example .env
php artisan key:generate
```

### 2. Cấu hình database trong .env
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=polybarber
DB_USERNAME=root
DB_PASSWORD=
```

### 3. Chạy migrations
```bash
php artisan migrate
```

### 4. Import dữ liệu (nếu cần)
```bash
php artisan db:seed --class=ImportDataSeeder
```

### 5. Tạo user admin
```bash
php artisan db:seed --class=AdminUserSeeder
```

### 6. Chạy server
```bash
php artisan serve
```

## Lưu ý

1. **Assets**: Tất cả assets từ dự án cũ đã được copy vào `public/legacy/`
2. **Images**: Hình ảnh được lưu trong `public/legacy/images/`
3. **Database**: Đảm bảo database `polybarber` đã được tạo
4. **Permissions**: Đảm bảo thư mục `storage` và `bootstrap/cache` có quyền ghi

## Tiếp tục phát triển

1. Hoàn thiện các views còn thiếu
2. Thêm validation và error handling
3. Tối ưu performance (caching, eager loading)
4. Viết tests
5. Tối ưu assets với Vite

