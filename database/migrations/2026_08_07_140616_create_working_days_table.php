<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Mon–Sat are working days by default, matching the company's current schedule. */
    private const DEFAULT_WORKING = [1, 2, 3, 4, 5, 6];

    public function up(): void
    {
        Schema::create('working_days', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('weekday')->unique()->comment('ISO-8601: 1 = Senin … 7 = Minggu');
            $table->boolean('is_working')->default(true);
            $table->timestamps();
        });

        $now = now();

        DB::table('working_days')->insert(
            collect(range(1, 7))
                ->map(fn (int $weekday) => [
                    'weekday' => $weekday,
                    'is_working' => in_array($weekday, self::DEFAULT_WORKING, true),
                    'created_at' => $now,
                    'updated_at' => $now,
                ])
                ->all(),
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('working_days');
    }
};
