# Test Cases - PolyHair Barbershop

## 📋 Mục lục
1. [Authentication Tests](#1-authentication-tests)
2. [Product Tests](#2-product-tests)
3. [Service Tests](#3-service-tests)
4. [Appointment Tests](#4-appointment-tests)
5. [Cart Tests](#5-cart-tests)
6. [Payment Tests](#6-payment-tests)
7. [Review Tests](#7-review-tests)
8. [Admin Tests](#8-admin-tests)
9. [Employee Tests](#9-employee-tests)

---

## 1. Authentication Tests

### TC-AUTH-001: Đăng ký tài khoản thành công
| Thuộc tính | Mô tả |
|------------|-------|
| **Mô tả** | Kiểm tra đăng ký tài khoản với thông tin hợp lệ |
| **Precondition** | User chưa có tài khoản |
| **Input** | Name: "Nguyễn Văn A", Email: "test@email.com", Phone: "0123456789", Password: "Password123" |
| **Steps** | 1. Truy cập /register<br>2. Nhập thông tin<br>3. Click "Đăng ký" |
| **Expected** | Tạo tài khoản thành công, chuyển hướng đến dashboard |
| **Priority** | High |

### TC-AUTH-002: Đăng ký với email đã tồn tại
| Thuộc tính | Mô tả |
|------------|-------|
| **Mô tả** | Kiểm tra đăng ký với email đã được sử dụng |
| **Input** | Email đã tồn tại trong hệ thống |
| **Expected** | Hiển thị lỗi "Email đã được sử dụng" |
| **Priority** | High |

### TC-AUTH-003: Đăng nhập thành công
| Thuộc tính | Mô tả |
|------------|-------|
| **Mô tả** | Kiểm tra đăng nhập với thông tin hợp lệ |
| **Input** | Email: "test@email.com", Password: "Password123" |
| **Steps** | 1. Truy cập /login<br>2. Nhập email và password<br>3. Click "Đăng nhập" |
| **Expected** | Đăng nhập thành công, chuyển hướng đến dashboard |
| **Priority** | High |

### TC-AUTH-004: Đăng nhập với mật khẩu sai
| Thuộc tính | Mô tả |
|------------|-------|
| **Input** | Email đúng, Password sai |
| **Expected** | Hiển thị lỗi "Thông tin đăng nhập không chính xác" |
| **Priority** | High |

### TC-AUTH-005: Quên mật khẩu
| Thuộc tính | Mô tả |
|------------|-------|
| **Mô tả** | Kiểm tra chức năng quên mật khẩu |
| **Input** | Email đã đăng ký |
| **Expected** | Gửi email OTP thành công |
| **Priority** | Medium |

---

## 2. Product Tests

### TC-PROD-001: Xem danh sách sản phẩm
| Thuộc tính | Mô tả |
|------------|-------|
| **Mô tả** | Kiểm tra hiển thị danh sách sản phẩm |
| **Steps** | 1. Truy cập /products |
| **Expected** | Hiển thị danh sách sản phẩm với phân trang |
| **Priority** | High |

### TC-PROD-002: Tìm kiếm sản phẩm
| Thuộc tính | Mô tả |
|------------|-------|
| **Input** | Keyword: "dầu gội" |
| **Expected** | Hiển thị các sản phẩm có chứa từ khóa |
| **Priority** | Medium |

### TC-PROD-003: Xem chi tiết sản phẩm
| Thuộc tính | Mô tả |
|------------|-------|
| **Steps** | 1. Truy cập /products/{id} |
| **Expected** | Hiển thị đầy đủ thông tin: tên, giá, mô tả, hình ảnh |
| **Priority** | High |

### TC-PROD-004: Lọc sản phẩm theo danh mục
| Thuộc tính | Mô tả |
|------------|-------|
| **Input** | Chọn danh mục "Dầu gội" |
| **Expected** | Chỉ hiển thị sản phẩm thuộc danh mục đã chọn |
| **Priority** | Medium |

---

## 3. Service Tests

### TC-SVC-001: Xem danh sách dịch vụ
| Thuộc tính | Mô tả |
|------------|-------|
| **Steps** | 1. Truy cập /services |
| **Expected** | Hiển thị danh sách dịch vụ theo danh mục |
| **Priority** | High |

### TC-SVC-002: Xem chi tiết dịch vụ
| Thuộc tính | Mô tả |
|------------|-------|
| **Steps** | 1. Truy cập /services/{id} |
| **Expected** | Hiển thị: tên, mô tả, giá, thời gian, variants |
| **Priority** | High |

### TC-SVC-003: Xem dịch vụ theo danh mục
| Thuộc tính | Mô tả |
|------------|-------|
| **Input** | Chọn danh mục "Cắt tóc" |
| **Expected** | Hiển thị các dịch vụ thuộc danh mục |
| **Priority** | Medium |

---

## 4. Appointment Tests

### TC-APT-001: Đặt lịch hẹn thành công
| Thuộc tính | Mô tả |
|------------|-------|
| **Mô tả** | Kiểm tra quy trình đặt lịch đầy đủ |
| **Precondition** | Có dịch vụ và nhân viên trong hệ thống |
| **Steps** | 1. Truy cập /appointment<br>2. Chọn dịch vụ<br>3. Chọn nhân viên<br>4. Chọn ngày giờ<br>5. Điền thông tin khách<br>6. Xác nhận |
| **Expected** | Tạo lịch hẹn thành công, hiển thị trang success |
| **Priority** | Critical |

### TC-APT-002: Kiểm tra slot thời gian trống
| Thuộc tính | Mô tả |
|------------|-------|
| **Input** | Chọn nhân viên và ngày |
| **Expected** | Hiển thị các khung giờ còn trống dựa trên lịch làm việc |
| **Priority** | High |

### TC-APT-003: Không hiển thị slot đã đặt
| Thuộc tính | Mô tả |
|------------|-------|
| **Precondition** | Có lịch hẹn đã đặt vào khung giờ 10:00 |
| **Expected** | Khung giờ 10:00 không khả dụng |
| **Priority** | High |

### TC-APT-004: Hủy lịch hẹn (Customer)
| Thuộc tính | Mô tả |
|------------|-------|
| **Precondition** | User đã đăng nhập, có lịch hẹn pending |
| **Steps** | 1. Vào chi tiết lịch hẹn<br>2. Click "Hủy" |
| **Expected** | Lịch hẹn chuyển sang trạng thái cancelled |
| **Priority** | High |

### TC-APT-005: Lấy nhân viên theo dịch vụ
| Thuộc tính | Mô tả |
|------------|-------|
| **Input** | Chọn dịch vụ "Cắt tóc nam" |
| **Expected** | Hiển thị danh sách nhân viên có kỹ năng cắt tóc nam |
| **Priority** | Medium |

---

## 5. Cart Tests

### TC-CART-001: Thêm sản phẩm vào giỏ
| Thuộc tính | Mô tả |
|------------|-------|
| **Input** | Product ID, Quantity: 2 |
| **Expected** | Sản phẩm được thêm vào giỏ, badge hiển thị số lượng |
| **Priority** | High |

### TC-CART-002: Cập nhật số lượng
| Thuộc tính | Mô tả |
|------------|-------|
| **Precondition** | Giỏ hàng có sản phẩm |
| **Input** | Thay đổi quantity từ 2 thành 5 |
| **Expected** | Số lượng và tổng tiền được cập nhật |
| **Priority** | High |

### TC-CART-003: Xóa sản phẩm khỏi giỏ
| Thuộc tính | Mô tả |
|------------|-------|
| **Steps** | 1. Click nút xóa trên sản phẩm |
| **Expected** | Sản phẩm bị xóa khỏi giỏ |
| **Priority** | High |

### TC-CART-004: Xóa toàn bộ giỏ hàng
| Thuộc tính | Mô tả |
|------------|-------|
| **Steps** | 1. Click "Xóa tất cả" |
| **Expected** | Giỏ hàng trống |
| **Priority** | Medium |

---

## 6. Payment Tests

### TC-PAY-001: Thanh toán thành công
| Thuộc tính | Mô tả |
|------------|-------|
| **Precondition** | Có lịch hẹn/giỏ hàng cần thanh toán |
| **Steps** | 1. Vào trang checkout<br>2. Chọn phương thức<br>3. Xác nhận thanh toán |
| **Expected** | Thanh toán thành công, tạo invoice |
| **Priority** | Critical |

### TC-PAY-002: Áp dụng mã khuyến mãi hợp lệ
| Thuộc tính | Mô tả |
|------------|-------|
| **Precondition** | Có mã khuyến mãi còn hiệu lực |
| **Input** | Nhập mã "SUMMER20" |
| **Expected** | Áp dụng giảm giá 20%, hiển thị tổng tiền mới |
| **Priority** | High |

### TC-PAY-003: Áp dụng mã khuyến mãi hết hạn
| Thuộc tính | Mô tả |
|------------|-------|
| **Input** | Mã đã hết hạn |
| **Expected** | Hiển thị lỗi "Mã khuyến mãi đã hết hạn" |
| **Priority** | Medium |

### TC-PAY-004: Xóa mã khuyến mãi
| Thuộc tính | Mô tả |
|------------|-------|
| **Precondition** | Đã áp dụng mã khuyến mãi |
| **Steps** | 1. Click "Xóa mã" |
| **Expected** | Mã bị xóa, giá quay về ban đầu |
| **Priority** | Medium |

---

## 7. Review Tests

### TC-REV-001: Tạo đánh giá sau khi sử dụng dịch vụ
| Thuộc tính | Mô tả |
|------------|-------|
| **Precondition** | User đã hoàn thành dịch vụ |
| **Input** | Rating: 5 sao, Comment: "Dịch vụ tuyệt vời" |
| **Expected** | Đánh giá được tạo thành công |
| **Priority** | High |

### TC-REV-002: Tạo đánh giá với hình ảnh
| Thuộc tính | Mô tả |
|------------|-------|
| **Input** | Rating, Comment, Images (max 5) |
| **Expected** | Đánh giá với hình ảnh được tạo thành công |
| **Priority** | Medium |

### TC-REV-003: Sửa đánh giá
| Thuộc tính | Mô tả |
|------------|-------|
| **Precondition** | User có đánh giá đã tạo |
| **Steps** | 1. Vào /reviews/{id}/edit<br>2. Sửa nội dung<br>3. Lưu |
| **Expected** | Đánh giá được cập nhật |
| **Priority** | Medium |

### TC-REV-004: Xem danh sách đánh giá
| Thuộc tính | Mô tả |
|------------|-------|
| **Steps** | 1. Truy cập /reviews |
| **Expected** | Hiển thị đánh giá với rating, bình luận, hình ảnh |
| **Priority** | High |

---

## 8. Admin Tests

### TC-ADM-001: Truy cập Dashboard
| Thuộc tính | Mô tả |
|------------|-------|
| **Precondition** | Đăng nhập với quyền Admin |
| **Expected** | Hiển thị thống kê: doanh thu, lịch hẹn, khách hàng |
| **Priority** | High |

### TC-ADM-002: CRUD Sản phẩm
| Thuộc tính | Mô tả |
|------------|-------|
| **Create** | Thêm sản phẩm mới với tên, giá, mô tả, hình ảnh |
| **Read** | Xem danh sách và chi tiết sản phẩm |
| **Update** | Cập nhật thông tin sản phẩm |
| **Delete** | Xóa sản phẩm |
| **Priority** | High |

### TC-ADM-003: CRUD Dịch vụ
| Thuộc tính | Mô tả |
|------------|-------|
| **Create** | Thêm dịch vụ với variants |
| **Update** | Cập nhật dịch vụ |
| **Delete** | Soft delete dịch vụ |
| **Restore** | Khôi phục từ thùng rác |
| **Priority** | High |

### TC-ADM-004: Quản lý lịch hẹn
| Thuộc tính | Mô tả |
|------------|-------|
| **Mô tả** | Xem, cập nhật trạng thái, hủy lịch hẹn |
| **Priority** | High |

### TC-ADM-005: Quản lý người dùng
| Thuộc tính | Mô tả |
|------------|-------|
| **Mô tả** | CRUD users với soft delete và restore |
| **Priority** | High |

### TC-ADM-006: Quản lý nhân viên
| Thuộc tính | Mô tả |
|------------|-------|
| **Mô tả** | CRUD employees, gán kỹ năng/dịch vụ |
| **Priority** | High |

### TC-ADM-007: Quản lý khuyến mãi
| Thuộc tính | Mô tả |
|------------|-------|
| **Mô tả** | Tạo mã giảm giá với giới hạn sử dụng, thời hạn |
| **Priority** | Medium |

### TC-ADM-008: Quản lý lịch làm việc
| Thuộc tính | Mô tả |
|------------|-------|
| **Mô tả** | Tạo, sửa, xóa ca làm việc cho nhân viên |
| **Priority** | High |

### TC-ADM-009: Ẩn/Xóa đánh giá
| Thuộc tính | Mô tả |
|------------|-------|
| **Mô tả** | Admin có thể ẩn hoặc xóa đánh giá không phù hợp |
| **Priority** | Medium |

### TC-ADM-010: Xuất báo cáo thanh toán
| Thuộc tính | Mô tả |
|------------|-------|
| **Steps** | 1. Vào /admin/payments/export |
| **Expected** | Tải file Excel/CSV báo cáo |
| **Priority** | Medium |

---

## 9. Employee Tests

### TC-EMP-001: Xem lịch hẹn được phân công
| Thuộc tính | Mô tả |
|------------|-------|
| **Precondition** | Đăng nhập với quyền Employee |
| **Steps** | 1. Truy cập /employee/appointments |
| **Expected** | Hiển thị các lịch hẹn được giao cho nhân viên |
| **Priority** | High |

### TC-EMP-002: Xác nhận lịch hẹn
| Thuộc tính | Mô tả |
|------------|-------|
| **Precondition** | Có lịch hẹn pending |
| **Steps** | 1. Click "Xác nhận" |
| **Expected** | Trạng thái chuyển sang confirmed |
| **Priority** | High |

### TC-EMP-003: Bắt đầu dịch vụ
| Thuộc tính | Mô tả |
|------------|-------|
| **Precondition** | Lịch hẹn đã confirmed |
| **Steps** | 1. Click "Bắt đầu" |
| **Expected** | Trạng thái chuyển sang in_progress |
| **Priority** | High |

### TC-EMP-004: Hoàn thành dịch vụ
| Thuộc tính | Mô tả |
|------------|-------|
| **Precondition** | Lịch hẹn đang in_progress |
| **Steps** | 1. Click "Hoàn thành" |
| **Expected** | Trạng thái chuyển sang completed |
| **Priority** | High |

### TC-EMP-005: Hủy lịch hẹn
| Thuộc tính | Mô tả |
|------------|-------|
| **Input** | Lý do hủy |
| **Expected** | Lịch hẹn bị hủy, ghi log lý do |
| **Priority** | Medium |

---

## 📊 Test Summary Matrix

| Module | Total | Critical | High | Medium | Low |
|--------|-------|----------|------|--------|-----|
| Authentication | 5 | 0 | 4 | 1 | 0 |
| Product | 4 | 0 | 2 | 2 | 0 |
| Service | 3 | 0 | 2 | 1 | 0 |
| Appointment | 5 | 1 | 3 | 1 | 0 |
| Cart | 4 | 0 | 3 | 1 | 0 |
| Payment | 4 | 1 | 1 | 2 | 0 |
| Review | 4 | 0 | 2 | 2 | 0 |
| Admin | 10 | 0 | 6 | 4 | 0 |
| Employee | 5 | 0 | 4 | 1 | 0 |
| **Total** | **44** | **2** | **27** | **15** | **0** |
