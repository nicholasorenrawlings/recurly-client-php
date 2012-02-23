<?php

class Recurly_jsMock extends Recurly_js {
  // Overload time so we can mock
  function utc_timestamp() {
    return "2012-02-21T12:34:56Z";
  }
}
class Recurly_jsResponseMock extends Recurly_jsResponse {
  // Overload time so we can mock
  function time_difference($timestamp) {
    return 1024;
  }
}

class Recurly_RecurlyjsTestCase extends UnitTestCase {

  function setUp() {
    Recurly_js::$privateKey = "0123456789abcdef0123456789abcdef";
  }

  function tearDown() {
  }

  function testGenerateSignatureSimple() {
    $recurly_js = new Recurly_jsMock(array(
      'account' => array('account_code' => '123')
    ));
    $signature = $recurly_js->generateSignature();

    $this->assertEqual($signature, "dbc8c01277f42a055f558f813b707b8afb1e0d0d|account%5Baccount_code%5D=123&time=2012-02-21T12%3A34%3A56Z");
  }

  function testGenerateSignatureComplex() {
    $recurly_js = new Recurly_jsMock(array(
      'account' => array('account_code' => '123'),
      'plan_code' => 'gold',
      'add_ons' => array(
        array('add_on_code'=>'extra','quantity'=>5),
        array('add_on_code'=>'bonus','quantity'=>2)
      ),
      'quantity' => 1
    ));
    $signature = $recurly_js->generateSignature();

    $this->assertEqual($signature, "e5cf1025288de4204e7bba35f52fe77579360f67|account%5Baccount_code%5D=123&add_ons%5B0%5D%5Badd_on_code%5D=extra&" .
                                   "add_ons%5B0%5D%5Bquantity%5D=5&add_ons%5B1%5D%5Badd_on_code%5D=bonus&add_ons%5B1%5D%5Bquantity%5D=2&" .
                                   "plan_code=gold&quantity=1&time=2012-02-21T12%3A34%3A56Z");
  }

  function testVerifyResult() {
    $signature = '332fe46229227d9141c24a3d741666458874c05a|account[account_code]=112358132134&timestamp=2012-02-21T12%3A34%3A56Z';
    $recurly_js = new Recurly_jsResponseMock($signature);
    $results = $recurly_js->getResults();
    print_r($results);
    $this->assertEqual($results['account']['account_code'], "112358132134");
    $this->assertEqual($results['timestamp'], "2012-02-21T12:34:56Z");
  }
}
