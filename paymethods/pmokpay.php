#!/usr/bin/php
<?php
set_include_path(get_include_path() . PATH_SEPARATOR . "/usr/local/mgr5/include/php");
define('__MODULE__', "pmokpay");
require_once 'okpay_util.php';

$longopts  = array
(
    "command:",
    "payment:",
    "amount:",
);

$options = getopt("", $longopts);

try {
	$command = $options['command'];
	Debug("command ". $options['command']);

	if ($command == "config") {
		$config_xml = simplexml_load_string($default_xml_string);
		$feature_node = $config_xml->addChild("feature");
		
		// Refundable
		//$feature_node->addChild("refund",			"on");
		
		// Allow money transfer
		// $feature_node->addChild("transfer",		"on");
		
		// Redirect to payment system checkout
		$feature_node->addChild("redirect",			"on");
		
		// Do not show payment option on checkout form (trminal payments)
		// $feature_node->addChild("noselect",		"on");
		
		// Payer credentials doesn't needed
		$feature_node->addChild("notneedprofile",	"on");
		
		// Advanced payment system configuration needed
		// $feature_node->addChild("pmtune",		"on");
		
		// Validation of payment system custom parameters needed
		$feature_node->addChild("pmvalidate",		"on");
		
		// Additional actions in payment form
		// $feature_node->addChild("crtune",		"on");
		
		//$feature_node->addChild("crvalidate",		"on");
		//$feature_node->addChild("crset",			"on");
		//$feature_node->addChild("crdelete",		"on");
		
		// $feature_node->addChild("rftune",		"on");
		// $feature_node->addChild("rfvalidate",	"on");
		// $feature_node->addChild("rfset", 		"on");
		
		// $feature_node->addChild("tftune", 		"on");
		// $feature_node->addChild("tfvalidate",	"on");
		// $feature_node->addChild("tfset", 		"on");
		
		$param_node = $config_xml->addChild("param");
		
		$param_node->addChild("payment_script", "/mancgi/okpaypayment.php");
		
		echo $config_xml->asXML();
		
	}elseif ($command == "pmvalidate") {
		$paymethod_form = simplexml_load_string(file_get_contents('php://stdin'));
		
		Debug($paymethod_form->asXML());
		
		$WalletID = $paymethod_form->WalletID;
		
		Debug($WalletID);
		
		if (!preg_match("/^OK[0-9]{9}$/", $WalletID)) {
			throw new Error("value", "WalletID", $WalletID);
		}
		
		echo $paymethod_form->asXML();
		
	} else {
		throw new Error("unknown command");
	}
} catch (Exception $e) {
	echo $e;
}

?>