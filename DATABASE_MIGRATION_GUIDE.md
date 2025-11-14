# Hướng dẫn Migration Database

## Tổng quan
Đã chuyển đổi database từ schema cũ sang schema mới dựa trên file `datn.sql`.

## Các thay đổi chính

### 1. Bảng mới đã tạo
- `roles` - Quản lý vai trò người dùng
- `employees` - Thông tin nhân viên (thay thế barbers)
- `skills` - Kỹ năng
- `employee_skills` - Kỹ năng của nhân viên
- `service_categories` - Danh mục dịch vụ
- `service_variants` - Biến thể dịch vụ (thay thế price trong services)
- `variant_attributes` - Thuộc tính của biến thể
- `combos` - Combo dịch vụ
- `combo_items` - Dịch vụ trong combo
- `working_shifts` - Ca làm việc
- `working_schedule` - Lịch làm việc
- `appointment_details` - Chi tiết lịch hẹn (thay thế app_details)
- `appointment_logs` - Log thay đổi trạng thái lịch hẹn
- `promotions` - Khuyến mãi
- `promotion_usages` - Lịch sử sử dụng khuyến mãi
- `reviews` - Đánh giá (thay thế evaluates)
- `notifications` - Thông báo
- `payments` - Thanh toán

### 2. Bảng đã cập nhật

#### `users`
- Thêm: `phone`, `avatar`, `gender`, `dob`, `status`, `role_id`
- Xóa: `email_verified_at`, `remember_token` (có thể giữ lại nếu cần)
- Thêm soft deletes

#### `appointments`
- Đổi: `id_barber` → `employee_id`
- Đổi: `id_user` → `user_id`
- Đổi: `cancel` (int) → `status` (enum)
- Thêm: `start_at`, `end_at`, `note`, `cancellation_reason`
- Xóa: `day`, `id_time`
- Thêm soft deletes

#### `services`
- Đổi: `id_type` → `category_id`
- Đổi: `images` → `image`
- Đổi: `detail` → `description`
- Xóa: `price`, `sale`, `time` (chuyển sang service_variants)
- Thêm: `status`
- Thêm soft deletes

### 3. Models đã tạo/cập nhật

#### Models mới:
- `Role`, `Employee`, `Skill`, `ServiceCategory`, `ServiceVariant`
- `VariantAttribute`, `Combo`, `ComboItem`, `WorkingShift`, `WorkingSchedule`
- `AppointmentDetail`, `AppointmentLog`, `Promotion`, `PromotionUsage`
- `Review`, `Notification`, `Payment`

#### Models đã cập nhật:
- `User` - Cập nhật relationships và fillable
- `Appointment` - Cập nhật relationships và fillable
- `Service` - Cập nhật relationships và fillable
- `Category` - Thêm soft deletes

### 4. Services đã cập nhật

#### AppointmentService
- Đổi `getByCancelStatus()` → `getByStatus()` (giữ backward compatibility)
- Đổi `id_barber` → `employee_id`
- Đổi `id_user` → `user_id`
- Sử dụng `AppointmentDetail` thay vì `AppDetail`
- Thêm logging với `AppointmentLog`

#### ServiceService
- Đổi `id_type` → `category_id`
- Đổi `images` → `image`
- Sử dụng `ServiceCategory` thay vì `Type`

### 5. Controllers đã cập nhật

#### AppointmentController
- Hỗ trợ cả `status` (mới) và `cancel` (cũ) để backward compatibility

## Các thay đổi cần làm tiếp

### 1. Controllers cần cập nhật
- `BarberController` → Đổi thành `EmployeeController`
- `ServiceController` - Cập nhật validation và logic
- `CategoryController` - Cập nhật nếu cần
- Các controllers khác sử dụng `id_type`, `id_barber`, etc.

### 2. Views cần cập nhật
- Thay đổi các trường hiển thị:
  - `barber` → `employee`
  - `cancel` → `status`
  - `id_type` → `category_id`
  - `images` → `image`
- Cập nhật form validation
- Cập nhật hiển thị enum values

### 3. Routes
- Kiểm tra và cập nhật routes nếu cần

### 4. Seeders
- Tạo seeders cho:
  - `roles`
  - `service_categories`
  - `working_shifts`
  - Sample data cho các bảng mới

## Mapping giá trị cũ → mới

### Appointment Status
```
cancel = 0 → status = 'Chờ xử lý'
cancel = 1 → status = 'Đã xác nhận'
cancel = 2 → status = 'Đã hủy'
cancel = 3 → status = 'Hoàn thành'
```

### Service
- `price` → chuyển sang `service_variants.price`
- `time` → chuyển sang `service_variants.duration`
- `id_type` → `category_id` (cần map từ types sang service_categories)

## Lưu ý quan trọng

1. **Backup database** trước khi chạy migrations
2. **Chạy migrations theo thứ tự** (đã được đánh số)
3. **Kiểm tra foreign keys** sau khi migrate
4. **Cập nhật views** để hiển thị đúng dữ liệu mới
5. **Test kỹ** các chức năng sau khi migrate

## Chạy migrations

```bash
php artisan migrate:fresh
# hoặc
php artisan migrate
```

## Rollback (nếu cần)

```bash
php artisan migrate:rollback --step=21
```

