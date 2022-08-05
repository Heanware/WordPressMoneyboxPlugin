<?php 

namespace Moneybox;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class MoneyboxFunctions extends Moneybox
{

	/* 

	This class contains methods to display needed information and process data to display it correctly.
	Every template contains a bit of php insertion to output information.

	--- DO NOT USE THIS CLASS TO ACCESS DATABASE VIA INSERT\DELETE\UPDATE QUERIES !  ---

	*/

	private readonly Object $wpdb;

	private Object $db;

	private  Array $moneyboxes;

	private readonly int $referal_bank;

	public function __construct(){

		add_action('user_registration_account_content', array($this, 'moneyboxInit'));

		add_action(MBOX_UR, array($this, 'moneyboxShowRefUrl'));

		add_action(MBOX_UR, array($this, 'moneyboxShowMoneybox'));

		add_action(MBOX_UR, array($this, 'moneyboxShowWithdraw'));

		add_action(MBOX_UR, array($this, 'moneyboxAddInsurance'));

		add_action(MBOX_UR, array($this, 'moneyboxShowBids'));

		add_action('cryptobox_after_new_payment', array($this, 'cryptoboxAfterNewPayment'), 10, 4);

		add_action('qiwi_after_new_payment', array($this, 'cryptoboxAfterNewPayment'), 10, 4);

	}

	public function moneyboxInit(){
		global $wpdb;
		$this->wpdb = $wpdb;
		$this->db = new MoneyboxDatabase($this->wpdb);
		$id = get_current_user_id();
		$this->moneyboxes = $this->wpdb->get_results("SELECT * FROM YOURTABLE WHERE user_id = $id");
		$this->referal_bank = $this->wpdb->get_results("SELECT referal_bank FROM YOURTABLE WHERE id = $id")[0]->referal_bank;
	}

	public function moneyboxShowRefUrl(){
		$data['url'] = "yoursite/registration/?referal=".wp_get_current_user()->user_login;
		$data['referal_bank'] = $this->referal_bank;
		parent::includeTemplate('ref_url', $data);
	}
	//function arraymapper
	public function moneyboxShowMoneybox(){
		if (count($this->moneyboxes) != 0){
			$moneyboxes = array();
			$ins = "";
			foreach ($this->moneyboxes as $single) { 
				$dir = get_template_directory_uri().'/assets/svg/'; 
				$mbox_date = new \DateTime($single->date);
				$moneybox = array(
					'number' => $single->id,
					'amount' => $single->amount,
					'profit_week' => $single->profit_week,
					'insurance' => $single->insurance,
					'broken' => $single->broken == 0 ? "Не разбита!" : "Разбита",
					'isInsurance' => $single->insurance == 0 ? "Нет страховки!" : "Застрахована!",
					'date' => $single->date,
					'min_interval' => $single->min_interval,
				);

				switch($single->moneybox_id){
					case 1:
					case 4:
					$img = '9';
					$moneybox['type'] = 'Любопытная';
					break;
					case 2:
					case 5:
					$img = '10';
					$moneybox['type'] = 'Оптимальная';
					break;
					case 3:
					case 6:
					$img = '11';
					$moneybox['type'] = 'Жадная';
					break;
				}
				switch ($single->broken) {
					case 1:
					$moneybox['dir'] = $dir.$img.'1.svg';
					break;
					case 0:
					switch ($single->insurance) {
						case 0:
						$moneybox['dir'] = $dir.$img.'.svg';  
						break;
						case 1:
						$moneybox['dir'] = $dir.$img.'ins.svg';
						break;
					}
					break;
					default:
					break;
				}
				$moneyboxes[] = $moneybox;
			}
			$data['moneyboxes'] = $moneyboxes;
			parent::includeTemplate('moneyboxes', $data);

		}
	}

