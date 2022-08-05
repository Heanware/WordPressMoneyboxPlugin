<?php 

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class MoneyboxPanelDataPicker{

	public Array $data;

	function __construct(){
		global $wpdb;
		$this->data['moneyboxes'] = $wpdb->get_results( // CASE в запросе просто для того, чтобы поиграться с SQL'ем, фактически не особо нужно.
			"SELECT  
			`YOURTABLE`.id, 
			user_id, 
			CASE 
			WHEN moneybox_id = 1 OR moneybox_id = 4 THEN 'Curious'
			WHEN moneybox_id = 2 OR moneybox_id = 5 THEN 'Optimal'
			WHEN moneybox_id = 3 OR moneybox_id = 6 THEN 'Greedy'
			ELSE 'NONE SPECIFIED'
			END
			moneybox_id,
			amount,
			profit,
			profit_week,
			broken,
			insurance,
			date,
			min_interval,
			`YOURTABLE`.user_login
			FROM YOURTABLE JOIN YOURTABLE ON `YOURTABLE`.user_id = `YOURTABLE`.ID"
		);
		$this->data['referal_list'] = $wpdb->get_results("SELECT * FROM YOURTABLE"); 
		$this->data['users'] = $wpdb->get_results("SELECT * FROM YOURTABLE WHERE referal_bank != 0");
		$this->data['weeks'] = $wpdb->get_results("SELECT * FROM YOURTABLE");
		$this->data['requests'] = $wpdb->get_results(
			"SELECT
			`YOURTABLE`.user_login,
			moneybox_id,
			total,
			cardnumber
			FROM 
			YOURTABLE
			JOIN YOURTABLE ON `YOURTABLE`.ID = `YOURTABLE`.user_id"
		);
		$this->data['support'] = $wpdb->get_results("SELECT * FROM YOURTABLE");
		$this->data['invested'] = $wpdb->get_results("SELECT endbank FROM YOURTABLE WHERE date = (SELECT MAX(id) - 1 FROM YOURTABLE)")[0]->endbank;
		$this->data['userspart'] = $wpdb->get_results("SELECT SUM(amount + profit_week) AS inv FROM YOURTABLE")[0]->inv;
		$this->data['referal'] = $wpdb->get_results("SELECT ROUND(SUM(referal_bank), 4) AS ref FROM YOURTABLE")[0]->ref;
		$this->data['insurance'] = $wpdb->get_results("SELECT insurance AS ins FROM YOURTABLE WHERE id = (SELECT MAX(id) FROM YOURTABLE)")[0]->ins;
		$this->data['support'] = $wpdb->get_results("SELECT * FROM YOURTABLE");
		$this->data['personal'] = $wpdb->get_results("SELECT our FROM YOURTABLE")[0]->our;

	}
}