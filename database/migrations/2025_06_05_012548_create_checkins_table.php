<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('checkins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->constrained('users');
            $table->date('data_checkin');
            $table->enum('humor_financeiro', ['otimo', 'bom', 'neutro', 'ruim', 'pessimo']);
            $table->text('observacoes')->nullable();
            $table->text('objetivos_alcancados')->nullable();
            $table->text('dificuldades')->nullable();
            $table->text('proximos_passos')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('checkins');
    }
};
