<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChatbotLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('chatbot_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('user_id');
            $table->string('device_name')->nullable();
            $table->text('user_message');
            $table->text('bot_response');
            $table->string('tool_called')->nullable();
            $table->integer('api_tokens_used')->default(0);
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index(['user_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('chatbot_logs');
    }
}
