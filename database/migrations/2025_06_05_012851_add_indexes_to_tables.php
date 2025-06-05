<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('fonte_rendas', function (Blueprint $table) {
            $table->index(['usuario_id', 'ativo']);
            $table->index('data_recebimento');
        });

        Schema::table('gasto_fixos', function (Blueprint $table) {
            $table->index(['usuario_id', 'ativo']);
            $table->index('dia_vencimento');
        });

        Schema::table('parcelamentos', function (Blueprint $table) {
            $table->index(['usuario_id', 'ativo']);
            $table->index('dia_vencimento');
        });

        Schema::table('transacaos', function (Blueprint $table) {
            $table->index(['usuario_id', 'data_transacao']);
            $table->index(['usuario_id', 'tipo']);
            $table->index('data_transacao');
        });

        Schema::table('meta_financeiras', function (Blueprint $table) {
            $table->index(['usuario_id', 'status']);
            $table->index('data_objetivo');
        });

        Schema::table('checkins', function (Blueprint $table) {
            $table->index(['usuario_id', 'data_checkin']);
        });
    }

    public function down()
    {
        Schema::table('fonte_rendas', function (Blueprint $table) {
            $table->dropIndex(['usuario_id', 'ativo']);
            $table->dropIndex(['data_recebimento']);
        });

        Schema::table('gasto_fixos', function (Blueprint $table) {
            $table->dropIndex(['usuario_id', 'ativo']);
            $table->dropIndex(['dia_vencimento']);
        });

        Schema::table('parcelamentos', function (Blueprint $table) {
            $table->dropIndex(['usuario_id', 'ativo']);
            $table->dropIndex(['dia_vencimento']);
        });

        Schema::table('transacaos', function (Blueprint $table) {
            $table->dropIndex(['usuario_id', 'data_transacao']);
            $table->dropIndex(['usuario_id', 'tipo']);
            $table->dropIndex(['data_transacao']);
        });

        Schema::table('meta_financeiras', function (Blueprint $table) {
            $table->dropIndex(['usuario_id', 'status']);
            $table->dropIndex(['data_objetivo']);
        });

        Schema::table('checkins', function (Blueprint $table) {
            $table->dropIndex(['usuario_id', 'data_checkin']);
        });
    }
};
