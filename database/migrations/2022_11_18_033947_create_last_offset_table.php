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
        Schema::create('last_offset', function (Blueprint $table) {
            $table->id();
            $table->string('offset')->default(1);
            $table->timestamps();
        });

        // add record
        \App\Models\LastOffset::create([
            'offset' => 1,
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('last_offset');
    }
};
