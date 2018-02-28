<?php

namespace Spatie\UptimeMonitor\Commands;

use Illuminate\Support\Facades\DB;
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
			    $monitor = Monitor::where( 'token', $token )->first();
			    if ( $monitor ) {

				    $data = array( 'is_confirm' => true, 'url'=> $monitor->url );
				    Mail::send('emails_confirm', $data, function( $message ) use ( $monitor ) {
					    $message->to( trim( $monitor->email ) )->subject('Confirm Email for Uptime Monitor');
					    $message->from('monitor@themeisle.com','Uptime Monitor');
				    });

			    	$monitor->enable();
				    echo json_encode( array(
					    'status'  => 200,
					    'message' => "{$monitor->url} activated, email confirmed!"
				    ), true );
			    } else {
				    echo json_encode( array(
					    'status'  => 200,
					    'message' => "Token invalid!"
				    ), true );
			    }
		    } catch ( \Exception $e ) {
			    echo json_encode( array(
				    'status' => 500,
				    'message' => $e->getMessage()
			    ), true );
			    return;
		    }
	    }

	    $this->warn( "Check token called!" );
    }
}
