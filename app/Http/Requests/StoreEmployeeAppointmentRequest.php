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
            'service_variants' => 'nullable|array',
            'service_variants.*' => 'exists:service_variants,id',
            'simple_services' => 'nullable|array',
            'simple_services.*' => 'exists:services,id',
            'appointment_date' => 'required|date|after_or_equal:today',
            'appointment_time' => 'required|string',
            'note' => 'nullable|string|max:1000',
            'promotion_code' => 'nullable|string|exists:promotions,code',
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
            'service_variants.*.exists' => 'Biến thể dịch vụ không hợp lệ.',
            'simple_services.*.exists' => 'Dịch vụ không hợp lệ.',
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

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $variants = $this->input('service_variants', []);
            $simpleServices = $this->input('simple_services', []);

            $hasVariants = is_array($variants) && count(array_filter($variants)) > 0;
            $hasSimple = is_array($simpleServices) && count(array_filter($simpleServices)) > 0;

            if (!$hasVariants && !$hasSimple) {
                $validator->errors()->add('service_variants', 'Vui lòng chọn ít nhất một dịch vụ.');
            }
        });
    }
}
