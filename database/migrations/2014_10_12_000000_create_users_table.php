<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('shopname', 150);
            $table->string('mobile')->unique();
            $table->enum('usertype', ['admin', 'sub-admin', 'super', 'distributor', 'retailer']);
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('pan', 100)->nullable();
            $table->string('password');
            $table->string('pp', 50)->nullable();
            $table->string('state', 100);
            $table->string('district', 150);
            $table->string('postoffice', 150)->nullable();
            $table->integer('pin');
            $table->boolean('is_active')->default(1);
            $table->tinyInteger('registration_approved')->default(1);  // 0 = pending, 1 = approved, 2 = rejected   
            $table->rememberToken();
            $table->index('usertype');
            $table->index('is_active');
            $table->index('registration_approved');
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
        Schema::dropIfExists('users');
    }
}
