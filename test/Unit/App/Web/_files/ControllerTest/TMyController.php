<?php declare(strict_types=1);
namespace Morpho\Test\Unit\App\Web\ControllerTest;

trait TMyController {
    public $calledMethod;

    protected function returnNull() {
        $this->calledMethod = __FUNCTION__;
        return null;
    }

    protected function returnString() {
        $this->calledMethod = __FUNCTION__;
        return __FUNCTION__ . 'Called';
    }

    protected function returnJson() {
        $this->calledMethod = __FUNCTION__;
        return $this->mkJsonResult(__FUNCTION__ . 'Called');
    }

    protected function returnResponse() {
        $this->calledMethod = __FUNCTION__;
        return $this->mkResponse(null, __FUNCTION__ . 'Called');
    }

    protected function returnView() {
        $this->calledMethod = __FUNCTION__;
        return $this->mkViewResult('test', ['foo' => 'bar']);
    }

    protected function returnArray() {
        $this->calledMethod = __FUNCTION__;
        return ['foo' => 'bar'];
    }

    /*
        public function forward() {
            $this->forward(...$this->forwardTo);
        }

        public function redirectHasArgs() {
            $this->redirect('/some/page', $this->statusCode);
        }

        public function redirectNoArgs() {
            $this->redirect();
        }

        public function returnResponse() {
            return $this->returnResponse;
        }
*/
}
