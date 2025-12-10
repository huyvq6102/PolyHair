# Logic Kiểm Tra Time Slot - Ca Làm Việc 07:00 đến 12:00

## Yêu Cầu
- Ca làm việc: 07:00 - 12:00
- **Không cho phép đặt lịch từ 12:00 trở đi**
- **Chỉ cho phép đặt các khung giờ nhỏ hơn 12:00**

## Logic Hiện Tại

### Backend Logic (PHP)

```php
// Kiểm tra slot có nằm trong ca làm việc không
foreach ($workingTimeRanges as $range) {
    // Slot phải >= start và < end (không bao gồm end time)
    $isGte = $slotTime->gte($range['start']);  // >= 07:00
    $isLt = $slotTime->lt($range['end']);      // < 12:00
    
    if ($isGte && $isLt) {
        $isInWorkingTime = true;
        break;
    }
}

// Slot chỉ available nếu:
$isAvailable = $isInWorkingTime && !$isBooked && !$isPastTime;
```

### Kết Quả

| Slot Time | >= 07:00? | < 12:00? | isInWorkingTime | Available? |
|-----------|-----------|----------|-----------------|------------|
| 07:00     | ✅ Yes    | ✅ Yes   | ✅ True         | ✅ Yes (nếu không bị booked/past) |
| 07:30     | ✅ Yes    | ✅ Yes   | ✅ True         | ✅ Yes (nếu không bị booked/past) |
| 08:00     | ✅ Yes    | ✅ Yes   | ✅ True         | ✅ Yes (nếu không bị booked/past) |
| 11:30     | ✅ Yes    | ✅ Yes   | ✅ True         | ✅ Yes (nếu không bị booked/past) |
| 12:00     | ✅ Yes    | ❌ No    | ❌ False        | ❌ No (không nằm trong ca) |
| 12:30     | ✅ Yes    | ❌ No    | ❌ False        | ❌ No (không nằm trong ca) |
| 13:00     | ✅ Yes    | ❌ No    | ❌ False        | ❌ No (không nằm trong ca) |

## Ví Dụ Dữ Liệu

### Đầu Vào (Input)

```json
{
    "employee_id": 1,
    "appointment_date": "2025-12-11",
    "total_duration": 30,
    "working_schedules": [
        {
            "shift": {
                "name": "Ca sáng",
                "start_time": "07:00",
                "end_time": "12:00"
            }
        }
    ],
    "booked_appointments": [],
    "current_time": "2025-12-11 09:00:00"
}
```

### Đầu Ra (Output)

```json
{
    "success": true,
    "time_slots": [
        {
            "time": "07:00",
            "display": "07:00",
            "word_time_id": 1,
            "available": true,
            "conflict_reason": null
        },
        {
            "time": "07:30",
            "display": "07:30",
            "word_time_id": 2,
            "available": true,
            "conflict_reason": null
        },
        {
            "time": "08:00",
            "display": "08:00",
            "word_time_id": 3,
            "available": true,
            "conflict_reason": null
        },
        {
            "time": "08:30",
            "display": "08:30",
            "word_time_id": 4,
            "available": true,
            "conflict_reason": null
        },
        {
            "time": "09:00",
            "display": "09:00",
            "word_time_id": 5,
            "available": true,
            "conflict_reason": null
        },
        {
            "time": "09:30",
            "display": "09:30",
            "word_time_id": 6,
            "available": true,
            "conflict_reason": null
        },
        {
            "time": "10:00",
            "display": "10:00",
            "word_time_id": 7,
            "available": true,
            "conflict_reason": null
        },
        {
            "time": "10:30",
            "display": "10:30",
            "word_time_id": 8,
            "available": true,
            "conflict_reason": null
        },
        {
            "time": "11:00",
            "display": "11:00",
            "word_time_id": 9,
            "available": true,
            "conflict_reason": null
        },
        {
            "time": "11:30",
            "display": "11:30",
            "word_time_id": 10,
            "available": true,
            "conflict_reason": null
        },
        {
            "time": "12:00",
            "display": "12:00",
            "word_time_id": 11,
            "available": false,
            "conflict_reason": "Không nằm trong ca làm việc"
        },
        {
            "time": "12:30",
            "display": "12:30",
            "word_time_id": 12,
            "available": false,
            "conflict_reason": "Không nằm trong ca làm việc"
        },
        {
            "time": "13:00",
            "display": "13:00",
            "word_time_id": 13,
            "available": false,
            "conflict_reason": "Không nằm trong ca làm việc"
        }
        // ... các slot khác từ 13:30 đến 22:00 đều available: false
    ]
}
```

## Test Cases

### Test Case 1: Slot 11:30 với dịch vụ 30 phút
- **Input**: Slot = 11:30, Duration = 30 phút, Shift End = 12:00
- **Calculation**: 11:30 + 30 phút = 12:00
- **Result**: ✅ **Cho phép** (12:00 = 12:00, chưa quá)

### Test Case 2: Slot 11:30 với dịch vụ 60 phút
- **Input**: Slot = 11:30, Duration = 60 phút, Shift End = 12:00
- **Calculation**: 11:30 + 60 phút = 12:30
- **Result**: ❌ **Không cho phép** (12:30 > 12:00, vượt quá ca)

### Test Case 3: Slot 12:00 với dịch vụ 30 phút
- **Input**: Slot = 12:00, Duration = 30 phút, Shift End = 12:00
- **Calculation**: 12:00 + 30 phút = 12:30
- **Result**: ❌ **Không cho phép** (Slot 12:00 không nằm trong ca làm việc)

### Test Case 4: Slot 12:30 với dịch vụ 30 phút
- **Input**: Slot = 12:30, Duration = 30 phút, Shift End = 12:00
- **Calculation**: 12:30 + 30 phút = 13:00
- **Result**: ❌ **Không cho phép** (Slot 12:30 không nằm trong ca làm việc)

## Kết Luận

✅ **Logic hiện tại đã đúng:**
- Slot 07:00 - 11:30: ✅ Có thể đặt (nếu không bị booked/past)
- Slot 12:00: ❌ Không thể đặt (không nằm trong ca)
- Slot 12:30 trở đi: ❌ Không thể đặt (không nằm trong ca)

✅ **Kiểm tra thêm:**
- Nếu slot + duration > shiftEndTime → ❌ Không cho phép
- Nếu slot + duration = shiftEndTime → ✅ Cho phép (chưa quá)




