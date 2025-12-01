<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEmployeeAppointmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $customerType = $this->input('customer_type', 'existing');
        
        // Common validation rules
        $commonRules = [
            'service_variants' => 'required|array|min:1',
            'service_variants.*' => 'exists:service_variants,id',
            'appointment_date' => 'required|date|after_or_equal:today',
            'appointment_time' => 'required|string',
            'note' => 'nullable|string|max:1000',
        ];
        
        if ($customerType === 'existing') {
            return array_merge([
                'customer_type' => 'required|in:existing,new',
                'user_id' => 'required|exists:users,id',
            ], $commonRules);
        } else {
            return array_merge([
                'customer_type' => 'required|in:existing,new',
                'new_customer_name' => 'required|string|max:255',
                'new_customer_phone' => 'required|string|max:20',
                'new_customer_email' => 'nullable|email|max:255',
            ], $commonRules);
        }
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'service_variants.required' => 'Vui lòng chọn ít nhất một dịch vụ.',
            'service_variants.min' => 'Vui lòng chọn ít nhất một dịch vụ.',
            'appointment_date.required' => 'Vui lòng chọn ngày đặt lịch.',
            'appointment_date.after_or_equal' => 'Ngày đặt lịch phải từ hôm nay trở đi.',
            'appointment_time.required' => 'Vui lòng chọn giờ đặt lịch.',
            'user_id.required' => 'Vui lòng chọn khách hàng.',
            'user_id.exists' => 'Khách hàng không tồn tại.',
            'new_customer_name.required' => 'Vui lòng nhập tên khách hàng.',
            'new_customer_phone.required' => 'Vui lòng nhập số điện thoại.',
            'new_customer_email.email' => 'Email không hợp lệ.',
        ];
    }
}
