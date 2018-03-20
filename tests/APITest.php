<?php

// use Illuminate\Foundation\Testing\WithoutMiddleware;
// use Illuminate\Foundation\Testing\DatabaseMigrations;
// use Illuminate\Foundation\Testing\DatabaseTransactions;

/**
* @runTestsInSeparateProcesses
*/
class APITest extends TestCase {

	public function testCreateCustomer()
	{
		$url = 'api/v1/customer';

		$newcustomer = [
			'first_name' => 'Test',
			'last_name' => 'Customer',
			'country_code' => 'MT',
			'gender' => 'M',
			'email' => 'test@test'.rand(1000,9999).'.com'
		];

 		$this->post($url, $newcustomer)
 		     ->seeStatusCode(201)
             ->seeJson($newcustomer);

        $response = json_decode($this->response->getContent(), true);
		$newcustomer = $response;

        $bonusParameterValueSetCorrectly = $response['bonus_parameter'] >= 5;
        $bonusParameterValueSetCorrectly = $bonusParameterValueSetCorrectly && $response['bonus_parameter'] <= 20;

        $this->assertTrue($bonusParameterValueSetCorrectly, 'Checking that Bonus Parameter set correctly between 5 and 20...');

        return $newcustomer;
	}

	/**
	 * @depends testCreateCustomer
 	*/
	public function testGetAllCustomers(array $newcustomer)
	{
		$url = 'api/v1/customer';

		$this->get($url)->seeStatusCode(200);

		$response = json_decode($this->response->getContent(), true);

		$this->assertTrue(count($response)>0, 'Checking that the customer collection is not empty...');

		return $newcustomer;
	}


	/**
	 * @depends testGetAllCustomers
 	*/
	public function testGetOneCustomer(array $newcustomer)
	{
		$url = 'api/v1/customer/'.$newcustomer['id'];

		$this->get($url)->seeStatusCode(200)->seeJson($newcustomer);

		return $newcustomer;
	}


	/**
	 * @depends testGetOneCustomer
 	*/
	public function testEditCustomers(array $newcustomer)
	{
		$url = 'api/v1/customer/'.$newcustomer['id'];

		$updated = [
			'first_name' => 'Test Updated',
			'last_name' => 'Customer',
			'country_code' => 'ABCD',
			'gender' => 'B'
		];

 		$this->put($url, $updated)
 		     ->seeStatusCode(422);

        $response = json_decode($this->response->getContent(), true);

        $validationErrorsSetCorrectly = isset($response['validation'])
                                     && isset($response['validation']['country_code'])
                                     && isset($response['validation']['gender'])
                                     && isset($response['validation']['email']);

        $this->assertTrue($validationErrorsSetCorrectly, 'Checking that validation errors returned correctly...');


        //test update success
		$updated = [
			'first_name' => 'Test Updated',
			'last_name' => 'Customer',
			'country_code' => 'DE',
			'gender' => 'F',
			'bonus_parameter' => $newcustomer['bonus_parameter'],
			'email' => $newcustomer['email']
		];

 		$this->put($url, $updated)
 		     ->seeStatusCode(200)
             ->seeJson($updated);

        return json_decode($this->response->getContent(), true);
	}

	/**
	 * @depends testEditCustomers
 	*/
	public function testCreateDeposit(array $newcustomer)
	{
		$url = 'api/v1/customer/'.$newcustomer['id'].'/deposit';

		$testReturn = [
			'bonus_balance' => '0.00',
			'real_money_balance' => '100.00',
			'balance' => 100
		];

 		$this->post($url, ['amount' => 100])
 		     ->seeStatusCode(201)
             ->seeJson($testReturn);

        return json_decode($this->response->getContent(), true);
	}


	/**
	 * @depends testCreateDeposit
 	*/
	public function testBonusDeposit(array $newcustomer)
	{
		//test that bonus is applied on 3rd deposit

		$url = 'api/v1/customer/'.$newcustomer['id'].'/deposit';

		$bonusApplied = 100 * ($newcustomer['bonus_parameter'] / 100);

		$testReturn = [
			'bonus_balance' => number_format((float)$bonusApplied, 2, '.', ''),
			'real_money_balance' => '300.00',
			'balance' => 300 + $bonusApplied
		];

		//2nd deposit
 		$this->post($url, ['amount' => 100])
 		     ->seeStatusCode(201);

 		//3rd deposit
 		$this->post($url, ['amount' => 100])
 		     ->seeStatusCode(201)
             ->seeJson($testReturn);

        return json_decode($this->response->getContent(), true);
	}


	/**
	 * @depends testBonusDeposit
 	*/
	public function testGetDeposits(array $newcustomer)
	{
		$url = 'api/v1/customer/'.$newcustomer['id'].'/deposit';

 		$this->get($url)
 		     ->seeStatusCode(200)
             ->seeJson();

		$response = json_decode($this->response->getContent(), true);

		$this->assertTrue(count($response)==3, 'Checking that the customer has 3 deposits...');

        return $newcustomer;
	}

	/**
	 * @depends testGetDeposits
 	*/
	public function testCreateWithdrawal(array $newcustomer)
	{
		$url = 'api/v1/customer/'.$newcustomer['id'].'/withdrawal';

		$testReturn = [
			'bonus_balance' => $newcustomer['bonus_balance'],
			'real_money_balance' => '0.00',
			'balance' => $newcustomer['balance']-300
		];

 		$this->post($url, ['amount' => 300])
 		     ->seeStatusCode(201)
             ->seeJson($testReturn);

        return json_decode($this->response->getContent(), true);
	}


	/**
	 * @depends testCreateWithdrawal
 	*/
	public function testInsufficientFunds(array $newcustomer)
	{
		$url = 'api/v1/customer/'.$newcustomer['id'].'/withdrawal';

 		$this->post($url, ['amount' => 1])
 		     ->seeStatusCode(400);

        return $newcustomer;
	}


	/**
	 * @depends testInsufficientFunds
 	*/
	public function testGetWithdrawals(array $newcustomer)
	{
		$url = 'api/v1/customer/'.$newcustomer['id'].'/withdrawal';

 		$this->get($url)
 		     ->seeStatusCode(200)
             ->seeJson();

		$response = json_decode($this->response->getContent(), true);

		$this->assertTrue(count($response)==1, 'Checking that the customer has 1 withdrawal...');

        return $newcustomer;
	}
	
}