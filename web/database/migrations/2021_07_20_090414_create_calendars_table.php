<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCalendarsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('calendars', function (Blueprint $table) {
            $table->uuid('id')
                ->primary();

            $table->string('title');

            $table->text('note')
                ->nullable();

            $table->string('password')
                ->nullable();

            $table->text('participants')
                ->toJson();

            $table->boolean('is_lobby')
                ->default(false);

            $table->text('live_stream')
                ->nullable()
                ->toJson();

            $table->date('start_date');

            $table->date('end_date')
                ->nullable();

            //$table->uuid('user_id');
            $table->foreignUuid('user_id')->
                constrained()->
                onDelete('restrict')->
                onUpdate('restrict');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('calendars');
    }
}
