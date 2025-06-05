<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('transacaos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->constrained('usuarios');
            $table->foreignId('conta_id')->nullable()->constrained('contas');
            $table->string('descricao', 200);
            $table->decimal('valor', 10, 2);
            $table->enum('tipo', ['entrada', 'saida']);
            $table->enum('categoria', ['alimentacao', 'transporte', 'saude', 'lazer', 'trabalho', 'outros']);
            $table->enum('forma_pagamento', ['dinheiro', 'pix', 'debito', 'credito']);
            $table->date('data_transacao');
            $table->text('observacoes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('transacaos');
    }
};
