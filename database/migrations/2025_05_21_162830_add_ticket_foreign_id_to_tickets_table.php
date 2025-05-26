<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
{
    Schema::table('tickets', function (Blueprint $table) {
        $table->foreignId('ticket_foreign_id')->nullable(); // Make sure it's nullable if it's not always required
    });
}

public function down()
{
    Schema::table('tickets', function (Blueprint $table) {
        $table->dropColumn('ticket_foreign_id');
    });
}

};
