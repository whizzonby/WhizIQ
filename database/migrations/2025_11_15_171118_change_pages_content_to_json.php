<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, add a temporary column
        Schema::table('pages', function (Blueprint $table) {
            $table->json('content_temp')->nullable()->after('content');
        });

        // Convert existing HTML content to JSON format for code editor mode
        $pages = DB::table('pages')->get();
        foreach ($pages as $page) {
            $contentData = $page->content;
            // Store as-is (string) - will be wrapped as JSON
            DB::table('pages')
                ->where('id', $page->id)
                ->update(['content_temp' => json_encode($contentData)]);
        }

        // Drop old column and rename temp
        Schema::table('pages', function (Blueprint $table) {
            $table->dropColumn('content');
        });

        Schema::table('pages', function (Blueprint $table) {
            $table->renameColumn('content_temp', 'content');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Add temp text column
        Schema::table('pages', function (Blueprint $table) {
            $table->longText('content_temp')->nullable()->after('content');
        });

        // Convert JSON back to text
        $pages = DB::table('pages')->get();
        foreach ($pages as $page) {
            $contentData = json_decode($page->content, true);
            DB::table('pages')
                ->where('id', $page->id)
                ->update(['content_temp' => $contentData ?? '']);
        }

        // Drop JSON column and rename temp
        Schema::table('pages', function (Blueprint $table) {
            $table->dropColumn('content');
        });

        Schema::table('pages', function (Blueprint $table) {
            $table->renameColumn('content_temp', 'content');
        });
    }
};
