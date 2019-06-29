<?php
declare(strict_types=1);

namespace App\Controller;

use Hyperf\HttpServer\Annotation\AutoController;

/**
 * @AutoController()
 * Class BaseController
 * @package App\Controller
 */
class BaseController extends Controller
{

    public function isPost()
    {
        return $this->request->isMethod('post');
    }

    public function isGet()
    {
        return $this->request->isMethod('get');
    }

}