<?php 

namespace Panel;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class MoneyboxPanel{

	function __construct(){
		add_action('admin_menu', array($this, 'moneyboxAdminMenu'));
	}

	public function moneyboxAdminMenu(){
		add_menu_page(
			esc_html__('Moneybox Settings&Info', 'Moneybox'), 
			esc_html__('Moneybox', 'moneybox'),
			'manage_options',
			'moneybox',
			[$this, 'moneyboxAdminPage'],
			'dashicons-money-alt',
			20
		);
	}

	public function moneyboxAdminPage(){
		if(!empty($_POST)){
			global $wpdb;
			$db = new \Moneybox\MoneyboxDatabase($wpdb);
			switch($_POST['value']){
				case 'profit':
				if(!empty($_POST['amount']) && floatval($_POST['amount'])){
					$db->moneyboxUpdateProfit($_POST['amount']);
				}else{
					exit('Invalid parameter!');
				}
				break;
				case 'reset':
				$db->moneyboxResetProfit();
				break;
				case 'jackpot':
				$db->moneyboxSupport($_POST['id'], $_POST['option']);
				break;
				case 'request':
				$db->moneyboxProcessRequest($_POST['id'], $_POST['option']);
				break;
				case 'our':
				$db->moneyboxOurProfit();
				break;
			}
			header("Location: " . $_SERVER["REQUEST_URI"]);
			exit();
		}

		require_once MBOX_ABSPATH . '/functions/database.php';

		require_once MBOX_ABSPATH . '/functions/panel_data_picker.php';

		$data = new \MoneyboxPanelDataPicker();
		$data = $data->data;
		
		require_once MBOX_TEMPLATE_PATH . '/admin/admin.php';
	}
}

if (class_exists('Panel\MoneyboxPanel')) {
	$MoneyboxPanel = new MoneyboxPanel();
}
