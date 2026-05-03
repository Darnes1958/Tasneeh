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
        Schema::connection('other')->create('trans', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(\App\Models\Factory::class);
            $table->foreignIdFor(\App\Models\Item::class);
            $table->decimal('quant',8,2);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trans');
    }
};
