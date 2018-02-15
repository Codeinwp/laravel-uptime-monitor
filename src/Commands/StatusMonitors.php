<?php

namespace Spatie\UptimeMonitor\Commands;

use Spatie\UptimeMonitor\MonitorRepository;
use Spatie\UptimeMonitor\Commands\MonitorLists\Healthy;
use Spatie\UptimeMonitor\Commands\MonitorLists\Disabled;
use Spatie\UptimeMonitor\Commands\MonitorLists\Unchecked;
use Spatie\UptimeMonitor\Commands\MonitorLists\UptimeCheckFailed;
use Spatie\UptimeMonitor\Commands\MonitorLists\CertificateCheckFailed;

use Spatie\UptimeMonitor\Models\Monitor;

class StatusMonitors extends BaseCommand
{
    protected $signature = 'monitor:status {--api}';

    protected $description = 'List status for monitors';

    public function handle()
    {
        $this->line('');

	    $isApiCall = $this->option('api');

        if (! MonitorRepository::getStatus()->count()) {
	        if( isset( $isApiCall ) && $isApiCall == true ) {
		        echo json_encode( array(
			        'status' => 200,
			        'message' => 'There are no monitors created or enabled. You create a monitor using the `monitor:create {url}` command',
			        'data' => array()
		        ), true );
		        return;
	        } else {
		        $this->warn('There are no monitors created or enabled.');
		        $this->info('You create a monitor using the `monitor:create {url}` command');
	        }

        }


	    if( isset( $isApiCall ) && $isApiCall == true ) {
		    $statusMonitor = MonitorRepository::getStatus();

		    $results = $statusMonitor->map(function (Monitor $monitor) {
			    $total = (string) $monitor->total;
			    $active = (string) $monitor->active;
			    $last = $monitor->last_checked_date;

			    return compact( 'total','active', 'last' );
		    });

		    echo json_encode( array(
		    	'status' => 200,
			    'message' => 'Ok',
			    'data' => $results
		    ), true );
		    return;
	    }
    }
}
