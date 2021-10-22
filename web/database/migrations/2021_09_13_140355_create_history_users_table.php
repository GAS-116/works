<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHistoryUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $table_name = 'history_users';

            Schema::create($table_name, function (Blueprint $table) {
            $table->uuid('id')
                ->primary();

            $table->foreignUuid('room_id')
                ->constrained()
                ->onDelete('restrict')->onUpdate('restrict');

            $table->foreignUuid('user_id')
                ->constrained()
                ->onDelete('restrict')
                ->onUpdate('restrict');

            $table->integer('time')
                ->nullable()
                ->comment('Meeting time');

            $table->timestamps();
        });

        DB::statement("ALTER TABLE {$table_name} COMMENT 'The history of the meeting in which the user spent time'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('history_user');
    }
}
