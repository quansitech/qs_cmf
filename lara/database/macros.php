<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;

Blueprint::macro('bigIncrementsForQscmf', function ($column = 'id') {
    $id = $this->bigIncrements($column);
    if (DB::getDriverName() === 'pgsql') {
        $id->generatedAs()->always();
    }
    return $id;
});
