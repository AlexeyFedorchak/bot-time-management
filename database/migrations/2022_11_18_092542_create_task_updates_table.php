<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('task_updates', function (Blueprint $table) {
            $table->id();
            $table->string('status')->default(\App\Models\TaskUpdate::STATUS_OPEN);
            $table->string('executor_id');
            $table->unsignedBigInteger('task_id');
            $table->timestamps();

            $table->foreign('task_id')
                ->references('id')
                ->on('tasks');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('task_updates');
    }
};
