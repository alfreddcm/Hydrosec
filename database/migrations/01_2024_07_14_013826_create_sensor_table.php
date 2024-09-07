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
        Schema::create('sensor', function (Blueprint $table) {
            $table-id(); 
            $table->unsignedBigInteger('towerid');
            $table->string('pH');
            $table->string('temperature');
            $table->string('nutrientlevel');
            $table->string('light');
            $table->string('status')->nullable();
            $table->text('iv'); 
            $table->text('k'); 
            $table->timestamps();

            $table->foreign('towerid')->references('id')->on('tbl_tower')->onDelete('cascade');
        });

        // Create the tbl_towerlogs table
        Schema::create('tbl_towerlogs', function (Blueprint $table) {
            $table->id(); 
            $table->unsignedBigInteger('ID_tower');
            $table->text('activity');
            $table->timestamps();

            $table->foreign('ID_tower')->references('id')->on('tbl_tower')->onDelete('cascade');
        });

        // Create the tbl_alert table
        Schema::create('tbl_alert', function (Blueprint $table) {
            $table->id(); // auto-incrementing integer
            $table->unsignedBigInteger('ID_tower');
            $table->text('message');
            $table->timestamps();

            $table->foreign('ID_tower')->references('id')->on('tbl_tower')->onDelete('cascade');
        });

        Schema::create('pump', function (Blueprint $table) {
            $table->id(); // auto-incrementing integer
            $table->unsignedBigInteger('towerid');
            $table->string('status');
            $table->timestamps();

            $table->foreign('towerid')->references('id')->on('tbl_tower')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sensor');
        Schema::dropIfExists('tbl_towerlogs');
        Schema::dropIfExists('tbl_alert');
        Schema::dropIfExists('pump');
        Schema::dropIfExists('tbl_tower');
    }
};
