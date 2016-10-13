#!/usr/bin/php
<?php
error_reporting(0);

set_include_path(get_include_path() . PATH_SEPARATOR . "/usr/local/mgr5/include/php");
define('__MODULE__', "okpayresult");
require_once 'okpay_util.php';

	Debug($_SERVER["REQUEST_METHOD"] . " OKPAY IPN Handler Init");
	
	echo "Content-Type: text/html\n\n";
	
	$params = file_get_contents("php://stdin"); //CgiInput(true);
	
	Debug(serialize($params));
	
	if(!isset($_POST["ok_invoice"]) || empty($_POST["ok_invoice"])) {
		Debug("Not IPN request");
		echo '<h1>404 Not Found</h1>';
		exit;
	}
	
	
	$status   = $_POST["ok_txn_status"];
	$amount   = $_POST["ok_txn_gross"];
	$currency = $_POST["ok_txn_currency"];
	$receiver = $_POST["ok_receiver"];
	$invoice  = $_POST["ok_invoice"];
	$txn_id   = $_POST["ok_txn_id"];
	
	
	$info = LocalQuery("payment.info", array("elid" => $invoice));
	
	$request = 'ok_verify=true';
	
	foreach ($_POST as $key => $value) {
		$value = urlencode(stripslashes($value));
		$request .= "&$key=$value";
	}
	
	$ch = curl_init('https://checkout.okpay.com/ipn-verify');
	
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HEADER, false);
	curl_setopt($ch, CURLOPT_TIMEOUT, 30);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	
	$response = curl_exec($ch);
	
	Debug($info->asXML());
	
	/*
	if (strcmp($response, 'VERIFIED') == 0)
	{
		if($amount == (string)$info->payment[0]->paymethodamount && $currency == (string)$info->payment[0]->currency[1]->iso))
		{
			if ($status == "completed")
			{
				LocalQuery("payment.setpaid", array("elid" => $invoice, "info" => "OKPAY Payment", "externalid" => $txn_id));
				Debug("Payment successfull");
			}
			elseif ($status == "pending" || $status == "hold")
			{
				LocalQuery("payment.setinpay", array("elid" => $invoice));
				Debug("Pending payment");
			}
			elseif ($status == "reversed" || $status == "error" || $status == "canceled")
			{
				LocalQuery("payment.setnopay", array("elid" => $invoice));
				Debug("Payment error or cancelled");
			}
			else
			{
				Debug("Invalid payment status");
			}
		}
		else
		{
			LocalQuery("payment.setfraud", array("elid" => $invoice, "info" => "Possible fraud attempt", "externalid" => $txn_id));
			Debug("Possible fraud");
		}
	}
	else
	{
		Debug(serialize($_POST));
		Debug("Invalid IPN request");
	}
	*/
	curl_close($ch);
	
	Debug("----------------");
	
?>