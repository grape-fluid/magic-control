<?php

namespace Tests\Cases;

require __DIR__ . '/../bootstrap.php';

use Tester\TestCase;
use Tester\Assert;


class BaseTest extends TestCase
{

	public function testBase()
	{
		Assert::count(1, 1);
    }

}

(new BaseTest)->run();