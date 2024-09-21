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
            $table->id(); // Equivalent to unsignedBigInteger and auto-incrementing
            $table->string('name');
            $table->string('towercode');
            $table->unsignedBigInteger('OwnerID'); // Use unsignedBigInteger
            $table->string('ipAdd')->nullable();
            $table->string('macAdd')->nullable();
            $table->string('status')->nullable();
            $table->string('mode')->nullable();
            $table->timestamp('startdate')->nullable();
            $table->timestamp('enddate')->nullable(); 
            $table->timestamp('last_sensor_error_email_sent_at')->nullable();
            $table->timestamp('last_critical_condition_email_sent_at')->nullable();
            $table->timestamp('last_pumping_email_sent_at')->nullable();  
            $table->timestamps();
        });

        Schema::create('tbl_useraccounts', function (Blueprint $table) {
            $table->id(); 
            $table->string('name');
            $table->string('username')->unique();
            $table->string('email')->unique();
            $table->string('password');
            $table->string('status');
            $table->timestamps();
        });

        Schema::create('tbl_workeraccounts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('username')->unique();
            $table->string('password');
            $table->unsignedBigInteger('OwnerID'); // Use unsignedBigInteger
            $table->string('status');
            $table->timestamps();

            $table->foreign('OwnerID')->references('id')->on('tbl_useraccounts')->onDelete('cascade');
        });

        Schema::create('tbl_adminaccounts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('username')->unique();
            $table->string('email')->unique();
            $table->string('password');
            $table->string('status')->default('1');
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token')->nullable(); 
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
        Schema::dropIfExists('tbl_tower'); // Make sure to drop this last if needed
    }
};
