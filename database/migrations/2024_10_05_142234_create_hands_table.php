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
        Schema::connection('other')->create('hands', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(\App\Models\Factory::class);
            $table->foreignIdFor(\App\Models\Man::class);
            $table->decimal('val',8,2);
            $table->bigInteger('user_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hands');
    }
};
