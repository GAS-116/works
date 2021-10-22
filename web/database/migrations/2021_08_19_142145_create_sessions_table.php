<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSessionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $table_name = 'sessions';

        Schema::create($table_name, function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('user_id')
                ->constrained()
                ->onUpdate('restrict')
                ->onDelete('restrict');

            $table->foreignUuid('room_id')
                ->constrained()
                ->onUpdate('restrict')
                ->onDelete('restrict');

            $table->boolean('active')
                ->default(true)
                ->comment('user in the room or in the waiting room');

            $table->dateTimeTz('start_time');

            $table->dateTimeTz('finish_time')
                ->nullable();

            $table->integer('time')
                ->nullable()
                ->comment('time in seconds');

            $table->timestamps();
        });

        DB::statement("ALTER TABLE {$table_name} COMMENT 'This contains a list of users who are currently in a room.'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sessions');
    }
}
