<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('blog_posts', function (Blueprint $table) {
            $table->index('title');
            $table->index('tags');
            $table->index('published_at');
        });
        DB::statement('CREATE INDEX blog_posts_body_index ON blog_posts (body(191))');
        Schema::table('products', function (Blueprint $table) {
            $table->index('name');
            $table->index('category');
        });
        DB::statement('CREATE INDEX products_description_index ON products (description(191))');
        Schema::table('pages', function (Blueprint $table) {
            $table->index('title');
        });
        DB::statement('CREATE INDEX pages_content_index ON pages (content(191))');
        Schema::table('faqs', function (Blueprint $table) {
            $table->index('question');
        });
        DB::statement('CREATE INDEX faqs_answer_index ON faqs (answer(191))');
    }

    public function down(): void
    {
        Schema::table('blog_posts', function (Blueprint $table) {
            $table->dropIndex(['title']);
            $table->dropIndex(['tags']);
            $table->dropIndex(['published_at']);
        });
        DB::statement('DROP INDEX blog_posts_body_index ON blog_posts');
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['name']);
            $table->dropIndex(['category']);
        });
        DB::statement('DROP INDEX products_description_index ON products');
        Schema::table('pages', function (Blueprint $table) {
            $table->dropIndex(['title']);
        });
        DB::statement('DROP INDEX pages_content_index ON pages');
        Schema::table('faqs', function (Blueprint $table) {
            $table->dropIndex(['question']);
        });
        DB::statement('DROP INDEX faqs_answer_index ON faqs');
    }
};
