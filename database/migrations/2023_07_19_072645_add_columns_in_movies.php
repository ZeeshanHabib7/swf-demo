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
        Schema::table('movies', function (Blueprint $table) {
            $table->string('original_language')->nullable();
            $table->string('poster_path')->nullable();
            $table->string('media_type')->nullable();
            $table->float('popularity',10,2)->nullable();
            $table->float('vote_average',10,2)->nullable();
            $table->float('vote_count',10,2)->nullable();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('movies', function (Blueprint $table) {
            //
        });
    }
};
