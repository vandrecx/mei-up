<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('meta_financeiras', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->constrained('usuarios');
            $table->string('titulo', 100);
            $table->text('descricao')->nullable();
            $table->decimal('valor_objetivo', 10, 2);
            $table->decimal('valor_atual', 10, 2)->default(0);
            $table->date('data_inicio');
            $table->date('data_objetivo');
            $table->enum('categoria', ['emergencia', 'investimento', 'compra', 'viagem', 'outros']);
            $table->enum('status', ['ativo', 'pausado', 'concluido', 'cancelado'])->default('ativo');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('meta_financeiras');
    }
};
