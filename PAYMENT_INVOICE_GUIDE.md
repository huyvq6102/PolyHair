# TÀI LIỆU HƯỚNG DẪN: HỆ THỐNG THANH TOÁN & HÓA ĐƠN (POLYHAIR)

Tài liệu này mô tả chi tiết về luồng xử lý, cấu trúc dữ liệu và cách vận hành chức năng Thanh toán và Quản lý hóa đơn trong dự án PolyHair.

---

## 1. Tổng quan chức năng
Hệ thống hỗ trợ mô hình kinh doanh "Lai" (Hybrid) của Salon tóc:
1.  **Đặt lịch dịch vụ (Appointment):** Cắt tóc, gội đầu, massage...
2.  **Mua sản phẩm (Order):** Sáp vuốt tóc, dầu gội, dưỡng chất...
3.  **Hóa đơn (Invoice):** Tự động sinh ra khi khách hàng thanh toán, có thể chứa cả Dịch vụ và Sản phẩm.

---

## 2. Cấu trúc Cơ sở dữ liệu (Database)

### Bảng `payments` (Trung tâm)
Bảng này lưu trữ thông tin giao dịch tài chính.
- `id`: ID giao dịch.
- `invoice_code`: Mã hóa đơn (Duy nhất, ví dụ: `INV-20251128-XJ9K2L`).
- `user_id`: Khách hàng thực hiện.
- `appointment_id`: Liên kết đến lịch hẹn (nếu có dịch vụ).
- `order_id`: Liên kết đến đơn hàng (nếu có mua sản phẩm).
- `total`: Tổng tiền thực thu.
- `created_at`: Thời gian giao dịch.

### Mối quan hệ
- **Payment** `belongsTo` **User**
- **Payment** `belongsTo` **Appointment** (Chứa chi tiết dịch vụ cắt tóc).
- **Payment** `belongsTo` **Order** (Chứa chi tiết sản phẩm mua kèm).

---

## 3. Luồng xử lý dữ liệu (Workflow)

### Bước 1: Giỏ hàng (Client)
- **File xử lý:** `app/Http/Controllers/Site/CartController.php`
- **Logic:**
    - Khách chọn Dịch vụ -> Thêm item có `type = 'service_variant'`.
    - Khách chọn Sản phẩm -> Thêm item có `type = 'product'` (Đã cập nhật validation để chấp nhận loại này).
    - Dữ liệu lưu trong `Session::get('cart')`.

### Bước 2: Thanh toán (Client)
- **File xử lý:** `app/Http/Controllers/Site/CheckoutController.php`
- **Logic:**
    - Kiểm tra đăng nhập (`auth()->user()`).
    - Hiển thị thông tin khách hàng (Tên, SĐT, Email) lấy từ bảng `users`.
    - Gọi `PaymentService` để xử lý giao dịch.

### Bước 3: Xử lý Core (Backend)
- **File xử lý:** `app/Services/PaymentService.php`
- **Logic:**
    1.  Duyệt qua giỏ hàng.
    2.  Tách item:
        - Nếu là Dịch vụ -> Tạo `Appointment` + `AppointmentDetail`.
        - Nếu là Sản phẩm -> Tạo `Order` + `OrderDetail`.
    3.  Tính tổng tiền + VAT.
    4.  Sinh mã hóa đơn `invoice_code`.
    5.  Tạo bản ghi `Payment` liên kết với cả Appointment và Order vừa tạo.

### Bước 4: Hiển thị kết quả (Client)
- **View:** `resources/views/site/payments/success.blade.php`
- **Logic:** Hiển thị Mã hóa đơn (`invoice_code`) và lời cảm ơn.

---

## 4. Quản lý Hóa đơn (Admin)

### Xem danh sách
- **Đường dẫn:** `/admin/payments`
- **Controller:** `app/Http/Controllers/Admin/PaymentController.php`
- **Hiển thị:** Bảng danh sách gồm Mã hóa đơn, Tên khách, Tổng tiền, Ngày tạo.

### Xem chi tiết & In ấn
- **Đường dẫn:** `/admin/payments/{id}`
- **Chức năng:**
    - Hiển thị thông tin khách hàng.
    - Liệt kê chi tiết:
        - Phần Dịch vụ (lấy từ `payment->appointment->appointmentDetails`).
        - Phần Sản phẩm (lấy từ `payment->order->orderDetails`).
    - **Nút In hóa đơn:** Sử dụng CSS `@media print` để chỉ in phần nội dung hóa đơn, ẩn menu và sidebar của trang web, phù hợp in giấy A4 hoặc máy in nhiệt.

---

## 5. Các file quan trọng cần lưu ý

| Tên file | Vai trò |
| :--- | :--- |
| `app/Models/Payment.php` | Model chính, khai báo quan hệ với Order/Appointment. |
| `app/Services/PaymentService.php` | **QUAN TRỌNG NHẤT**. Chứa toàn bộ logic xử lý tạo đơn/lịch/thanh toán. |
| `app/Http/Controllers/Site/CartController.php` | Validate và thêm sản phẩm vào giỏ. |
| `resources/views/admin/payments/show.blade.php` | Giao diện hóa đơn in ấn. |
| `database/migrations/..._add_order_id...` | Migration thêm cột liên kết Order. |
| `database/migrations/..._add_invoice_code...` | Migration thêm mã hóa đơn. |

---

## 6. Xử lý sự cố thường gặp

**Lỗi:** `Call to a member function format() on null` (ở trang Admin)
- **Nguyên nhân:** Các bản ghi thanh toán cũ (trước ngày 28/11/2025) không có dữ liệu `created_at` (bị NULL).
- **Cách sửa:** Code đã được cập nhật để kiểm tra `null` trước khi format ngày tháng (`$payment->created_at ? ... : 'N/A'`).

**Lỗi:** Không tạo được Order khi thanh toán
- **Nguyên nhân:** Giỏ hàng không chứa item loại `product` hoặc Controller chặn `type='product'`.
- **Cách kiểm tra:** Đảm bảo `CartController` đã được update validation chấp nhận `product`.
