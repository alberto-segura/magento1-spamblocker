<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension
 * to newer versions in the future.
 *
 *
 * @category   NoBullShitDev
 * @package    NoBullShitDev_SpamBlocker
 * @author     Copyright (c) 2019 Alberto Segura https://nobullshit.dev/
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
**/

class NoBullShitDev_SpamBlocker_Model_Observer {
  /**
  *** Validate customer email values.
  *** If any of the paramteters are set, we check if it contains forbidden email domains.
  **/
  public function checkEmail (Varien_Event_Observer $observer) {
    $params = Mage::app()->getRequest()->getParams();
    // Mage::log('Russian SPAM: START'.print_r($params,1), null, 'spam.log', true);

    $blocked_emails = Mage::getStoreConfig('customer/create_account/email_blocked_domain');
    // Mage::log('Russian SPAM: '.$blocked_emails, null, 'spam.log', true);

    if($blocked_emails){
      $blocked_emails = str_replace(" ","",$blocked_emails);
      $blocked_emails = explode(",",$blocked_emails);
      $entered_email = explode('@',$params['email']);
      if (in_array($entered_email[1], $blocked_emails)) {
        $email_error = Mage::getStoreConfig('customer/create_account/email_blocked_domain_error');
        if($email_error){
          throw Mage::exception('Mage_Customer', Mage::helper('customer')->__($email_error));
        } else {
          throw Mage::exception('Mage_Customer', Mage::helper('customer')->__('The email address is not valid to create an account.'));
        }
      }
    }

    return $this;
  }

  /**
  *** Validate customer name values.
  *** If any of the paramteters are set, we check if it contains forbidden characters or Cyrillics.
  **/
  public function checkFullNameCharacters (Varien_Event_Observer $observer) {
    $params = Mage::app()->getRequest()->getParams();
    # Mage::log('Russian SPAM: START'.print_r($params,1), null, 'spam.log', true);

    if($params['firstname']){
      $contains_cyrillic = (bool) preg_match('/([:=<>$@$!%*?&#^])|([\p{Cyrillic}])/u', $params['firstname']);
      if ($contains_cyrillic) {
        throw Mage::exception('Mage_Customer', Mage::helper('customer')->__('The first name is not valid.'));
      }
    }

    if($params['middlename']){
      $contains_cyrillic = (bool) preg_match('/([:=<>$@$!%*?&#^])|([\p{Cyrillic}])/u', $params['middlename']);
      if ($contains_cyrillic) {
        throw Mage::exception('Mage_Customer', Mage::helper('customer')->__('The middle name is not valid.'));
      }
    }

    if($params['lastname']){
      $contains_cyrillic = (bool) preg_match('/([:=<>$@$!%*?&#^])|([\p{Cyrillic}])/u', $params['lastname']);
      if ($contains_cyrillic) {
        throw Mage::exception('Mage_Customer', Mage::helper('customer')->__('The last name is not valid.'));
      }
    }

    return $this;
  }

  /**
  *** Validate contact form values.
  *** If any of the paramteters are set, we check if it contains forbidden characters or Cyrillics.
  **/
  public function checkContactFormCharacters (Varien_Event_Observer $observer) {
    $params = Mage::app()->getRequest()->getParams();
    # Mage::log('Russian SPAM: START'.print_r($params,1), null, 'spam.log', true);

    $blocked_emails = Mage::getStoreConfig('customer/create_account/email_blocked_domain');
    $email_error = Mage::getStoreConfig('customer/create_account/email_blocked_domain_error');
    $error = FALSE;
    $msg = __('There is an error in your form.');

    if($params['name']){
      $contains_cyrillic = (bool) preg_match('/([:=<>$@$!%*?&#^])|([\p{Cyrillic}])/u', $params['name']);
      if ($contains_cyrillic) {
        $msg = __('The first name is not valid.');
        $error = TRUE;
      }
    }

    if($params['telephone']){
      $contains_cyrillic = (bool) preg_match('/([:=<>$@$!%*?&#^])|([\p{Cyrillic}])/u', $params['telephone']);
      if ($contains_cyrillic) {
        $msg = __('The telephone is not valid.');
        $error = TRUE;
      }
    }

    if($params['comment']){
      $contains_cyrillic = (bool) preg_match('/([:=<>$@$!%*?&#^])|([\p{Cyrillic}])/u', $params['comment']);
      if ($contains_cyrillic) {
        $msg = __('The comment is not valid.');
        $error = TRUE;
      }
    }

    if($blocked_emails){
      $blocked_emails = str_replace(" ","",$blocked_emails);
      $blocked_emails = explode(",",$blocked_emails);
      $entered_email = explode('@',$params['email']);
      if (in_array($entered_email[1], $blocked_emails)) {
        if($email_error){
          $msg = $email_error;
          $error = TRUE;
        }
      }
    }

    if ($error) {
      Mage::getSingleton('customer/session')->addError(Mage::helper('captcha')->__($msg));
      $controller = $observer->getControllerAction();
      $controller->setFlag('', Mage_Core_Controller_Varien_Action::FLAG_NO_DISPATCH, true);
      $controller->getResponse()->setRedirect(Mage::getUrl('*/*/'));
    }

    return $this;
  }
}
