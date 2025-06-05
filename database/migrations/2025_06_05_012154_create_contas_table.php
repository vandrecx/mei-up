<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContasTable extends Migration
{
    public function up()
    {
        Schema::create('contas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->string('nome', 100);
            $table->enum('tipo', ['conta_corrente', 'poupanca', 'cartao_credito', 'cartao_debito']);
            $table->string('banco', 50)->nullable();
            $table->string('numero_conta', 20)->nullable();
            $table->decimal('saldo_atual', 10, 2)->default(0);
            $table->decimal('limite', 10, 2)->nullable();
            $table->integer('data_fechamento')->nullable();
            $table->integer('data_vencimento')->nullable();
            $table->boolean('ativo')->default(true);
            $table->timestamps();
            $table->softDeletes();

            // Índices
            $table->index(['user_id', 'ativo']);
            $table->index('tipo');
        });
    }

    public function down()
    {
        Schema::dropIfExists('contas');
    }
};