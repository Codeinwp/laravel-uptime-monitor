<?php

namespace Spatie\UptimeMonitor\Commands;

use Spatie\UptimeMonitor\Models\Monitor;

class DeleteMonitor extends BaseCommand
{
    protected $signature = 'monitor:delete {url} {--api}';

    protected $description = 'Delete a monitor';

    public function handle()
    {
        $url = $this->argument('url');

        $monitor = Monitor::where('url', $url)->first();

	    $isApiCall = $this->option('api');

	    if( isset( $isApiCall ) && $isApiCall == true ) {
		    if (! $monitor) {
		    	return json_encode( array(
		    		'status' => 401,
				    'message' => "Monitor {$url} is not configured"
			    ), true );
		    }

		    try {
			    $monitor->delete();
			    return json_encode( array(
				    'status' => 200,
				    'message' => "{$monitor->url} will not be monitored anymore"
			    ), true );
		    } catch ( \Exception $e ) {
			    return json_encode( array(
				    'status' => 500,
				    'message' => $e->getMessage()
			    ), true );
		    }
	    } else {
	        if (! $monitor) {
	            $this->error("Monitor {$url} is not configured");

	            return;
	        }

		    if ($this->confirm("Are you sure you want stop monitoring {$monitor->url}?")) {
			    $monitor->delete();

			    $this->warn("{$monitor->url} will not be monitored anymore");
		    }
	    }
    }
}
