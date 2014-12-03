<?php
/*
  $ Id: chinabank.php v1.3 Jack $

  Payment modules for Zen-cart v1.3.8a 中文版
  
  Written by Decoder (htz@imust.cn)
  Modify date 2004-12-16 1:08
  Modify date 2006-11-19 by mpwjy
  Modify date 2008-03-27 by Jack Huang

  Released under the GNU General Public License
*/


  class CHINABANK {
    var $code, $title, $description, $enabled;
  /**
   * order status setting for pending orders
   *
   * @var int
   */
   var $order_pending_status = 1;
  /**
   * order status setting for completed orders
   *
   * @var int
   */
   var $order_status = DEFAULT_ORDERS_STATUS_ID;

// class constructor
    function CHINABANK() {
      $this->code = 'chinabank';
      $this->title = MODULE_PAYMENT_CHINABANK_TEXT_TITLE;
      $this->sort_order = MODULE_PAYMENT_CHINABANK_SORT_ORDER;
	  $this->description = MODULE_PAYMENT_CHINABANK_TEXT_DESCRIPTION;
      $this->enabled = ((MODULE_PAYMENT_CHINABANK_STATUS == 'True') ? true : false);

       if ((int)MODULE_PAYMENT_CHINABANK_ORDER_STATUS_ID > 0) {
        $this->order_status = MODULE_PAYMENT_CHINABANK_ORDER_STATUS_ID;
      }

      if (is_object($order)) $this->update_status();
	  $this->form_action_url = 'https://pay3.chinabank.com.cn/PayGate';
    }

// class methods
    function javascript_validation() {
      return false;
    }

   function selection() {
     return array('id' => $this->code,
                   'module' => MODULE_PAYMENT_CHINABANK_TEXT_CATALOG_LOGO,
                   'icon' => MODULE_PAYMENT_CHINABANK_TEXT_CATALOG_LOGO
                   );
   }

    function pre_confirmation_check() {
      return false;
    }

    function confirmation() {
      return false;
    }

    function process_button() {
      global $order, $currencies;

		//测试订单号是否已经产生
		$r_v_ymd = date('Ymd');
		$time1 = date("his");
		$str = $_SESSION['customer_id']."#".$time1;
		$r_v_oid = date('Ymd').'-'.MODULE_PAYMENT_CHINABANK_MID.'-'.$str;
		$r_v_total = number_format(($order->info['total']) * $currencies->get_value(MODULE_PAYMENT_CHINABANK_MONEYTYPE), 2, '.', '');

		//校验
		$ordermd5 = $r_v_total.MODULE_PAYMENT_CHINABANK_MONEYTYPE.$r_v_oid.MODULE_PAYMENT_CHINABANK_MID.zen_href_link(FILENAME_CHECKOUT_PROCESS, '', 'SSL').MODULE_PAYMENT_CHINABANK_MD5KEY;
		$r_ordermd5 = strtoupper(md5($ordermd5));

		$process_button_string = zen_draw_hidden_field('v_mid', MODULE_PAYMENT_CHINABANK_MID) .
							   zen_draw_hidden_field('v_oid', $r_v_oid) .
                               zen_draw_hidden_field('v_amount', $r_v_total) .
							   zen_draw_hidden_field('v_moneytype', MODULE_PAYMENT_CHINABANK_MONEYTYPE) .
	                           zen_draw_hidden_field('v_url', zen_href_link(FILENAME_CHECKOUT_PROCESS, '','SSL')) .
                               zen_draw_hidden_field('v_md5info', $r_ordermd5) .
                               zen_draw_hidden_field('remark1', "ZENCARTCHINA") .
                               zen_draw_hidden_field('remark2', "CHINABANK")
							   ;

	  return $process_button_string;
    }

    function before_process() {
	global $messageStack;

      $script_path = $_SERVER['HTTP_REFERER'];
      $me = explode('main_page=', $script_path);
      $result = explode('&', $me[0]);
      if ($result[0] == 'checkout_confirmation') {
		$messageStack->add_session('checkout_payment', '支付不成功！', 'error');
		zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL'));
	}

      return false;
	}

    function after_process() {
	global $messageStack;
	
      global $db,$_POST,$order,$insert_id;
	  if ($_POST['v_pstatus'] == '30') {
	            $sql_data_array = array('orders_id' => $insert_id,
                                  'orders_status_id' => $new_order_status_id,
                                  'date_added' => 'now()',
                                  'customer_notified' => $customer_notification,
                                  'comments' =>"使用[".$order->info['payment_method']."]的[".$_POST['v_pmode']."]*****支付失败*****！支付订单号＝".$_POST['v_oid'] );

        zen_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array);
		$messageStack->add_session('checkout_payment', '错误信息：' . $_POST['v_pstring'], 'error');
        zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL', true, false));
      }
      $compare_string = $_POST['v_oid'].$_POST['v_pstatus'].$_POST['v_amount'].$_POST['v_moneytype'].MODULE_PAYMENT_CHINABANK_MD5KEY;
      $compare_hash1 = strtoupper(md5($compare_string));
      $compare_hash2 = $_POST['v_md5'];
  
      if ($compare_hash1 != $compare_hash2 or $_POST['v_pstatus'] != '20')	{
		$messageStack->add_session('checkout_payment', '错误信息：' . $_POST['v_pstring'], 'error');
        zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL', true, false));
	  }
		$new_order_status_id = $order->info['order_status'];
		//$new_order_status_id = $order->info['order_status'] + 1;
        if ($order->info['total'] > 0) {
          $customer_notification = (SEND_EMAILS == 'true') ? '1' : '0';
          $sql_data_array = array('orders_id' => $insert_id,
                                  'orders_status_id' => $new_order_status_id,
                                  'date_added' => 'now()',
                                  'customer_notified' => $customer_notification,
                                  'comments' =>"使用[".$order->info['payment_method']."]的[".$_POST['v_pmode']."]支付成功！支付订单号＝".$_POST['v_oid'] );

          zen_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array); 

        $db->Execute("update  " . TABLE_ORDERS . " set orders_status  = '" . $new_order_status_id . "', last_modified = now()  where orders_id  = '" . $insert_id . "'");
        }

	  $_SESSION['order_created'] = '';
	  return false;
	}

    function output_error() {
      return false;
    }

    function check() {
      global $db;
	  if (!isset($this->_check)) {
        $check_query = $db->Execute("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_PAYMENT_CHINABANK_STATUS'");
        $this->_check = $check_query->RecordCount();
      }
      return $this->_check;
    }

    function install() {
      global $db;
	  $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('打开网银在线支付模式', 'MODULE_PAYMENT_CHINABANK_STATUS', 'True', '您是否同意打开网银在线支付模式', '6', '1', 'zen_cfg_select_option(array(\'True\', \'False\'), ', now())");

	  $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('商户编号', 'MODULE_PAYMENT_CHINABANK_MID', '1001', '网银在线给您分配的商户编号', '6', '2', now())");

      $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('商户MD5密钥', 'MODULE_PAYMENT_CHINABANK_MD5KEY', 'test', '您在网银在线设置的MD5密钥', '6', '3', now())");

      $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('交易支付货币类型', 'MODULE_PAYMENT_CHINABANK_MONEYTYPE', 'CNY', '交易支付货币类型，CNY是人民币', '6', '4', now())");

      $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('商品存货情况反馈', 'MODULE_PAYMENT_CHINABANK_ORDERSTATUS', '1', '向支付平台反馈支付动作购买的商品是否有存货。默认填写“1”', '6', '5', now())");
    
      $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('显示顺序', 'MODULE_PAYMENT_CHINABANK_SORT_ORDER', '1', '支付方式的显示顺序，低数值靠前。', '6', '6', now())");

	}

    function remove() {
      global $db;
      $db->Execute("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    function keys() {
      return array('MODULE_PAYMENT_CHINABANK_STATUS', 'MODULE_PAYMENT_CHINABANK_MID', 'MODULE_PAYMENT_CHINABANK_MD5KEY', 'MODULE_PAYMENT_CHINABANK_MONEYTYPE', 'MODULE_PAYMENT_CHINABANK_ORDERSTATUS', 'MODULE_PAYMENT_CHINABANK_SORT_ORDER');
      }
    }
?>
