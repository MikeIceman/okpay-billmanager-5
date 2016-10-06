#!/usr/bin/php
<?php
set_include_path(get_include_path() . PATH_SEPARATOR . "/usr/local/mgr5/include/php");
define('__MODULE__', "okpaypayment");
require_once 'bill_util.php';

//echo "Content-Type: text/html

$client_ip = ClientIp();
$param = CgiInput();

if ($param["auth"] == "") {
	throw new Error("no auth info");
} else {
	$info = LocalQuery("payment.info", array("elid" => $param["elid"], ));
	
	$receiver = (string)$info->payment[0]->paymethod[1]->WalletID;
	$amount   = (string)$info->payment[0]->paymethodamount;
	$currency = (string)$info->payment[0]->currency[1]->iso;
	$invoice  = (string)$info->payment[0]->id;
	$project  = (string)$info->payment[0]->project->name;
	$comment  = (string)$info->payment[0]->number;
	$email    = (string)$info->payment[0]->email;
	//$direct_payment => (string)$info->payment[0]->paymethod[1]->direct_payment;
	
	echo "<html>
			<head>
			<meta http-equiv='Content-Type' content='text/html; charset=UTF-8' />
			<link rel='shortcut icon' href='billmgr.ico' type='image/x-icon' />
			<script language='JavaScript'>
				function DoSubmit() {
					//document.okpayform.submit();
				}
			</script>
			</head>
			<body onload='DoSubmit()'>
			<form name='okpayform' action='https://checkout.okpay.com/' method='POST'>
				<input type='hidden' name='ok_receiver' value='$receiver'>
				<input type='hidden' name='ok_invoice' value='$invoice'>
				<input type='hidden' name='ok_currency' value='$currency'>
				<input type='hidden' name='ok_item_1_price' value='$amount'>
				<input type='hidden' name='ok_item_1_name' value='$project [$comment]'>
				<input type='hidden' name='ok_payer_email' value='$email'>
				<input type='hidden' name='ok_return_success' value='" . (string)$info->payment[0]->manager_url . "?func=payment.success&elid=" . $elid . "&module=" . __MODULE__ . "'>
				<input type='hidden' name='ok_return_fail' value='" . (string)$info->payment[0]->manager_url . "?func=payment.fail&elid=" . $elid . "&module=" . __MODULE__ . "'>
			</form>
			</body>
		</html>";
}

?>