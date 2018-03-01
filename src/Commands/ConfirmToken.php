<?php

namespace Spatie\UptimeMonitor\Commands;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\View;
use Spatie\UptimeMonitor\Models\Monitor;
use Spatie\UptimeMonitor\MonitorRepository;

class ConfirmToken extends BaseCommand
{
    protected $signature = 'monitor:confirm-token {--api} 
                            {--token= : Token to check and confirm}';

    protected $description = 'Check the token and activate monitor';

    public function handle()
    {
	    $isApiCall = $this->option('api');
	    $token = $this->option('token');

	    if( isset( $isApiCall ) && $isApiCall == true && isset( $token ) && $token != '' ) {
		    try {
			    $monitor = Monitor::where( 'token', $token )->where('uptime_check_enabled', 0)->first();
			    if ( $monitor ) {

				    $data = array( 'is_confirm' => true, 'url'=> $monitor->url );
				    Mail::send('emails_confirm', $data, function( $message ) use ( $monitor ) {
					    $message->to( trim( $monitor->email ) )->subject('Email Confirmed for Uptime Monitor');
					    $message->from('monitor@orbitfox.com','Uptime Monitor');
				    });

			    	$monitor->enable();

				    $this->info( "{$monitor->url} activated, email confirmed!" );
			    } else {

				    $this->warn( "Token invalid or not applicable!" );
			    }
		    } catch ( \Exception $e ) {
			    echo json_encode( array(
				    'status' => 500,
				    'message' => $e->getMessage()
			    ), true );
			    return 1;
		    }
	    }
    }
}
