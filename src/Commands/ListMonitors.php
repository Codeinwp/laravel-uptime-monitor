<?php

namespace Spatie\UptimeMonitor\Commands;

use Spatie\UptimeMonitor\MonitorRepository;
use Spatie\UptimeMonitor\Commands\MonitorLists\Healthy;
use Spatie\UptimeMonitor\Commands\MonitorLists\Disabled;
use Spatie\UptimeMonitor\Commands\MonitorLists\Unchecked;
use Spatie\UptimeMonitor\Commands\MonitorLists\UptimeCheckFailed;
use Spatie\UptimeMonitor\Commands\MonitorLists\CertificateCheckFailed;

class ListMonitors extends BaseCommand
{
    protected $signature = 'monitor:list {--api}';

    protected $description = 'List all monitors';

    public function handle()
    {
        $this->line('');

	    $isApiCall = $this->option('api');

        if (! MonitorRepository::getEnabled()->count()) {
	        if( isset( $isApiCall ) && $isApiCall == true ) {
		        return json_encode( array(
			        'status' => 200,
			        'message' => 'There are no monitors created or enabled. You create a monitor using the `monitor:create {url}` command',
			        'data' => array()
		        ), true );
	        } else {
		        $this->warn('There are no monitors created or enabled.');
		        $this->info('You create a monitor using the `monitor:create {url}` command');
	        }

        }


	    if( isset( $isApiCall ) && $isApiCall == true ) {
		    $healthyMonitor = MonitorRepository::getEnabled();

		    $results = $healthyMonitor->map(function (Monitor $monitor) {
			    $url = (string) $monitor->url;
			    $email = (string) $monitor->email;

			    $reachable = $monitor->uptime_status;

			    $onlineSince = $monitor->formattedLastUpdatedStatusChangeDate('forHumans');

			    return compact( 'url','email', 'reachable', 'onlineSince' );
		    });

		    return json_encode( array(
		    	'status' => 200,
			    'message' => 'Ok',
			    'data' => $results
		    ), true );
	    }

        Unchecked::display();
        Disabled::display();
        UptimeCheckFailed::display();
        CertificateCheckFailed::display();
        Healthy::display();
    }
}
