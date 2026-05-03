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
        Schema::connection('other')->create('hall_stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(\App\Models\Hall::class,'hall_id')->constrained();
            $table->foreignIdFor(\App\Models\Factory::class);
            $table->integer('stock');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hall_stocks');
    }
};
