<?php

namespace App\Models\Entities;

use Illuminate\Database\Eloquent\Model as BaseModel;

class Model extends BaseModel {

    public function __construct(array $attributes = []) {
        parent::__construct($attributes);

        $this->timestamps = false;
    }

    /**
     * @param mixed     $column
     * @param float|int $amount
     * @param array     $extra
     *
     * @return int
     */
    public function increment($column, $amount = 1, array $extra = []): int {
        return parent::increment($column, $amount, $extra);
    }

    /**
     * @param mixed     $column
     * @param float|int $amount
     * @param array     $extra
     *
     * @return int
     */
    public function decrement($column, $amount = 1, array $extra = []): int {
        return parent::decrement($column, $amount, $extra);
    }
}
