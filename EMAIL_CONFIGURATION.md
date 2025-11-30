# Hướng dẫn cấu hình Email - Gmail

## Vấn đề: Email chưa gửi được

Hiện tại hệ thống đang sử dụng mail driver mặc định là `log`, có nghĩa là email chỉ được ghi vào log file thay vì gửi thực sự.

## Hướng dẫn cấu hình Gmail chi tiết

### Bước 1: Bật 2-Step Verification (Xác thực 2 bước)

1. Truy cập: https://myaccount.google.com/security
2. Đăng nhập vào tài khoản Gmail của bạn
3. Tìm mục **"2-Step Verification"** (Xác thực 2 bước)
4. Nhấn vào và làm theo hướng dẫn để bật tính năng này
   - Bạn sẽ cần số điện thoại để nhận mã xác thực
   - Hoặc có thể dùng ứng dụng Google Authenticator

**Lưu ý:** Bạn PHẢI bật 2-Step Verification trước khi có thể tạo App Password.

### Bước 2: Tạo App Password (Mật khẩu ứng dụng)

1. Sau khi đã bật 2-Step Verification, quay lại trang: https://myaccount.google.com/security
2. Tìm mục **"App passwords"** (Mật khẩu ứng dụng)
   - Nếu không thấy, có thể cần đợi vài phút sau khi bật 2-Step Verification
3. Nhấn vào **"App passwords"**
4. Chọn:
   - **Select app:** Chọn "Mail"
   - **Select device:** Chọn "Other (Custom name)" và nhập "PolyHair" hoặc tên bất kỳ
5. Nhấn **"Generate"** (Tạo)
6. Google sẽ hiển thị một mật khẩu 16 ký tự (không có khoảng trắng)
   - Ví dụ: `abcd efgh ijkl mnop`
   - Bạn cần copy toàn bộ mật khẩu này (bao gồm cả khoảng trắng, hoặc bỏ khoảng trắng)

### Bước 3: Cấu hình trong file .env

1. Mở file `.env` trong thư mục gốc của dự án
2. Tìm hoặc thêm các dòng sau (thay thế thông tin của bạn):

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=abcdefghijklmnop
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your-email@gmail.com
MAIL_FROM_NAME="PolyHair"
```

**Ví dụ cụ thể:**
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=cuong2005@gmail.com
MAIL_PASSWORD=abcd efgh ijkl mnop
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=cuong2005@gmail.com
MAIL_FROM_NAME="PolyHair"
```

**Lưu ý quan trọng:**
- `MAIL_USERNAME`: Email Gmail của bạn (ví dụ: cuong2005@gmail.com)
- `MAIL_PASSWORD`: App Password 16 ký tự vừa tạo (có thể có hoặc không có khoảng trắng)
- `MAIL_FROM_ADDRESS`: Phải giống với `MAIL_USERNAME`
- `MAIL_FROM_NAME`: Tên hiển thị trong email (có thể đổi thành tên khác)

### Bước 4: Clear cache và test

1. Mở terminal/command prompt trong thư mục dự án
2. Chạy lệnh để clear cache:
```bash
php artisan config:clear
```

3. Test email bằng cách:
   - Truy cập: `http://127.0.0.1:8000/appointment/test-email?email=your-email@gmail.com`
   - Hoặc đặt lịch và kiểm tra email inbox

### Bước 5: Kiểm tra kết quả

- **Nếu thành công:** Email sẽ xuất hiện trong inbox của bạn
- **Nếu thất bại:** Kiểm tra:
  1. File `storage/logs/laravel.log` để xem lỗi chi tiết
  2. Đảm bảo App Password đã được copy đúng (không có khoảng trắng thừa)
  3. Đảm bảo đã bật 2-Step Verification
  4. Thử tạo App Password mới nếu vẫn không được

### 2. Cấu hình Mailtrap (Để test)

Nếu muốn test email mà không gửi thực sự, sử dụng Mailtrap:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your-mailtrap-username
MAIL_PASSWORD=your-mailtrap-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@polyhair.com
MAIL_FROM_NAME="PolyHair"
```

### 3. Kiểm tra cấu hình

Sau khi cấu hình, truy cập:
- `http://your-domain/appointment/test-email?email=your-email@example.com`
- Hoặc kiểm tra log: `storage/logs/laravel.log`

### 4. Test email

Sau khi cấu hình xong, test bằng cách:
1. Đặt lịch thành công
2. Kiểm tra email inbox
3. Nếu không thấy, kiểm tra log file: `storage/logs/laravel.log`

## Debug

Nếu vẫn không gửi được email, kiểm tra:
1. File `.env` đã được cập nhật chưa
2. Chạy `php artisan config:clear` để clear cache
3. Kiểm tra log: `storage/logs/laravel.log`
4. Đảm bảo user có email trong database

