# Cách Kiểm Tra Request available-time-slots

## Bước 1: Click vào request `available-time-slots`
Trong Network tab, tìm và click vào request có tên:
- `available-time-slots?employee_id=21...`

## Bước 2: Kiểm Tra Query String Parameters
1. Chọn tab **Payload** hoặc **Query String Parameters**
2. Kiểm tra các tham số:
   - `employee_id`: phải có giá trị (ví dụ: 21)
   - `appointment_date`: phải có giá trị (ví dụ: 2025-12-12)
   - `total_duration`: **QUAN TRỌNG** - phải có giá trị > 0 (ví dụ: 60, 120)

## Bước 3: Kiểm Tra Response
1. Chọn tab **Response** hoặc **Preview**
2. Tìm slot `11:30` trong response
3. Kiểm tra:
   ```json
   {
     "time": "11:30",
     "available": false,  // Phải là false
     "conflict_reason": "Đã quá ca làm việc của nhân viên cho dịch vụ trên 60p"
   }
   ```

## Nếu `total_duration` = 0 hoặc không có:

### Vấn đề:
- Input `#total_duration_minutes` không có giá trị
- Hoặc JavaScript không lấy được giá trị

### Cách sửa tạm thời:
Thêm log để debug trong JavaScript:
```javascript
console.log('Total duration:', totalDuration);
console.log('Total duration input value:', $('#total_duration_minutes').val());
```




