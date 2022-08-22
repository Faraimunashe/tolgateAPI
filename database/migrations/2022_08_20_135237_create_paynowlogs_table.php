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
        Schema::create('paynowlogs', function (Blueprint $table) {
            $table->id();
            $table->string('reference');
            $table->string('paynow_reference');
            $table->string('status');
            $table->decimal('amount');
            $table->string('poll_url');
            $table->string('hash');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('paynowlogs');
    }
};
