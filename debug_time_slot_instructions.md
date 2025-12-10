# Hướng Dẫn Debug Time Slot - Kiểm Tra total_duration

## Bước 1: Mở Chrome DevTools
1. Nhấn `F12` hoặc `Ctrl + Shift + I` để mở DevTools
2. Chọn tab **Network**

## Bước 2: Reload Page và Thực Hiện Thao Tác
1. Nhấn nút **"Reload page"** trong Network tab (hoặc `Ctrl + R`)
2. Chọn dịch vụ 60 phút (hoặc > 60 phút)
3. Chọn nhân viên có ca làm việc 07:00-12:00
4. Chọn ngày
5. Quan sát các request trong Network tab

## Bước 3: Tìm Request `available-time-slots`
1. Trong Network tab, tìm request có tên chứa `available-time-slots`
2. Click vào request đó
3. Chọn tab **Payload** hoặc **Query String Parameters**

## Bước 4: Kiểm Tra Tham Số
Kiểm tra xem request có gửi các tham số sau không:
- `employee_id`: ID của nhân viên
- `appointment_date`: Ngày đặt lịch (format: YYYY-MM-DD)
- `total_duration`: **QUAN TRỌNG** - Tổng thời gian dịch vụ (phút)

## Bước 5: Kiểm Tra Response
1. Chọn tab **Response** hoặc **Preview**
2. Tìm slot `11:30` trong response
3. Kiểm tra:
   - `available`: phải là `false`
   - `conflict_reason`: phải có giá trị "Đã quá ca làm việc của nhân viên cho dịch vụ trên 60p"

## Ví Dụ Request Đúng:

### Query String Parameters:
```
employee_id: 1
appointment_date: 2025-12-12
total_duration: 60
```

### Response cho slot 11:30:
```json
{
  "time": "11:30",
  "available": false,
  "conflict_reason": "Đã quá ca làm việc của nhân viên cho dịch vụ trên 60p"
}
```

## Nếu `total_duration` = 0 hoặc không có:

### Vấn đề có thể là:
1. `#total_duration_minutes` input không có giá trị
2. JavaScript không lấy được giá trị từ input
3. Dịch vụ chưa được chọn hoặc duration = 0

### Cách sửa:
1. Kiểm tra trong tab **Elements** xem có input `id="total_duration_minutes"` không
2. Kiểm tra giá trị của input đó
3. Nếu input không có hoặc giá trị = 0, cần kiểm tra lại logic tính `$totalDuration` trong backend

## Kiểm Tra Console Logs

1. Chọn tab **Console** trong DevTools
2. Tìm các log liên quan đến:
   - `total_duration`
   - `11:30`
   - `available-time-slots`

## Kiểm Tra Server Logs

Kiểm tra file `storage/logs/laravel.log` để xem:
- `total_duration` có giá trị gì
- Logic kiểm tra có chạy không
- `is_available` có được set đúng không

### Lệnh xem log (PowerShell):
```powershell
Get-Content storage/logs/laravel.log -Tail 100 | Select-String -Pattern "11:30|total_duration|DEBUG"
```




