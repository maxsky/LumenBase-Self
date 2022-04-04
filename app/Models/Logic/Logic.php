<?php

/**
 * Created by IntelliJ IDEA.
 * User: Max Sky
 * Date: 6/11/2020
 * Time: 5:51 PM
 */

namespace App\Models\Logic;

use DB;

abstract class Logic {

    /**
     * @param string $query
     * @param array  $bindings
     *
     * @return array
     */
    public function select(string $query, array $bindings = []): array {
        return DB::select($query, $bindings);
    }

    /**
     * @param string $query
     * @param array  $bindings
     *
     * @return object|null
     */
    public function selectOne(string $query, array $bindings = []): ?object {
        return DB::selectOne($query, $bindings);
    }

    /**
     * @param string $query
     * @param array  $bindings
     *
     * @return bool
     */
    public function statement(string $query, array $bindings = []): bool {
        return DB::statement($query, $bindings);
    }

    /**
     * @param string $query
     * @param array  $bindings
     *
     * @return int
     */
    public function affectingStatement(string $query, array $bindings = []): int {
        return DB::affectingStatement($query, $bindings);
    }
}
