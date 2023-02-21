<?php
    use voku\db\DB;
    use voku\helper\DbWrapper4Session;
    use voku\helper\Session2DB;
	
	if(!empty($config['session2db_autoloader'])) {

		// include autoloader
		require __DIR__ . $config['session2db_autoloader'];
		
		// initialize the database connection e.g. via "voku\db\DB"-class
		$db = DB::getInstance(
			$config['db_host'], // e.g. localhost
			$config['db_user'], // e.g. user_1
			$config['db_password'], // e.g. ******
			$config['db_name'], // e.g. db_1
			$config['db_port'],     // e.g. 3306
			'utf8mb4',  // e.g. utf8mb4
			false,       // e.g. true|false (exit_on_error)
			false,       // e.g. true|false (echo_on_error)
			'',         // e.g. 'framework\Logger' (logger_class_name)
			''          // e.g. 'DEBUG' (logger_level)
			);

		// you can also use you own database implementation via the "Db4Session"-interface,
		// take a look at the "DbWrapper4Session"-class for a example
		$db_wrapper = new DbWrapper4Session($db);

		// initialize "Session to DB"
		new Session2DB(
		  'mxGqzwwTV6RS',                    // security_code
		  3600,                              // session_lifetime
		  true,                              // lock_to_user_agent
		  true,                              // lock_to_ip
		  1,                                 // gc_probability
		  1000,                              // gc_divisor
		  'session_data',                    // table_name
		  60,                                // lock_timeout
		  $db_wrapper,                       // db (must implement the "Db4Session"-interface)
		  true                               // start_session (start the session-handling automatically, otherwise you need to use session2db->start() afterwards)
		);
	}
?>