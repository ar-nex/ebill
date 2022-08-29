<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_id', 50)->unique();
            $table->string('tag', 50)->nullable();
            $table->string('pairtag', 50)->nullable();
            $table->foreignId('user_id');
            $table->enum('usertype', ['admin', 'sub-admin', 'super', 'distributor', 'retailer']);
            $table->string('code', 50)->nullable();
            $table->enum('type', ['in', 'out']);
            $table->decimal('in_amount', $precision = 9, $scale = 2)->default(0);
            $table->decimal('out_amount', $precision = 9, $scale = 2)->default(0);
            $table->string('log')->nullable();
            $table->foreign('user_id')->references('id')->on('users');
            $table->index('usertype');
            $table->index('type');
            $table->index('tag');
            $table->index('pairtag');
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
        Schema::dropIfExists('transactions');
    }
}
