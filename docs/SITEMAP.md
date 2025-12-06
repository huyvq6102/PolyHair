# Sitemap - PolyHair Barbershop

## 📍 Site Map Overview

```
🏠 TRANG CHỦ (/)
├──📦 SẢN PHẨM (/products)
│   ├── Danh sách | Tìm kiếm (/search) | Chi tiết (/{id})
├──💈 DỊCH VỤ (/services)
│   ├── Danh sách | Chi tiết (/{id})
├──📰 BLOG (/blog)
│   ├── Danh sách | Tìm kiếm (/search) | Chi tiết (/{id})
├──📞 LIÊN HỆ (/contact)
├──🛒 GIỎ HÀNG (/cart)
├──📅 ĐẶT LỊCH (/appointment)
│   ├── Chọn DV (/select-services) | Thành công (/success/{id}) | Chi tiết (/{id})
├──💳 THANH TOÁN (/check-out)
│   ├── Thanh toán | Thành công (/success/{id})
├──⭐ ĐÁNH GIÁ (/reviews)
│   ├── Danh sách | Tạo mới (/create) | Sửa (/{id}/edit) | Đánh giá chung (/general/create)
└──🔐 XÁC THỰC (/login, /register, /forgot-password)

👨‍💼 EMPLOYEE PORTAL (/employee)
├── Lịch hẹn (/appointments) | Tạo mới (/create) | Chi tiết (/{id})

🔧 ADMIN (/admin)
├── Dashboard (/)
├── Categories (/categories) - CRUD + Trash
├── Types (/types) - CRUD
├── Products (/products) - CRUD
├── Services (/services) - CRUD + Trash + Detail
├── Service Categories (/service-categories) - CRUD
├── Promotions (/promotions) - CRUD + Trash
├── Appointments (/appointments) - CRUD + Cancelled
├── Payments (/payments) - List + Detail + Export
├── Orders (/orders) - CRUD
├── Users (/users) - CRUD + Trash
├── Employees (/employees) - CRUD + Trash + Skills
├── News (/news) - CRUD
├── Working Schedules (/working-schedules) - CRUD + Trash
├── Reviews (/reviews) - List + Show + Edit + Hide + Delete
└── Settings (/settings) - View + Update
```

## 🔒 Phân quyền

| Vùng | Guest | Customer | Employee | Admin |
|------|:-----:|:--------:|:--------:|:-----:|
| Trang chủ, Sản phẩm, Dịch vụ, Blog, Liên hệ | ✅ | ✅ | ✅ | ✅ |
| Giỏ hàng, Đặt lịch, Thanh toán | ✅ | ✅ | ✅ | ✅ |
| Hủy lịch, Đánh giá, Profile | ❌ | ✅ | ✅ | ✅ |
| Employee Portal | ❌ | ❌ | ✅ | ✅ |
| Admin Dashboard | ❌ | ❌ | ❌ | ✅ |