	public function moneyboxShowWithdraw(){

		$timestamp = time();
		$bCanWithdraw = date('D', $timestamp) != 'Sun' /* && date('D', $timestamp) != 'Sat'*/ /*true*/;
		if($bCanWithdraw) {
			if (isset($_POST['withdraw'])){
				$request = array(
					'user_id' => get_current_user_id(),
					'moneybox_id' => $_POST['moneybox_number'],
					'cardnumber' => str_replace(' ', '', $_POST['requisites'])
				);
				if(!empty($request['cardnumber']) && !empty($request['moneybox_id']) && ($request['moneybox_id'] == 'referal' || intval($request['moneybox_id']) != 0)){
					if($request['moneybox_id'] == 'referal' && $this->referal_bank != 0)
					{
						$request['referal_bank'] = $this->referal_bank;
						$this->db->moneyboxWithdraw($request, 1);
					}else{
						$this->db->moneyboxWithdraw($request, 0);
					}
					header("Location: ". $_SERVER["REQUEST_URI"]);
					exit();
				}
			}

			$withdrawFilter = function($moneybox){
				if($moneybox->broken == 0 && $moneybox->min_interval <= date('Y-m-d')){
					switch ($moneybox->moneybox_id) {
						case 1:
						case 4:
						$moneybox->type = "Любопытная";
						break;
						case 2:
						case 5:
						$moneybox->type = "Оптимальная";
						break;
						case 3:
						case 6:
						$moneybox->type = "Жадная";
						break;
						case 12:
						$moneybox->type = "Тестовая";
						break; 
					}
					return $moneybox;
				}
			};
			$moneyboxes = array_filter(array_map($withdrawFilter, $this->moneyboxes)); // Не хотелось плодить foreach'и, поэтому выбрал array_map
			$referal_bank = $this->referal_bank;
			if (count($moneyboxes) != 0 || $referal_bank != 0) {
				$data['moneyboxes'] = $moneyboxes;
				$data['referal_bank'] = $referal_bank;
				parent::includeTemplate('withdraw', $data);
			}
		}
	}

	public function moneyboxAddInsurance(){
		if (isset($_POST['insurance'])){
			$this->db->moneyboxInsurance((int)$_POST['moneybox_number'], get_current_user_id());
			header("Location: ". $_SERVER["REQUEST_URI"]);
			exit();
		}
		
		$moneyboxes = array();

		foreach($this->moneyboxes as $moneybox){ 
			$moneybox->broken == 0 && $moneybox->date >= $date && $moneybox->insurance == 0 ? $moneyboxes[] = $moneybox : false;  
		}
		if(count($moneyboxes) != 0){
			$data['moneyboxes'] = $moneyboxes;
			parent::includeTemplate('add_insurance', $data);
		}
	}

	public function moneyboxShowBids(){
		$data['user_bids'] = $this->wpdb->get_results("SELECT * FROM YOURTABLE WHERE user_id = ".get_current_user_id()."", ARRAY_A); 
		parent::includeTemplate('user_bids', $data);
	}

	public function cryptoboxAfterNewPayment($user_id, $order_id, $payment_details, $box_status){
		global $wpdb;
		$db = new MoneyboxDatabase($wpdb);
		if(empty($payment_details['error'])){
			$paymentDate = strtotime($payment_details['paymentDate']);
			switch (str_replace('product_', '', $order_id)) {
				case 1:
				case 4:
				$last_date = strtotime('+3 MONTH', $paymentDate);
				break;
				case 12:
				$last_date = strtotime('+0 MONTH', $paymentDate);
				default:
				$last_date = strtotime('+1 MONTH', $paymentDate);
				break;
			}
			if(is_array($box_status)){
				$same_payment = count(
					$wpdb->get_results(
						$wpdb->prepare(
							"SELECT * FROM YOURTABLE 
							WHERE billId = %s", 
							$box_status['billId']
						), ARRAY_A
					)
				) != 0;
				if (!$same_payment) 
				{
					$db->qiwiInsertNewPayment($box_status['billId']);
				}
			}
			$interval = date('Y-m-d', $last_date);
			$db->moneyboxAddAfterCheck($user_id, $order_id, $payment_details, $interval);
		}
	}
}

if (class_exists('Moneybox\MoneyboxFunctions')) {
	$moneyboxF = new MoneyboxFunctions();
}