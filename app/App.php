<?php
namespace App;
use App\Controllers\HomeController;
use App\Core\BaseController;
use App\Core\Request;
use App\Core\Router;

class App extends BaseController {
    private $__controller;
    private $__action;
    private $__param = [];
    private $router;
    public function __construct()
    {
        $this->router = new Router();
        $this->handleUrl();
    }
    public function handleUrl(){
        try {
            $url = $this->router->handle($_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD']);
            $urlarr = array_values($url);
            if (!empty($urlarr[0])) {
                $this->__controller = $urlarr[0];
                // Kiểm tra file có tồn tại
                if (file_exists(path_root($this->__controller . '.php'))) {
                    require_once path_root($this->__controller . '.php');
                    // Kiểm tra class tồn tại
                    if (class_exists($this->__controller)) {
                        $this->__controller = new $this->__controller();
                    } else {
                         throw new \RuntimeException("{$this->__controller} does not exit",500);
                    }
                    //   xóa phần tử khi thực hiện xong
                    unset($urlarr[0]);
                } else {
                    throw new \RuntimeException("{$this->__controller} does not exit",500);
                }
            } else {
                throw new \RuntimeException('Page not found', 404);
            }
            // xử lý action
            if (isset($urlarr[1])) {
                $this->__action = $urlarr[1];
                //   xóa phần tử khi thực hiện xong
                unset($urlarr[1]);
            }
            // xử lý param

            $this->__param = array_values($urlarr);
            if (method_exists($this->__controller, $this->__action)) {
                // xử lý method
                $method = new \ReflectionMethod($this->__controller, $this->__action);
                $agr = [];
                foreach ($method->getParameters() as $ag){
                    if($ag->name == 'request'){
                        array_push($agr, new Request());
                    }else{
                        $agr = array_merge($agr, $this->__param);
                    }
                }
                $result = $this->__controller->{$this->__action}(...$agr);
                if (is_array($result) || is_object($result)) {
                    echo json_encode($result);
                } else {
                    echo $result;
                }
                exit();
            }else{
                $controller = serialize($this->__controller);
                throw new \RuntimeException("Method {$this->__action} does not exit in controller {$controller}",500);
            }
        }catch (\Throwable $e){
            $code = (int)$e->getCode();
            $code = $code ? $code:500;
            if(DEBUG_LOG) {
                $date = "\n\n[".date('Y-m-d H:i:s')."]: ";
                if (!file_exists(__DIR__ROOT .'/storage')) {
                    mkdir(__DIR__ROOT .'/storage', 0777, true);
                }
                file_put_contents(__DIR__ROOT .'/storage/debug.log',$date . $e, FILE_APPEND);
           }
           http_response_code($code);
           return $this->render_view("error.index",[
               "message" => $e->getMessage(),
               "line" => $e->getLine(),
               "file" => $e->getFile(),
               "code" => $code
           ]);
        }
    }
}