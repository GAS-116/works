<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRoomsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rooms', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name', 100);
            $table->string('slug')->unique();

            $table->boolean('status')
                ->default(true)
                ->comment('Determines the status of the room: 1 - allowed for use, 0 - banned');

            $table->string('password')->comment('Room password');
            $table->uuid('owner_by')->index();

            $table->timestamps();
            $table->softDeletes();
        });

        DB::statement("ALTER TABLE `rooms` COMMENT 'Stores information about rooms of user, its status, settings'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('rooms');
    }
}
