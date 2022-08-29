<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBillRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bill_requests', function (Blueprint $table) {
            $table->id();
            $table->decimal('amount', $precision = 9, $scale = 2);
            $table->string('consumer_id', 50);
            $table->string('consumer_name')->nullable();
            $table->string('consumer_mobile')->nullable();
            $table->string('operator', 150);
            $table->string('operator_id');
            $table->string('transaction_id', 50)->nullable();
            //0 = pending, 1 = approved, 2 = rejected
            $table->tinyInteger('status')->default(0);
            $table->foreignId('user_id');
            $table->foreign('user_id')->references('id')->on('users');  
            $table->index('status');
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
        Schema::dropIfExists('bill_requests');
    }
}
