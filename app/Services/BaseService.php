<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;

class BaseService
{
    /**
     * response
     *
     * @param $status
     * @param $message
     * @return array
     */
    public function response($status, $message): array
    {
        return [
            'status' => $status,
            'message' => $message,
        ];
    }

    /**
     * @param $email
     * @return Model|null
     */
}