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
            $table->tinyInteger('id', false, true)->autoIncrement();
            $table->string('pH');
            $table->string('temperature');
            $table->string('nutrientlevel');
            $table->string('status');
            $table->timestamps();
        });

        Schema::create('tbl_tower', function (Blueprint $table) {
            $table->tinyInteger('id', false, true)->autoIncrement();
            $table->tinyInteger('ID_account', false, true);
            $table->tinyInteger('ID_worker', false, true);
            $table->tinyInteger('ID_sensor', false, true);
            $table->string('API_key');
            $table->string('Mac_add');
            $table->timestamps();

            $table->foreign('ID_sensor')->references('id')->on('sensor')->onDelete('cascade');
        });

        Schema::create('tbl_towerlogs', function (Blueprint $table) {
            $table->tinyInteger('id', false, true)->autoIncrement();
            $table->tinyInteger('ID_tower', false, true);
            $table->string('activity');
            $table->timestamps();

            $table->foreign('ID_tower')->references('id')->on('tbl_tower')->onDelete('cascade');
        });

        Schema::create('tbl_alert', function (Blueprint $table) {
            $table->tinyInteger('id', false, true)->autoIncrement();
            $table->tinyInteger('ID_tower', false, true);
            $table->string('message');
            $table->timestamps();

            $table->foreign('ID_tower')->references('id')->on('tbl_tower')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_alert');
        Schema::dropIfExists('tbl_cultivation');
        Schema::dropIfExists('tbl_nutrientsolution');
        Schema::dropIfExists('tbl_towerlogs');
        Schema::dropIfExists('tbl_tower');
        Schema::dropIfExists('sensor');
    }
};
