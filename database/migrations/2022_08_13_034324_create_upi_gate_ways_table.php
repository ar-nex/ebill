<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUpiGateWaysTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('upi_gate_ways', function (Blueprint $table) {
            $table->id();
            $table->string('client_txn_id', 80)->unique();
            $table->decimal('amount', 9, 2);
            $table->string('product_info', 80)->nullable();
            $table->string('udf1', 80)->nullable();
            $table->string('udf2', 80)->nullable();
            $table->string('udf3', 80)->nullable();
            $table->boolean('status')->default(false);
            $table->string('resp_msg')->nullable();
            $table->foreignId('user_id');
            $table->foreign('user_id')->references('id')->on('users');  
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
        Schema::dropIfExists('upi_gate_ways');
    }
}
