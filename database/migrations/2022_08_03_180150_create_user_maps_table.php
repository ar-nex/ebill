<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
class CreateUserMapsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_maps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id');
            $table->foreignId('user_id');
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('parent_id')->references('id')->on('users');
            $table->enum('usertype', ['admin', 'sub-admin', 'super', 'distributor', 'retailer']);
            $table->enum('parenttype', ['admin', 'sub-admin', 'super', 'distributor', 'retailer']);
            $table->index('parenttype');
            $table->index('usertype');
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
        Schema::dropIfExists('user_maps');
    }
}
