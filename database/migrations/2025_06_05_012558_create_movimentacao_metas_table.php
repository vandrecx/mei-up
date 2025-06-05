<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('movimentacao_metas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('meta_id')->constrained('meta_financeiras');
            $table->foreignId('transacao_id')->nullable()->constrained('transacaos');
            $table->decimal('valor', 10, 2);
            $table->enum('tipo', ['deposito', 'retirada']);
            $table->date('data_movimentacao');
            $table->text('observacoes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('movimentacao_metas');
    }
};
