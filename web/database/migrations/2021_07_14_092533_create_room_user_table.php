<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRoomUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('room_user', function (Blueprint $table) {
            $table->foreignUuid('user_id')
                ->constrained()
                ->onUpdate('restrict')
                ->onDelete('restrict');

            $table->foreignUuid('room_id')
                ->constrained()
                ->onUpdate('restrict')
                ->onDelete('restrict');

            $table->string('invite_code')
                ->nullable()
                ->comment('Saves information about the code generated to invite other users to the conference.');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('room_user');
    }
}
