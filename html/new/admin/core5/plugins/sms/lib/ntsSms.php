<?php
class ntsSms {
	var $body;
	var $from;
	var $debug;
	var $disabled = false;
	var $error;
	var $params = array();

	function addLog(){
		}

	function _realSend( $to, $msg, $from = '' ){
		$return = 0;

		$this->setError( '' );
		$plugin = 'sms';

		$plm =& ntsPluginManager::getInstance();
		$gateway = $plm->getPluginSetting( $plugin, 'gateway' );

	/* the send file should return $success and $response vars */
		$sendFile = dirname(__FILE__) . '/../gateways/' . $gateway . '/send.php';
		require( $sendFile );

	/* add log */
		$ntsdb =& dbWrapper::getInstance();
		$tblName = 'smslog';
		$paramsArray = array(
			'sent_at'	=> time(),
			'to_number'		=> $to,
			'from_number'	=> $from,
			'message'	=> $msg,
			'success'	=> $success,
			'response'	=> $response,
			'gateway'	=> $gateway,
			);

		$ntsdb->insert( $tblName, $paramsArray, array('to' => 'string', 'from' => 'string') );
	/* end of log */

		return $success;
		}

	function ntsSms(){
		$plugin = 'sms';
		$this->body = '';
		$this->error = '';

		$this->disabled = false;

	/* from, from name, and debug settings */
		$plm =& ntsPluginManager::getInstance();
		$this->disabled = ( $plm->getPluginSetting( $plugin, 'disabled' ) ) ? true : false;
		$this->debug = ( $plm->getPluginSetting( $plugin, 'debug' ) ) ? true : false;
		}

	function setParam( $k, $v )
	{
		$this->params[$k] = $v;
	}

	function getParam( $k )
	{
		$return = isset($this->params[$k]) ? $this->params[$k] : NULL;
		return $return;
	}

	function setBody( $body ){
		$this->body = $body;
		}
	function setFrom( $from ){
		$this->from = $from;
		}

	function sendToOne( $toEmail ){
		$toArray = array( $toEmail );
		return $this->_send( $toArray );
		}

	function getBody(){
		return $this->body;
		}

	function _send( $toArray = array() ){
		if( $this->disabled )
			return true;

		$plugin = 'sms';
		$plm =& ntsPluginManager::getInstance();
		$settings = $plm->getPluginSettings( $plugin );
		if( isset($settings['from']) ){
			$this->setFrom( $settings['from'] );
			}

		$from = $this->from;
		$msg = $this->getBody();

		reset( $toArray );

		if( $this->debug ){
			$text = '';
			$text .= "\n-------------------------------------------\n";
			$text .= "SMS MESSAGE";
			$text .= "\n-------------------------------------------\n";
			foreach( $toArray as $to ){
				$text .= "To:<I>$to</I>\n";
				}
			$text .= "From: $from\n";
			$text .= "Msg:\n" . $msg . "\n";
			$text .= "\n-------------------------------------------\n";

			$outFile = NTS_APP_DIR . '/../smslog.txt';
			if( file_exists($outFile) ){
				$fp = fopen( $outFile, 'a' );
				fwrite( $fp, $text . "\n\n" );
				fclose($fp);
				}
			else {
				echo nl2br($text);
				}
			}
		else {
			foreach( $toArray as $to ){
				$this->_realSend( $to, $msg, $from );
				}
			}

		return true;
		}

	function isError(){
		$return = $this->error ? true : false;
		return $return;
		}

	function getError(){
		return $this->error;
		}

	function setError( $error ){
		$this->error = $error;
		}
	}
?>