<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Exception\InvalidAccessException;
use App\Exception\InvalidArgumentsException;
use App\Exception\InvalidRequestMethodException;
use App\Logic\Admin\LoginLogic;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\AutoController;
use App\Validate\LoginValidate;
use App\Controller\Controller;

/**
 * @AutoController()
 * Class LoginController
 * @package App\Controller
 */
class LoginController extends Controller
{
    /**
     * @Inject()
     * @var LoginLogic
     */
    private $logic;

    /**
     * @ignore 登录
     * @return mixed
     */
    public function index()
    {

        if (!$this->isPost()) {
            throw new InvalidRequestMethodException();
        }

        $username = $this->request->post('username', '');//admin
        $password = $this->request->post('password', '');//123123

        $data = [
            'username' => $username,
            'password' => $password,
        ];

        $validate = new LoginValidate();

        if (!$validate->scene('login')->check($data)) {
            throw new InvalidAccessException($validate->getError());
        }

        $tokens = $this->logic->login($username, $password);

        return $this->response->success($tokens, 0, '登录成功！');

    }

    /**
     * @ignore 刷新token
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \Exception
     */
    public function refreshToken()
    {

        $refresh_token = $this->request->header('refresh-token', '');

        $data = [
            'refresh_token' => $refresh_token,
        ];

        $validate = new LoginValidate();

        if (!$validate->scene('refreshToken')->check($data)) {
            throw new InvalidArgumentsException($validate->getError());
        }

        $tokens = $this->logic->refreshToken($refresh_token);

        return $this->response->success($tokens, 0, '刷新成功！');

    }

//    public function captcha()
//    {
//
//    }

}