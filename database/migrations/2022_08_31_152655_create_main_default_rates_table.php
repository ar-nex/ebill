<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMainDefaultRatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('main_default_rates', function (Blueprint $table) {
            $table->id();
            $table->enum('byUsertype', ['sub-admin', 'super', 'distributor', 'retailer']);
            $table->enum('forUsertype', ['sub-admin', 'super', 'distributor', 'retailer']);
            $table->decimal('amount', 8, 2);
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
        Schema::dropIfExists('main_default_rates');
    }
}
