#!/usr/bin/php
<?php
set_include_path(get_include_path() . PATH_SEPARATOR . "/usr/local/mgr5/include/php");

define('__MODULE__', "okpayresult.php");

require_once 'okpay_util.php';

echo "Content-Type: text/xml\n\n";

$out_xml = simplexml_load_string("<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<result/>\n");

$param = CgiInput(true);

$status = $param["ok_txn_status"];
$amount = $param["ok_txn_gross"];
$currency = $param["ok_txn_currency"];
$receiver = $param["ok_receiver"];
$invoice = $param["ok_invoice"];
$txn_id = $param["ok_txn_id"];

if ($invoice == "") {
	$out_xml->addChild("result_code", "5");
	$out_xml->addChild("description", "empty invoice id");
} else {
	$info = LocalQuery("payment.info", array("elid" => $param["ok_invoice"]));
	
	$request = 'ok_verify=true';
	
	foreach ($param as $key => $value) {
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
	
	if (strcmp($response, 'VERIFIED') == 0 && $amount == (string)$info->payment[0]->paymethodamount && $currency == (string)$info->payment[0]->currency[1]->iso)
	{
		if ($status == "completed")
		{
			LocalQuery("payment.setpaid", array("elid" => $invoice));
			$out_xml->addChild("result_code", "0");
		}
		elseif ($status == "pending" || $status == "hold")
		{
			LocalQuery("payment.setinpay", array("elid" => $invoice));
			$out_xml->addChild("result_code", "0");
		}
		elseif ($status == "reversed" || $status == "error" || $status == "canceled")
		{
			LocalQuery("payment.setnopay", array("elid" => $invoice));
			$out_xml->addChild("result_code", "0");
		}
		else
		{
			$out_xml->addChild("result_code", "5");
			$out_xml->addChild("description", "invalid data");
		}
	}
	else
	{
		$out_xml->addChild("result_code", "150");
		$out_xml->addChild("description", "invalid IPN");
	}
	
	curl_close($ch);
}

Debug("out: ". $out_xml->asXML());
echo $out_xml->asXML();
?>