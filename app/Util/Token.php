<?php
declare(strict_types=1);

namespace App\Util;

use App\Constants\Constants;
use App\Exception\InvalidConfigException;
use App\Exception\LoginException;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Hyperf\Utils\Traits\StaticInstance;
use InvalidArgumentException;

class Token
{
    use StaticInstance;

    private static $alg = 'HS256';

    private static $app_key;

    private $user;

    private $user_id;

    public function __construct()
    {
//        self::$alg = 'HS256';
        self::$app_key = config('app_key');
    }

    public function encode(array $payload): string
    {
        return JWT::encode($payload, self::$app_key, self::$alg);
    }

    /**
     * @param string $jwt
     * @return array
     */
    public function decode(string $jwt): array
    {
        $msg = '';
        $code = -1;
        try {
            $decode = JWT::decode($jwt, self::$app_key, [self::$alg]);

            $data = $decode->data;
            $this->user = $data;
            $this->user_id = $data['user_id'];

            return (array)$decode;
        } catch (ExpiredException $exception) {
            //过期token
            //throw new LoginException('token过期！', -1);
            $msg = 'token过期！';
        } catch (InvalidArgumentException $exception) {
            //参数错误
            //throw new LoginException('token参数非法！', -1);
            $msg = 'token参数非法！';
        } catch (\UnexpectedValueException $exception) {
            //token无效
            //throw new LoginException('token无效！', -1);
            $msg = 'token无效！';
        } catch (\Exception $exception) {
            //throw new LoginException($exception->getMessage(), -1);
            $msg = $exception->getMessage();
        } finally {
            throw new LoginException($msg, $code);
        }
    }

    /**
     * 创建token
     * @param Payload $payload
     * @return string
     */
    public function createToken(Payload $payload)
    {
        $token = $this->encode($payload->toArray());

        return $token;
    }

    public function checkToken(string $token): Payload
    {
        if (empty($token)) {
            throw new LoginException('token不能为空！', -1);
        }

        $decode = $this->decode($token);

        if (is_null($decode)) {
            throw new LoginException('token无效！', -1);
        }

        $jwt = new Payload($decode);

        if (Constants::SCOPE_ROLE !== $jwt->scopes) {
            throw new LoginException('token参数非法！', -2);
        }

        return $jwt;

    }

    public function checkRefreshToken(string $refresh)
    {
        if (empty($refresh)) {
            throw new LoginException('token不能为空！', -1);
        }

        $decode = $this->decode($refresh);

        if (is_null($decode)) {
            throw new LoginException('token无效！', -1);
        }

        $jwt = new Payload($decode);

        if (Constants::SCOPE_REFRESH !== $jwt->scopes) {
            throw new LoginException('refresh-token参数非法！', -2);
        }

        return $jwt->toArray();
    }

    /**
     * 刷新token
     * @param $refresh
     * @return string
     */
    public function refreshToken($refresh): string
    {
        if (empty($refresh)) {
            throw new LoginException('参数有误！');
        }

        $jwt = $this->decode($refresh);

        if (is_null($jwt)) {
            throw new LoginException('refresh-token参数有误！', -2);
        }

        if (Constants::SCOPE_REFRESH !== $jwt['scopes']) {
            throw new LoginException('refresh-token参数非法！', -2);
        }

        $data = $jwt['data'];
        return $data;
    }

    public function getUserId(): string
    {
        return $this->user_id;
    }

    public function getUser(): array
    {
        return $this->user;
    }

}