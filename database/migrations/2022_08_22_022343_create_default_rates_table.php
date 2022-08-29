<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDefaultRatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('default_rates', function (Blueprint $table) {
            $table->id();
            $table->enum('usertype', ['sub-admin', 'super', 'distributor', 'retailer']);
            $table->decimal('amount', 8, 2);
            $table->foreignId('parent_id');
            $table->foreign('parent_id')->references('id')->on('users');
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
        Schema::dropIfExists('default_rates');
    }
}
