<?php 

/**
 * Plugin Name: Moneybox
 * Description: none specified
 * Version: 3.0
 * Author: HNW, WHS
 *
 **/

namespace Moneybox;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Moneybox{

	public function __construct(){
		$this->useStyles();
		$this->defineConstants();
		$this->includeFunctions();
	}

	private function defineConstants(){
		$this->define("MBOX_DS", DIRECTORY_SEPARATOR );
		$this->define("MBOX_UR", "user_registration_account_dashboard");
		$this->define("MBOX_ABSPATH", dirname(__FILE__) . MBOX_DS );
		$this->define("MBOX_TEMPLATE_PATH", MBOX_ABSPATH . MBOX_DS .'templates' . MBOX_DS);
	}

	private function define( $name, $value ) {
		if ( ! defined( $name ) ) {
			define( $name, $value );
		}
	}

	private function useStyles(){
		wp_enqueue_style('moneybox_style', plugins_url('/assets/css/style.css', __FILE__));
		wp_enqueue_style('moneybox_style_media', plugins_url('/assets/css/media.css', __FILE__));
		wp_enqueue_script('moneybox_script', plugins_url('/assets/js/script.js', __FILE__));
	}

	private function includeFunctions(){

		/*
		INCLUDE FUNCTIONS
		*/

		require_once MBOX_ABSPATH . '/functions/database.php';

		require_once MBOX_ABSPATH . '/functions/functions.php';

		require_once MBOX_ABSPATH . '/functions/panel.php';

	}

	protected function includeTemplate($template_name, $data){
		/*
		Это плохое решение и его нужно переделать
		Вывести логику подключения шаблонов в отдельный класс и там реализовать эту функцию
		Потому что логика вывода шаблонов в классе инициализаторе выглядит неуместно и может помешать расширению
		*/

		extract($data);
		
		include_once MBOX_TEMPLATE_PATH . $template_name . '_template.php';

	}
}

if (class_exists('Moneybox\Moneybox')) {
	$moneybox = new Moneybox();
}
