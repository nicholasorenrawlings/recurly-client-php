<?php

/*
 * Helper class for signing Recurly.js requests
 */
class Recurly_js
{
  /**
   * Recurly.js Private Key
   */
  public static $privateKey;

  private $data;


  function __construct($data)
  {
    $this->data = $data;
  }

  # Create a signature with the protected data
  public function generateSignature()
  {
    return $this->_data($this->data);
  }

  # Create the data string for the signature
  private function _data($params)
  {
    $queryString = $this->_queryString($params);
    return Recurly_js::_hash($queryString) . "|" . $queryString;
  }

  private function _queryString($params)
  {
    $params['time'] = $this->utc_timestamp();
    ksort($params); // Not neccessary, but makes the signature easier to read
    return http_build_query($params, null, '&');
  }

  // Hash a message using the client's private key
  public static function _hash($message)
  {
    if (!isset(Recurly_js::$privateKey) || strlen(Recurly_js::$privateKey) != 32) {
      throw new Recurly_ConfigurationError("Recurly.js private key is not set. The private key must be 32 characters.");
    }
    return hash_hmac('sha1', $message, Recurly_js::$privateKey);
  }

  

  // In its own function so it can be stubbed for testing
  protected function utc_timestamp()
  {
    return gmdate("Y-m-d\TH:i:s\Z");
  }

  /*
   * DEPRECIATED METHODS
   *
   */

  public function verifySubscription()
  {
    $response = new Recurly_jsResponse();
    return $rresponseesult->getResults();
  }

  public function verifyTransaction()
  {
    $response = new Recurly_jsResponse();
    return $rresponseesult->getResults();
  }

  public function verifyBillingInfoUpdated()
  {
    $response = new Recurly_jsResponse();
    return $rresponseesult->getResults();
  }

  // Create a signature for a one-time transaction for the given $accountCode
  // DEPRECIATED, use generateSignature() instead
  public static function signTransaction($amountInCents, $currency, $accountCode = null)
  {
    if (empty($currency) || strlen($currency) != 3)
      throw new InvalidArgumentException("Invalid currency");
    if (intval($amountInCents) <= 0)
      throw new InvalidArgumentException("Invalid amount in cents");

    $data = array(
      'account' => array(
        'account_code' => $accountCode
      ),
      'transaction' => array(
        'amount_in_cents' => $amountInCents,
        'currency' => $currency
      )
    );
    $recurly_js = new Recurly_js($data);
    return $recurly_js->generateSignature();
  }

  // Create a signature for a new subscription
  // DEPRECIATED, use generateSignature() instead
  public static function signSubscription($planCode, $accountCode)
  {
    $data = array(
      'account' => array(
        'account_code' => $accountCode
      ),
      'subscription' => array(
        'plan_code' => $planCode
      )
    );
    $recurly_js = new Recurly_js($data);
    return $recurly_js->generateSignature();
  }

  // Create a signature for updating billing information for the given $accountCode
  // DEPRECIATED, use generateSignature() instead
  public static function signBillingInfoUpdate($accountCode)
  {
    if (empty($accountCode))
      throw new InvalidArgumentException("Account code is required");

    $data = array(
      'account' => array(
        'account_code' => $accountCode
      )
    );
    $recurly_js = new Recurly_js($data);
    return $recurly_js->generateSignature();
  }
}
