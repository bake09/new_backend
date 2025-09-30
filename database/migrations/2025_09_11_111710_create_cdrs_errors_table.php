<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cdrs_errors', function (Blueprint $table) {
            $table->bigInteger('id')->nullable();
            $table->bigInteger('callid')->nullable();
            $table->bigInteger('callstepid')->nullable();
            $table->string('channelname')->nullable();
            $table->bigInteger('cdraccountid')->nullable();
            $table->string('calleraccountid')->nullable();
            $table->string('callercallerid')->nullable();
            $table->bigInteger('calledaccountid')->nullable();
            $table->string('calledcallerid')->nullable();
            $table->bigInteger('serviceid')->nullable();
            $table->string('starttime')->nullable(); // VARCHAR für Fehlerfälle
            $table->string('ringingtime')->nullable();
            $table->string('linktime')->nullable();
            $table->string('callresulttime')->nullable();
            $table->string('callresult')->nullable();
            $table->bigInteger('callresultcausedby')->nullable();
            $table->bigInteger('lineid')->nullable();
            $table->string('linename')->nullable();
            $table->bigInteger('callbacknumber')->nullable();
            $table->string('answeredelswhere')->nullable();
            $table->string('incoming')->nullable(); // als String für ungültige Werte
            $table->string('answered')->nullable();
            $table->string('hasvoicemail')->nullable();
            $table->string('hasmonitor')->nullable();
            $table->string('hasfax')->nullable();
            $table->string('deleted')->nullable();
            $table->string('privatecall')->nullable();
            $table->string('callbacknumberextern')->nullable();
            $table->string('summarystep')->nullable();
            $table->bigInteger('duration')->nullable();
            $table->bigInteger('login')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cdrs_errors');
    }
};
