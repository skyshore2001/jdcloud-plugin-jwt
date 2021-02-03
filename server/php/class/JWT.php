<?php

// NOTE: 支持多个key, 在head中使用kid, 参见Firebase/README.md中JWK例子。
class JWT
{
	static function encode($payload) {
		// $key = "hello";
		// $jwt = \Firebase\JWT\JWT::encode($payload, $key, "HS256");
		@$key = file_get_contents(__DIR__ . "/../key/jwt.key");
		!$key && jdRet(E_SERVER, "bad jwt encode key");
		$jwt = \Firebase\JWT\JWT::encode($payload, $key, "RS256");
		return $jwt;
	}

	static function decode($jwt) {
		// $key = "hello";
		@$key = file_get_contents(__DIR__ . "/../key/jwt.pub");
		!$key && jdRet(E_SERVER, "bad jwt decode key");
		try {
			$decoded = \Firebase\JWT\JWT::decode($jwt, $key, ["RS256"]); // 'ES256', 'HS256', 'HS384', 'HS512', 'RS256', 'RS384', and 'RS512'
		}
		catch (Exception $ex) {
			logit($ex);
			throw new MyException(E_SERVER, "auth fails[jwt]: " . $ex->getMessage(), "认证错误", $ex);
		}
		return $decoded;
	}

/**
@fn JWT::verify()

返回非空表示验证成功，返回值为payload.
*/
	static function verify() {
		if (! ($s = $_SERVER["HTTP_AUTHORIZATION"]))
			return;
		$arr = explode(' ', $s, 2);
		if (count($arr) < 2 || strtolower($arr[0]) !== "bearer")
			return;
		$payload = (array)self::decode($arr[1]);
		return $payload;
	}
}

/* for test:
function api_test1()
{
	$payload = [
		"empId" => 3
	];
	$jwt = JWT::encode($payload);
	$decoded = JWT::decode($jwt);
	return [$jwt, $decoded];
}
*/
