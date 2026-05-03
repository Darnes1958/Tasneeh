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
        Schema::connection('other')->create('hall_trans', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(\App\Models\Hall::class,'hall_id1')->constrained();
            $table->foreignIdFor(\App\Models\Hall::class,'hall_id2')->constrained();
            $table->foreignIdFor(\App\Models\Factory::class);
            $table->date('tran_date');
            $table->integer('quant');
            $table->bigInteger('user_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hall_trans');
    }
};
