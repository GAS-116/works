<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHistoryMeetingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $table_name = 'history_meetings';

        Schema::create($table_name, function (Blueprint $table) {
            $table->uuid('id')
                ->primary();

            $table->foreignUuid('room_id')
                ->constrained()
                ->onDelete('restrict')->onUpdate('restrict');

            $table->dateTimeTz('start_time');

            $table->dateTimeTz('finish_time')
                ->nullable();

            $table->integer('total_time')
                ->nullable()
                ->comment('Meeting time');

            $table->softDeletes();
        });

        DB::statement("ALTER TABLE {$table_name} COMMENT 'History of one meeting'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('history_meetings');
    }
}
