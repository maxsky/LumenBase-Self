<?php

namespace App\Common\Utils;

use Exception;
use Kra8\Snowflake\Snowflake as SF;

class Snowflake {

    /**
     * @return string
     */
    public static function generate(): string {
        $snowflake = app(SF::class);

        do {
            try {
                $no = $snowflake->next();
            } catch (Exception $e) {
                usleep(5);
                $no = null;
            }
        } while (!$no);

        return $no;
    }
}
