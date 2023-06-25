#!/usr/bin/php
<?php
// Configuration
$ITOP_URL = 'https://demo.combodo.com/itop';
$ITOP_USER = 'admin';
$ITOP_PWD = 'admin';
$TICKET_CLASS = 'Incident';
$TITLE = 'Service Down on %1$s';
$DESCRIPTION = 'The Service "%2$s" is down on "%1$s"';
$COMMENT = 'Created from PHP';

if ($argc != 5)
{
	echo "Usage: {$argv[0]} <host> <service> <service_status> <service_state_type>\n";
	exit;
}
$host = $argv[1];
$service = $argv[2];
$service_status = $argv[3];
$service_state_type = $argv[4];
$url = $ITOP_URL.'/webservices/rest.php?version=1.3';

if (($service_status != "OK") && ($service_status != "UP") && ($service_state_type == "HARD"))
{
	$payload = array(
			'operation' => 'core/create',
			'class' => $TICKET_CLASS,
			'fields' => array(
					'org_id' => sprintf('SELECT Organization AS O JOIN FunctionalCI AS CI ON CI.org_id = O.id WHERE CI.name="%1$s"', $host),
					'title' => sprintf($TITLE, $host, $service),
					'description' => sprintf($DESCRIPTION, $host, $service),
					'functionalcis_list' => array(
						array('functionalci_id' => sprintf("SELECT FunctionalCI WHERE name='%s'", $host), 'impact_code' => 'manual'),	
					),
			),
			'comment' => $COMMENT,
			'output_fields' => 'id',
	);
 
	$data = array(
			'auth_user' => $ITOP_USER,
			'auth_pwd' => $ITOP_PWD,
			'json_data' => json_encode($payload)
	);

	$options = array(
			CURLOPT_POST			=> count($data),
			CURLOPT_POSTFIELDS		=> http_build_query($data),
			// Various options...
			CURLOPT_RETURNTRANSFER	=> true,     // return the content of the request
			CURLOPT_HEADER			=> false,    // don't return the headers in the output
			CURLOPT_FOLLOWLOCATION	=> true,     // follow redirects
			CURLOPT_ENCODING		=> "",       // handle all encodings
			CURLOPT_AUTOREFERER		=> true,     // set referer on redirect
			CURLOPT_CONNECTTIMEOUT	=> 120,      // timeout on connect
			CURLOPT_TIMEOUT			=> 120,      // timeout on response
			// Disabling SSL verification
			CURLOPT_SSL_VERIFYPEER	=> false,    // Disable SSL certificate validation
			CURLOPT_SSL_VERIFYHOST 	=> false,	 // Disable host vs certificate validation
	);

	$handle = curl_init($url);
	curl_setopt_array($handle, $options);
	$response = curl_exec($handle);
	$errno = curl_errno($handle);
	$error_message = curl_error($handle);
	curl_close($handle);

	if ($errno !== 0)
	{
		echo "Problem opening URL: $url, $error_message\n";
		exit;
	}
	$decoded_response = json_decode($response, true);
	if ($decoded_response === false)
	{
		echo "Error: ".print_r($response, true)."\n";
	}
	else if ($decoded_response['code'] != 0)
	{
		echo $decoded_response['message']."\n";
	}
	else
	{
		echo "Ticket created.\n";
	}
}
else
{
	echo "Service state type !='HARD', doing nothing.\n";
}