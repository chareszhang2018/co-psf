<?php
namespace Coroutine;

class Multi extends Task{

	protected $_callList = array();

	protected $_callRsp = array();

	const TASK_QUEUE = "multi";

	public function wrap($coroutine){
		$this->_callList[] = $coroutine;
		$taskId = \Coroutine::newTaskId();
		$coroutine = $this->multiCoroutine($taskId,$coroutine);
		\Coroutine::register($taskId,$coroutine);
		return \Coroutine::wrap($coroutine);
	}

	public function multiCoroutine($taskId,$coroutine){
		try{
			$resp = yield from $coroutine;
			$this->_callRsp[] = $resp;
			if(count($this->_callRsp) == count($this->_callList)){
				$this->executeCoroutine($this->_callRsp);
				$this->next();
			}
			\Coroutine::unregister($taskId);
		}catch(\Exception $e){
			$this->executeCoroutine(null,$e);
			$this->next();
		}
	}
}