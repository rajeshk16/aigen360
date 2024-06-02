<?php

/**
 * @package PaytmRequest
 * @author TechVillage <support@techvill.org>
 * @contributor Md. Mostafijur Rahman <[mostafijur.techvill@gmail.com]>
 * @created 30-01-2023
 */

namespace Modules\Paytm\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PaytmRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return moduleConfig('paytm.validation')['rules'];
    }

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Attributes custom names
     *
     * @return array
     */
    public function attributes()
    {
        return moduleConfig('paytm.validation')['attributes'];
    }
}
