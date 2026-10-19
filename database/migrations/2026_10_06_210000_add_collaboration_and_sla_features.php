<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_requests', function (Blueprint $table) {
            $table->unsignedTinyInteger('rating')->nullable();
            $table->text('rating_comment')->nullable();
            $table->timestamp('rated_at')->nullable();
            $table->timestamp('due_at')->nullable();
            $table->timestamp('escalated_at')->nullable();
        });

        Schema::create('request_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_id')->constrained('service_requests')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->text('body');
            $table->boolean('is_internal')->default(false);
            $table->timestamps();
        });

        $hours = ['URGENT' => 24, 'HIGH' => 72, 'MEDIUM' => 168, 'LOW' => 336];

        DB::table('service_requests')->whereNull('due_at')->orderBy('id')->each(function ($row) use ($hours) {
            DB::table('service_requests')->where('id', $row->id)->update([
                'due_at' => \Illuminate\Support\Carbon::parse($row->created_at)->addHours($hours[$row->priority] ?? 336),
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('request_comments');

        Schema::table('service_requests', function (Blueprint $table) {
            $table->dropColumn(['rating', 'rating_comment', 'rated_at', 'due_at', 'escalated_at']);
        });
    }
};
