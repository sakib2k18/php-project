<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * "Skills you can bring" and "Why do you want to volunteer?" became optional,
 * and "What would you like to do?" became a multi-select.
 *
 * `preferred_activity` widens to `text` holding a JSON array rather than using
 * `$table->json()`: on MariaDB 10.4 `json` is `longtext` plus a
 * `CHECK (JSON_VALID(col))` constraint, and the rows already in the table hold
 * bare strings like 'Fundraising', so the ALTER would fail that check.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Widen/open the columns first so the data conversion below has
        // somewhere to write a multi-value JSON array.
        Schema::table('volunteers', function (Blueprint $table) {
            $table->string('skills', 500)->nullable()->change();
            $table->text('preferred_activity')->change();
            $table->text('motivation')->nullable()->change();
        });

        // Existing rows hold a single activity as a plain string; the model now
        // casts this column to an array, so a bare string would decode to null.
        DB::table('volunteers')
            ->select('id', 'preferred_activity')
            ->orderBy('id')
            ->get()
            ->each(function (object $row): void {
                $value = $row->preferred_activity;

                if (blank($value) || is_array(json_decode((string) $value, true))) {
                    return; // empty, or already converted by a previous run
                }

                DB::table('volunteers')->where('id', $row->id)->update([
                    'preferred_activity' => json_encode([$value]),
                ]);
            });
    }

    /**
     * Lossy: reverting to `string(60)` truncates (and so invalidates) any row
     * that was given more than one activity. The wrapping is not undone —
     * splitting on a comma would be ambiguous.
     */
    public function down(): void
    {
        Schema::table('volunteers', function (Blueprint $table) {
            $table->string('skills', 500)->change();
            $table->string('preferred_activity', 60)->change();
            $table->text('motivation')->change();
        });
    }
};
