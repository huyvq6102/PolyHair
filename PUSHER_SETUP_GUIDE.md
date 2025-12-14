# Hướng dẫn Setup Laravel Broadcasting + Pusher

## 📋 Tổng quan

Hệ thống đã được cấu hình để sử dụng Laravel Broadcasting với Pusher để cập nhật real-time trạng thái appointment. Khi admin thay đổi trạng thái, client sẽ tự động nhận được cập nhật mà không cần reload trang.

## 🔧 Các bước cài đặt

### 1. Đăng ký tài khoản Pusher

1. Truy cập https://pusher.com và đăng ký tài khoản miễn phí
2. Tạo một Channels app mới
3. Lấy các thông tin:
   - App ID
   - Key
   - Secret
   - Cluster (ví dụ: ap1, eu, us2)

### 2. Cấu hình .env

Thêm các biến sau vào file `.env`:

```env
BROADCAST_DRIVER=pusher

PUSHER_APP_ID=your_app_id
PUSHER_APP_KEY=your_app_key
PUSHER_APP_SECRET=your_app_secret
PUSHER_APP_CLUSTER=ap1
PUSHER_HOST=
PUSHER_PORT=443
PUSHER_SCHEME=https
```
### 3. Cài đặt Packages

#chạy 3 lệnh: 
php artisan config:clear
php artisan cache:clear
php artisan config:cache

```bash
# Cài đặt Pusher PHP Server
composer require pusher/pusher-php-server

# Cài đặt Pusher JS (đã thêm vào package.json)
npm install
```


### 4. Cấu hình Routes

File `routes/channels.php` đã được tạo để authorize private channels. Chỉ user sở hữu appointment hoặc admin/employee mới được lắng nghe.

### 5. Cấu hình Bootstrap

File `bootstrap/app.php` đã được cập nhật để load `routes/channels.php`.

## 🎯 Cách hoạt động

### Backend

1. **Event**: `AppointmentStatusUpdated` được tạo và implement `ShouldBroadcast`
2. **Broadcast**: Event được broadcast trên private channel `appointment.{id}`
3. **Trigger**: Event được trigger khi:
   - `AppointmentService::updateStatus()` được gọi
   - `AppointmentService::cancelAppointment()` được gọi
   - `AppointmentService::update()` thay đổi status
   - `AppointmentService::restore()` được gọi

### Frontend

1. **Pusher JS**: Kết nối với Pusher server
2. **Subscribe**: Subscribe vào private channel `private-appointment.{id}`
3. **Listen**: Lắng nghe event `status.updated`
4. **Update UI**: Tự động cập nhật status badge khi nhận được event

## 📁 Files đã được tạo/cập nhật

### Files mới:
- `app/Events/AppointmentStatusUpdated.php` - Event để broadcast
- `routes/channels.php` - Authorization cho private channels
- `config/broadcasting.php` - Cấu hình broadcasting
- `PUSHER_SETUP_GUIDE.md` - File hướng dẫn này

### Files đã cập nhật:
- `composer.json` - Thêm `pusher/pusher-php-server`
- `package.json` - Thêm `pusher-js`
- `app/Services/AppointmentService.php` - Thêm broadcast event
- `bootstrap/app.php` - Load channels.php
- `resources/views/site/appointment/show.blade.php` - Thêm Pusher JS và listener

## 🧪 Testing

1. Mở 2 trình duyệt:
   - Browser 1: Trang admin, đăng nhập và vào edit appointment
   - Browser 2: Trang client, xem chi tiết appointment (cùng appointment ID)

2. Ở Browser 1 (Admin): Thay đổi trạng thái appointment
3. Ở Browser 2 (Client): Kiểm tra xem status badge có tự động cập nhật không

## ⚠️ Lưu ý

1. **Queue**: Nếu sử dụng queue, cần chạy `php artisan queue:work` để xử lý broadcast jobs
2. **Authentication**: Private channels yêu cầu user phải đăng nhập
3. **CORS**: Đảm bảo Pusher app cho phép domain của bạn
4. **Free Plan**: Pusher free plan có giới hạn 200k messages/ngày và 100 concurrent connections

## 🔍 Debug

Nếu không hoạt động, kiểm tra:

1. Console browser có lỗi không?
2. Pusher dashboard có nhận được events không?
3. Laravel logs có lỗi broadcast không?
4. `.env` đã cấu hình đúng chưa?
5. User đã đăng nhập chưa? (Private channel cần auth)

## 📚 Tài liệu tham khảo

- [Laravel Broadcasting](https://laravel.com/docs/broadcasting)
- [Pusher Channels](https://pusher.com/docs/channels/)
- [Pusher JS SDK](https://github.com/pusher/pusher-js)

