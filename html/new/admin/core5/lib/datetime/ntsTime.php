<?php
global $NTS_TIME_WEEKDAYS;
$NTS_TIME_WEEKDAYS = array( M('Sunday'), M('Monday'), M('Tuesday'), M('Wednesday'), M('Thursday'), M('Friday'), M('Saturday'),  );

global $NTS_TIME_WEEKDAYS_SHORT;
$NTS_TIME_WEEKDAYS_SHORT = array( M('Sun'), M('Mon'), M('Tue'), M('Wed'), M('Thu'), M('Fri'), M('Sat') );

global $NTS_TIME_MONTH_NAMES;
$NTS_TIME_MONTH_NAMES = array( 'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec' );

global $NTS_TIME_MONTH_NAMES_REPLACE;
$NTS_TIME_MONTH_NAMES_REPLACE = array();
reset( $NTS_TIME_MONTH_NAMES );
foreach( $NTS_TIME_MONTH_NAMES as $mn ){
	$NTS_TIME_MONTH_NAMES_REPLACE[] = M($mn);
	}

/* new object oriented style */
class ntsTime extends DateTime {
	var $timeFormat = 'H:i';
	var $dateFormat = 'd/m/Y';
	var $weekdays = array();
	var $weekdaysShort = array();
	var $monthNames = array();
	var $timezone = '';

	function __construct( $time = 0, $tz = '' ){
//static $initCount;
//$initCount++;
//echo "<h2>init $initCount</h2>";
		if( strlen($time) == 0 )
			$ts = 0;
		if( ! $time )
			$time = time();
		if( is_array($time) )
			$time = $time[0];

		parent::__construct();
		$this->setTimestamp( $time );

		if( ! $tz ){
			$tz = NTS_COMPANY_TIMEZONE;
			}
		$this->setTimezone( $tz );

		$this->timeFormat = NTS_TIME_FORMAT;
		$this->dateFormat = NTS_DATE_FORMAT;
		}

	function setNow(){
		$this->setTimestamp( time() );
		}

	static function expandPeriodString( $what, $multiply = 1 ){
		$string = '';
		switch( $what ){
			case 'd':
				$string = '+' . 1 * $multiply . ' days';
				break;
			case '2d':
				$string = '+' . 2 * $multiply . ' days';
				break;
			case 'w':
				$string = '+' . 1 * $multiply . ' weeks';
				break;
			case '2w':
				$string = '+' . 2 * $multiply . ' weeks';
				break;
			case '3w':
				$string = '+' . 3 * $multiply . ' weeks';
				break;
			case '6w':
				$string = '+' . 6 * $multiply . ' weeks';
				break;
			case 'm':
				$string = '+' . 1 * $multiply . ' months';
				break;
			}
		return $string;
		}

	function setTimezone( $tz )
	{
		if( is_array($tz) )
			$tz = $tz[0];

//		if( preg_match('/^-?[\d\.]$/', $tz) ){
//			$currentTz = ($tz >= 0) ? '+' . $tz : $tz;
//			$tz = "Etc/GMT$currentTz";
//			echo "<br><br>Setting timezone as Etc/GMT$currentTz<br><br>";
//			}

		if( strlen($tz) )
		{
			$this->timezone = $tz;
			$tz = new DateTimeZone($tz);
			parent::setTimezone( $tz );
		}
	}

	function getLastDayOfMonth(){
		$thisYear = $this->getYear(); 
		$thisMonth = $this->getMonth();

		$this->setDateTime( $thisYear, ($thisMonth + 1), 0, 0, 0, 0 );
		$return = $this->format( 'j' );
		return $return;
		}

	function getTimestamp(){
		if( function_exists('date_timestamp_get') ){
			return parent::getTimestamp();
			}
		else {
			$return = $this->format('U');
			return $return;
			}
		}

	function setTimestamp( $ts ){
		if( strpos($ts, '-') !== FALSE ){
			$tss = explode( '-', $ts );
			$ts = $tss[0];
			}
		if( function_exists('date_timestamp_set') ){
			return parent::setTimestamp( $ts );
			}
		else {
			$strTime = '@' . $ts;
			parent::__construct( $strTime );
			$this->setTimezone( $this->timezone );
			return;
			}
		}

	static function splitDate( $string ){
		$year = substr( $string, 0, 4 );
		$month = substr( $string, 4, 2 );
		$day = substr( $string, 6, 4 );
		$return = array( $year, $month, $day );
		return $return;
		}

