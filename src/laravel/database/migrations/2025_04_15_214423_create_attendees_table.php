<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('attendees', function (Blueprint $table) {

            $table->uuid('id')->primary()->default(Str::orderedUuid());
            $table->uuid('event_id');
            $table->uuid('user_id')->nullable(); // user who helped register
            $table->string('name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->boolean('checked_in')->default(false);
            $table->timestamp('registered_at')->useCurrent();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('event_id')->references('id')->on('events')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            $table->unique(['event_id', 'email']); // prevent duplicate registration for same event
            $table->index(['event_id', 'user_id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendees');
    }
};
