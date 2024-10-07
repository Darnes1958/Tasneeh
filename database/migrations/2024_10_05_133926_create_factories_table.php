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
        Schema::connection('other')->create('factories', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(\App\Models\Product::class);
            $table->date('process_date');
            $table->date('ready_date')->nullable();
            $table->string('status')->default('manufacturing');
            $table->decimal('cost', 8, 2)->default(0);
            $table->decimal('price', 8, 2)->default(0);
            $table->integer('quantity')->default(1);
            $table->decimal('handwork',8,2)->default(0);
            $table->bigInteger('user_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('factories');
    }
};
