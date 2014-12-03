<?php
/*
  $Id: chinabank.php v1.3 2008/03/26 Jack $

  Payment modules for Zen-Cart v1.3.8a

  Written by Decoder (htz@imust.cn)
  Modify date 2004-12-15 16:23

  Released under the GNU General Public License
*/

  define('MODULE_PAYMENT_CHINABANK_TEXT_TITLE', 'Chinabank Payment Gateway');
  define('MODULE_PAYMENT_CHINABANK_TEXT_DESCRIPTION', 'Chinabank Payment Gateway');

  define('MODULE_PAYMENT_CHINABANK_MARK_BUTTON_IMG', DIR_WS_MODULES . '/payment/chinabank/chinabank.gif');
  define('MODULE_PAYMENT_CHINABANK_MARK_BUTTON_ALT', 'Chinabank Payment Gateway');
  define('MODULE_PAYMENT_CHINABANK_ACCEPTANCE_MARK_TEXT', 'Electronic Payment Specialist');

  define('MODULE_PAYMENT_CHINABANK_TEXT_CATALOG_LOGO', '<img src="' . MODULE_PAYMENT_CHINABANK_MARK_BUTTON_IMG . '" alt="' . MODULE_PAYMENT_CHINABANK_MARK_BUTTON_ALT . '" title="' . MODULE_PAYMENT_CHINABANK_MARK_BUTTON_ALT . '" /> &nbsp;' .  '<span class="smallText">' . MODULE_PAYMENT_CHINABANK_ACCEPTANCE_MARK_TEXT . '</span>');

?>