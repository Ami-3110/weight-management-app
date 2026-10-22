<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class WeightLogRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'date'            => ['required', 'date'],
            'weight'          => ['required', 'numeric','between:0,999.9', 'decimal:1'],
            'calories'        => ['required', 'integer'],
            'exercise_time'   => ['required'],
            'exercise_content'=> ['nullable', 'max:120'],
        ];
    }

    public function messages(): array
    {
        return [
            'date.required'   => '日付を入力してください',
            'weight.required' => '体重を入力してください',
            'weight.numeric' => '数字で入力してください',
            'weight.max_digits' => '４桁までの数字で入力してください',
            'weight.decimal' => '小数点は1桁で入力してください',
            'calories.required'=> '摂取カロリーを入力してください',
            'calories.integer'=> '数字で入力してください',
            'exercise_time.required' => '運動時間を入力してください',
            'exercise_content.max'   => '120文字以内で入力してください',
        ];
    }

    /*public function withValidator($validator): void
    {
        $value = $this->input('weight', '');
    
        if ($value === '') {
            return; // required が拾う
        }
    
        if (!preg_match('/^\d+(\.\d+)?$/', $value)) {
            $validator->errors()->add('weight', '数字で入力してください');
            return;
        }
    
        [$integer, $decimal] = array_pad(explode('.', $value), 2, '');
    
        if (strlen($integer) > 3) {
            $validator->errors()->add('weight', '4桁までの数字で入力してください');
        }
    
        if ($decimal !== '' && strlen($decimal) > 1) {
            $validator->errors()->add('weight', '小数点は1桁で入力してください');
        }
    }*/
}