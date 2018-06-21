<?php declare(strict_types=1);
namespace Morpho\Test\Unit\App\Web\ControllerTest;

trait TMyController {
    public $calledMethod;

    protected function returnNullAction() {
        $this->calledMethod = __FUNCTION__;
        return null;
    }

    protected function returnStringAction() {
        $this->calledMethod = __FUNCTION__;
        return __FUNCTION__ . 'Called';
    }

    protected function returnJsonAction() {
        $this->calledMethod = __FUNCTION__;
        return $this->mkJsonResult(__FUNCTION__ . 'Called');
    }

    protected function returnResponseAction() {
        $this->calledMethod = __FUNCTION__;
        return $this->mkResponse(null, __FUNCTION__ . 'Called');
    }

    protected function returnViewAction() {
        $this->calledMethod = __FUNCTION__;
        return $this->mkViewResult('test', ['foo' => 'bar']);
    }

    protected function returnArrayAction() {
        $this->calledMethod = __FUNCTION__;
        return ['foo' => 'bar'];
    }

    /*
        public function forwardAction() {
            $this->forward(...$this->forwardTo);
        }

        public function redirectHasArgsAction() {
            $this->redirect('/some/page', $this->statusCode);
        }

        public function redirectNoArgsAction() {
            $this->redirect();
        }

        public function returnResponseAction() {
            return $this->returnResponse;
        }
*/
}
