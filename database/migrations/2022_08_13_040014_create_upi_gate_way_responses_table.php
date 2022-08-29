<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUpiGateWayResponsesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('upi_gate_way_responses', function (Blueprint $table) {
            $table->id();
            $table->string('order_id', 80);
            $table->string('payment_url');
            $table->string('bhim_link')->nullable();
            $table->string('phonepe_link', 80)->nullable();
            $table->string('paytm_link', 80)->nullable();
            $table->string('gpay_link', 80)->nullable();
            $table->foreignId('upi_gateway_id');
            $table->foreign('upi_gateway_id')->references('id')->on('upi_gate_ways');
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
        Schema::dropIfExists('upi_gate_way_responses');
    }
}
