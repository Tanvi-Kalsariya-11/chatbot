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
        Schema::table('users', function (Blueprint $table) {
            $table->string('image')->nullable()->after('email');
            $table->softDeletes();
        });

        Schema::table('threads', function (Blueprint $table) {
            $table->unsignedBigInteger('ass_id')->nullable()->after('id');
            $table->unsignedBigInteger('group_id')->nullable()->after('ass_id');
            $table->string('thread_name')->nullable()->after('group_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('image');
            $table->dropSoftDeletes();
        });

        Schema::table('threads', function (Blueprint $table) {
            $table->dropColumn('ass_id');
            $table->dropColumn('group_id');
            $table->dropColumn('thread_name');
        });
    }
};