	function timestampFromDbDate( $date ){
		list( $year, $month, $day ) = ntsTime::splitDate( $date );
		$this->setDateTime( $year, $month, $day, 0, 0, 0 );
		$return = $this->getTimestamp();
		return $return;
		}

	function getParts(){
		$return = array( $this->format('Y'), $this->format('m'), $this->format('d'), $this->format('H'), $this->format('i') );
		return $return;
		}

	function getYear(){
		$return = $this->format('Y');
		return $return;
		}

	function getMonth(){
		$return = $this->format('m');
		return $return;
		}

	function getMonthName(){
		global $NTS_TIME_MONTH_NAMES;
		$thisMonth = (int) $this->getMonth();
		$return = $NTS_TIME_MONTH_NAMES[ $thisMonth - 1 ];
		return $return;
		}

	function getDay(){
		$return = $this->format('d');
		return $return;
		}

	function getTimeOfDay(){
		$ts = $this->getTimestamp();
		$dayStart = $this->getStartDay();
		$return = $ts - $dayStart;
		return $return;
		}

	function formatTimeOfDay( $ts ){
		$this->setDateDb('20130315');
		$this->modify( '+' . $ts . ' seconds' );
		return $this->formatTime();
		}

	function getStartDay(){
		$thisYear = $this->getYear(); 
		$thisMonth = $this->getMonth();
		$thisDay = $this->getDay();

		$this->setDateTime( $thisYear, $thisMonth, $thisDay, 0, 0, 0 );
		$return = $this->getTimestamp();
		return $return;
		}

	function setStartDay(){
		$thisYear = $this->getYear(); 
		$thisMonth = $this->getMonth();
		$thisDay = $this->getDay();

		$this->setDateTime( $thisYear, $thisMonth, $thisDay, 0, 0, 0 );
		$return = $this->getTimestamp();
		return $return;
		}

	function setNextDay(){
		$this->setStartDay();
		$this->modify( '+1 day' );
		}

	function getEndDay(){
		$thisYear = $this->getYear(); 
		$thisMonth = $this->getMonth();
		$thisDay = $this->getDay();

		$this->setDateTime( $thisYear, $thisMonth, ($thisDay + 1), 0, 0, 0 );
		$return = $this->getTimestamp();
		return $return;
		}

	function setStartWeek(){
		$conf =& ntsConf::getInstance();
		$weekStartsOn = $conf->get('weekStartsOn');

		$this->setStartDay();
		$weekDay = $this->getWeekday();

		while( $weekDay != $weekStartsOn ){
			$this->modify( '-1 day' );
			$weekDay = $this->getWeekday();
			}
		}

	function setStartMonth(){
		$thisYear = $this->getYear(); 
		$thisMonth = $this->getMonth();
		$this->setDateTime( $thisYear, $thisMonth, 1, 0, 0, 0 );
		}

	function setEndMonth(){
		$thisYear = $this->getYear(); 
		$thisMonth = $this->getMonth();
		$this->setDateTime( $thisYear, ($thisMonth + 1), 1, 0, 0, -1 );
		}

	function setStartYear(){
		$thisYear = $this->getYear(); 
		$this->setDateTime( $thisYear, 1, 1, 0, 0, 0 );
		}

	function timezoneShift(){
		$return = 60 * 60 * $this->timezone;
		return $return;
		}

	function setDateTime( $year, $month, $day, $hour, $minute, $second ){
		$this->setDate( $year, $month, $day );
		$this->setTime( $hour, $minute, $second );
		}

	function setDateDb( $date ){
		list( $year, $month, $day ) = ntsTime::splitDate( $date );
		$this->setDateTime( $year, $month, $day, 0, 0, 0 );
		}

	function formatTime( $duration = 0, $displayTimezone = 0 ){
		$return = $this->format( $this->timeFormat );
		if( $duration ){
			$this->modify( '+' . $duration . ' seconds' );
			$return .= ' - ' . $this->format( $this->timeFormat );
			}

		if( $displayTimezone ){
			$return .= ' [' . ntsTime::timezoneTitle($this->timezone) . ']';
			}
		return $return;
		}

	function formatDate(){
		global $NTS_TIME_MONTH_NAMES, $NTS_TIME_MONTH_NAMES_REPLACE;
		$return = $this->format( $this->dateFormat );
	// replace months 
		$return = str_replace( $NTS_TIME_MONTH_NAMES, $NTS_TIME_MONTH_NAMES_REPLACE, $return );
		return $return;
		}

	static function formatDateParam( $year, $month, $day ){
		$return = sprintf("%04d%02d%02d", $year, $month, $day);
		return $return;
		}

