<?php

/*
 * Helper class for validating Recurly.js responses
 */
class Recurly_jsResponse
{
  private $signature;

  function __construct($signature)
  {
    if (!is_null($signature)) {
      $this->signature = $signature;
    } else {
      if (isset($_POST) && isset($_POST['signature'])) {
        $this->signature = $_POST['signature'];
      } else {
        throw new Recurly_ForgedQueryStringError("Response does not contain a signature");
      }
    }
  }

  # Validate the signature and return the protected data in the signature
  public function getResults()
  {
    list($signature, $data) = explode('|', $this->signature, 2);
    $expected_signature = Recurly_js::_hash($data);

    if ($signature != $expected_signature)
      throw new RecurlyForgedQueryStringException("Recurly.js signature forged or incorrect private key");

    parse_str($data, $results);

    if (isset($results['timestamp']) && $this->time_difference($results['timestamp']) > 3600)
      throw new Recurly_ForgedQueryStringError("Timestamp is over an hour old. The server timezone may be incorrect or this may be a replay attack.");

    return $results;
  }

  // In its own function so it can be stubbed for testing
  // Positive amount of time between server timestamp and Recurly timestamp
  protected function time_difference($timestamp)
  {
    return abs(time() - $timestamp);
  }
}
