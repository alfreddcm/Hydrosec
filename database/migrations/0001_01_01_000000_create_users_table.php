<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tbl_tower', function (Blueprint $table) {
            $table->tinyInteger('id', false, true)->autoIncrement();
            $table->string('ipAdd');
            $table->string('macAdd');
            $table->timestamps();
        });

        Schema::create('tbl_useraccounts', function (Blueprint $table) {
            $table->id(); 
            $table->unsignedTinyInteger('ID_tower');
            $table->string('name');
            $table->string('username')->unique();
            $table->string('email')->unique();
            $table->string('password');
            $table->string('status')->default('active');
            $table->timestamps();

            $table->foreign('ID_tower')->references('id')->on('tbl_tower')->onDelete('cascade');
        });

        Schema::create('tbl_workeraccounts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('username')->unique();
            $table->string('password');
            $table->unsignedBigInteger('owner_id'); // Use unsignedBigInteger
            $table->string('status')->default('active');
            $table->timestamps();

            $table->foreign('owner_id')->references('id')->on('tbl_useraccounts')->onDelete('cascade');
        });

        Schema::create('tbl_adminaccounts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('username')->unique();
            $table->string('email')->unique();
            $table->string('password');
            $table->string('status')->default('active');
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token')->nullable(); // Consider making this nullable if needed
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('tbl_adminaccounts');
        Schema::dropIfExists('tbl_workeraccounts');
        Schema::dropIfExists('tbl_useraccounts');
    }
};
