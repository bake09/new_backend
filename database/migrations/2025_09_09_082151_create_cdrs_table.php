<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        
        
        
        
        
        
        // id	
        // callid	
        // callstepid	
        // channelname	
        // cdraccountid	
        // calleraccountid	
        // callercallerid	
        // calledaccountid	
        // calledcallerid	
        // serviceid	
        // starttime	
        // ringingtime	
        // linktime	
        // callresulttime	
        // callresult	
        // callresultcausedby	
        // lineid	
        // linename	
        // callbacknumber	
        // answeredelswhere	
        // incoming	
        // answered	
        // hasvoicemail	
        // hasmonitor	
        // hasfax	
        // deleted	
        // privatecall	
        // callbacknumberextern	
        // summarystep	
        // duration	
        // login



        Schema::create('cdrs', function (Blueprint $table) {
            // $table->id();
            $table->bigInteger('id')->unique()->nullable();
            $table->bigInteger('callid')->nullable();
            $table->bigInteger('callstepid')->nullable();
            $table->text('channelname')->nullable();
            $table->bigInteger('cdraccountid')->nullable();
            $table->string('calleraccountid')->nullable();
            $table->string('callercallerid')->nullable();
            $table->bigInteger('calledaccountid')->nullable();
            $table->string('calledcallerid')->nullable();
            $table->bigInteger('serviceid')->nullable();
            $table->dateTime('starttime')->nullable();
            $table->dateTime('ringingtime')->nullable();
            $table->dateTime('linktime')->nullable();
            $table->dateTime('callresulttime')->nullable();
            $table->string('callresult')->nullable();
            $table->bigInteger('callresultcausedby')->nullable();
            $table->bigInteger('lineid')->nullable();
            $table->string('linename')->nullable();
            $table->text('callbacknumber')->nullable();
            $table->string('answeredelswhere')->nullable();
            $table->boolean('incoming')->nullable();
            $table->boolean('answered')->nullable();
            $table->boolean('hasvoicemail')->nullable();
            $table->boolean('hasmonitor')->nullable();
            $table->boolean('hasfax')->nullable();
            $table->boolean('deleted')->nullable();
            $table->boolean('privatecall')->nullable();
            $table->boolean('callbacknumberextern')->nullable();
            $table->boolean('summarystep')->nullable();
            $table->bigInteger('duration')->nullable();
            $table->bigInteger('login')->nullable();
            // $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cdrs');
    }
};
