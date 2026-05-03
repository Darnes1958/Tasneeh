<?php

use App\Models\Place;
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
        Schema::connection('other')->table('factories', function (Blueprint $table) {
            $table->foreignIdFor(Place::class);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('factories', function (Blueprint $table) {
            //
        });
    }
};