	function formatDate_Db(){
		$dateFormat = 'Ymd';
		$return = $this->format( $dateFormat );
		return $return;
		}

	function formatTime_Db(){
		$dateFormat = 'Hi';
		$return = $this->format( $dateFormat );
		return $return;
		}

	function getWeekday(){
		$return = $this->format('w');
		return $return;
		}

	function formatWeekday(){
		global $NTS_TIME_WEEKDAYS;
		$return = $NTS_TIME_WEEKDAYS[ $this->format('w') ];
		return $return;
		}

	function formatFull(){
		$return = $this->formatWeekdayShort() . ', ' . $this->formatDate() . ' ' . $this->formatTime();
		return $return;
		}

	function formatWeekdayShort(){
		return ntsTime::weekdayLabelShort( $this->format('w') );
		}

	static function weekdayLabelShort( $wdi )
	{
		global $NTS_TIME_WEEKDAYS_SHORT;
		$return = $NTS_TIME_WEEKDAYS_SHORT[ $wdi ];
		return $return;
	}

	static function timezoneTitle( $tz, $showOffset = FALSE ){
		if( is_array($tz) )
			$tz = $tz[0];
		$tzobj = new DateTimeZone( $tz );
		$dtobj = new DateTime();
		$dtobj->setTimezone( $tzobj );


		if( $showOffset ){
			$offset = $tzobj->getOffset($dtobj);
			$offsetString = 'GMT';
			$offsetString .= ($offset >= 0) ? '+' : '';
			$offsetString = $offsetString . ( $offset/(60 * 60) );
			$return = $tz . ' (' . $offsetString . ')';
			}
		else {
			$return = $tz;
			}

		return $return;
		}

	static function getTimezones(){
		$skipStarts = array('Brazil/', 'Canada/', 'Chile/', 'Etc/', 'Mexico/', 'US/');
		$return = array();
		$timezones = timezone_identifiers_list();
		reset( $timezones );
		foreach( $timezones as $tz ){
			if( strpos($tz, "/") === false )
				continue;
			$skipIt = false;
			reset( $skipStarts );
			foreach( $skipStarts as $skip ){
				if( substr($tz, 0, strlen($skip)) == $skip ){
					$skipIt = true;
					break;
					}
				}
			if( $skipIt )
				continue;

			$tzTitle = ntsTime::timezoneTitle( $tz );
			$return[] = array( $tz, $tzTitle );
			}
		return $return;
		}

	static function formatPeriodShort( $ts ){
		$day = (int) ($ts/(24 * 60 * 60));
		$hour = (int) ( ($ts - (24 * 60 * 60)*$day)/(60 * 60));
		$minute = (int) ( $ts - (24 * 60 * 60)*$day - (60 * 60)*$hour ) / 60;

		$formatArray = array();
		if( $day > 0 ){
			$formatArray[] = $day;
			}
		$formatArray[] = sprintf( "%02d", $hour );
		$formatArray[] = sprintf( "%02d", $minute );

		$verbose = join( ':', $formatArray );
		return $verbose;
		}

	static function formatPeriod( $ts ){
		$conf =& ntsConf::getInstance();
		$limitMeasure = $conf->get('limitTimeMeasure');

		switch( $limitMeasure ){
			case 'minute':
				$day = 0;
				$hour = 0;
				$minute = (int) ( $ts ) / 60;
				break;
			case 'hour':
				$day = 0;
				$hour = (int) ( ($ts)/(60 * 60));
				$minute = (int) ( $ts - (60 * 60)*$hour ) / 60;
				break;
			default:
				$day = (int) ($ts/(24 * 60 * 60));
				$hour = (int) ( ($ts - (24 * 60 * 60)*$day)/(60 * 60));
				$minute = (int) ( $ts - (24 * 60 * 60)*$day - (60 * 60)*$hour ) / 60;
				break;
			}

		$formatArray = array();
		if( $day > 0 ){
			if( $day > 1 )
				$formatArray[] = $day . ' ' . M('Days');
			else
				$formatArray[] = $day . ' ' . M('Day');
			}
		if( $hour > 0 ){
			if( $hour > 1 )
				$formatArray[] = $hour . ' ' . M('Hours');
			else
				$formatArray[] = $hour . ' ' . M('Hour');
			}
		if( $minute > 0 ){
			if( $minute > 1 )
				$formatArray[] = $minute . ' ' . M('Minutes');
			else
				$formatArray[] = $minute . ' ' . M('Minute');
			}

		$verbose = join( ' ', $formatArray );
		return $verbose;
		}
	}
?>