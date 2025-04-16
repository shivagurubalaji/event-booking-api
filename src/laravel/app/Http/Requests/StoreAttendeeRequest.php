<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Models\Attendee;
use App\Traits\StandardAPIResponse;

class StoreAttendeeRequest extends FormRequest
{

    use StandardAPIResponse;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth('sanctum')->check(); 
        //return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'phone' => 'nullable|string|max:20',
            'notes' => 'nullable|string',
        ];
    }


    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $eventId = $this->route('event'); 
            $attendeeId = $this->route('attendee'); 

            $email = $this->input('email');

            if ($email && $eventId) {
                $query = Attendee::where('event_id', $eventId)
                    ->where('email', $email);

                if ($attendeeId) {
                    $query->where('id', '!=', $attendeeId);
                }

                if ($query->exists()) {
                    $validator->errors()->add('email', 'Another attendee with this email already exists for this event.');
                }
            }
        });
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException($this->errorResponse(
            'Validation failed.',
            $validator->errors()->toArray(),
            422
        ));

    }
}
