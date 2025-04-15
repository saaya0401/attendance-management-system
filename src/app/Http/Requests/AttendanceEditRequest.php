<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AttendanceEditRequest extends FormRequest
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
        return [
            'date'=>['required'],
            'clock_in'=>['required', 'date_format:H:i'],
            'clock_out'=>['required', 'date_format:H:i',  'after:clock_in'],
            'break_in.*'=>['nullable', 'date_format:H:i'],
            'break_out.*'=>['nullable', 'date_format:H:i'],
            'comment'=>['required', 'string', 'max:100'],
        ];
    }

    public function messages()
    {
        return [
            'clock_in.required'=>'出勤時間を入力してください',
            'clock_out.required'=>'退勤時間を入力してください',
            'clock_out.after'=>'出勤時間もしくは退勤時間が不適切な値です',
            'comment.required'=>'備考を記入してください',
            'comment.max'=>'備考は100文字以下で入力してください'
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator){
            $clockIn=$this->input('clock_in');
            $clockOut=$this->input('clock_out');

            $breakIns=$this->input('break_in', []);
            $breakOuts=$this->input('break_out', []);

            foreach($breakIns as $i=>$start){
                $end=$breakOuts[$i] ?? null;

                if($start && !$end){
                    $validator->errors()->add("break_out.$i", "休憩終了時間を入力してください");
                }
                if(!$start && $end){
                    $validator->errors()->add("break_in.$i", "休憩開始時間を入力してください");
                }
                if($start && $end){
                    if($start >= $end ){
                        $validator->errors()->add("break_in.$i", "休憩開始時間は終了時間より前にしてください");
                    }
                    if($start < $clockIn || $end > $clockOut){
                        $validator->errors()->add("break_in.$i", "休憩時間が勤務時間外です");
                    }
                }
            }
        });
    }
}
