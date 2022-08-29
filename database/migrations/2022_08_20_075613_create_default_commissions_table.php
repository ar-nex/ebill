<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDefaultCommissionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('default_commissions', function (Blueprint $table) {
            $table->id();
            $table->enum('service_type', ['fast', 'slow']);
            $table->unsignedMediumInteger('min_range');
            $table->unsignedMediumInteger('max_range');
            $table->unsignedDecimal('percentage', 5, 2);
            $table->enum('usertype', ['admin', 'sub-admin', 'super', 'distributor', 'retailer']);
            $table->foreignId('parent_id');
            $table->foreign('parent_id')->references('id')->on('users');
            $table->timestamps();
            $table->index('service_type');
            $table->index('usertype');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('default_commissions');
    }
}
