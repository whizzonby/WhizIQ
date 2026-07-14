<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('booking_settings', function (Blueprint $table) {
            if (! Schema::hasColumn('booking_settings', 'about_title')) {
                $table->string('about_title')->nullable()->after('welcome_message');
            }

            if (! Schema::hasColumn('booking_settings', 'about_text')) {
                $table->text('about_text')->nullable()->after('about_title');
            }

            if (! Schema::hasColumn('booking_settings', 'cover_image_url')) {
                $table->string('cover_image_url')->nullable()->after('logo_url');
            }

            if (! Schema::hasColumn('booking_settings', 'gallery_images')) {
                $table->json('gallery_images')->nullable()->after('cover_image_url');
            }
        });
    }

    public function down(): void
    {
        Schema::table('booking_settings', function (Blueprint $table) {
            foreach (['about_title', 'about_text', 'cover_image_url', 'gallery_images'] as $column) {
                if (Schema::hasColumn('booking_settings', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
