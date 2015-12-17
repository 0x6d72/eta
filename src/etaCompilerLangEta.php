<?php
/**
 * The MIT License (MIT)
 * 
 * Copyright (c) 2014 0x6d72
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

class etaCompilerLangEtaFuncContext
{
	/**
	 * @var string
	 */
	protected $sName;

	/**
	 * @var etaCompilerRefPoint
	 */
	protected $oStartRefPoint;

	/**
	 * @var int
	 */
	protected $iParamCount = 0;

	/**
	 * @var int
	 */
	protected $iLocalCount = 0;

	/**
	 * @var array
	 */
	protected $aLocals = array();

	/**
	 * @param string $sName
	 * @param etaCompilerRefPoint $oStartRefPoint
	 * @return void
	 */
	public function __construct($sName, etaCompilerRefPoint $oStartRefPoint)
	{
		$this->sName = $sName;

		$this->oStartRefPoint = $oStartRefPoint;
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->sName;
	}

	/**
	 * @return etaCompilerRefPoint
	 */
	public function getStartRefPoint()
	{
		return $this->oStartRefPoint;
	}

	/**
	 * @return int
	 */
	public function getParamCount()
	{
		return $this->iParamCount;
	}

	/**
	 * @param int $iParamCount
	 * @return void
	 */
	public function setParamCount($iParamCount)
	{
		$this->iParamCount = $iParamCount;
	}

	/**
	 * @param string $sLocal
	 */
	public function addLocal($sLocal)
	{
		if(isset($this->aLocals[$sLocal]))
		{
			throw new Exception(sprintf(
				'cannot redeclare local "%s" in function "%s"',
				$sLocal,
				$this->sName
			));
		}

		$this->aLocals[$sLocal] = $this->iLocalCount++;
	}

	/**
	 * @return int
	 */
	public function getLocalCount()
	{
		return count($this->aLocals);
	}

	/**
	 * @param string $sLocal
	 * @return bool
	 */
	public function hasLocal($sLocal)
	{
		return isset($this->aLocals[$sLocal]);
	}

	/**
	 * @param string $sLocal
	 * @param bool $bIndirect
	 * @return etaCompilerAddr
	 */
	public function getLocalAddr($sLocal, $bIndirect = true)
	{
		if($this->hasLocal($sLocal))
		{
			return new etaCompilerAddrValue(
				$this->aLocals[$sLocal], $bIndirect, true
			);
		}

		throw new Exception(sprintf(
			'local "%s" not defined in function "%s"', $sLocal, $this->sName
		));
	}
}

class etaCompilerLangEtaFuncTable
{
	/**
	 * @var array
	 */
	protected $aContexts = array();

	/**
	 * @var etaCompilerLangEtaFuncContext
	 */
	protected $oCurrentContext = null;

	/**
	 * @param etaCompilerLangEtaFuncContext $oContext
	 * @return void
	 */
	public function addContext(etaCompilerLangEtaFuncContext $oContext)
	{
		$sName = $oContext->getName();

		if(isset($this->aContexts[$sName]))
		{
			throw new Exception(sprintf(
				'cannot redefine function "%s"', $sName
			));
		}

		$this->aContexts[$sName] = $oContext;
	}

	/**
	 * @param string $sName
	 * @return bool
	 */
	public function hasContext($sName)
	{
		return isset($this->aContexts[$sName]);
	}

	/**
	 * @param string $sName
	 * @return etaCompilerLangEtaFuncContext
	 */
	public function getContext($sName)
	{
		if($this->hasContext($sName))
		{
			return $this->aContexts[$sName];
		}

		throw new Exception(sprintf('function "%s" not defined', $sName));
	}

	/**
	 * @param etaCompilerLangEtaFuncContext $oContext
	 * @return void
	 */
	public function setCurrentContext(etaCompilerLangEtaFuncContext $oContext)
	{
		$this->oCurrentContext = $oContext;
	}

	/**
	 * @return bool
	 */
	public function hasCurrentContext()
	{
		return $this->oCurrentContext !== null;
	}

	/**
	 * @return etaCompilerLangEtaFuncContext
	 */
	public function getCurrentContext()
	{
		if($this->hasCurrentContext())
		{
			return $this->oCurrentContext;
		}

		throw new Exception('not in a function');
	}

	/**
	 * @return array
	 */
	public function getContexts()
	{
		return $this->aContexts;
	}
}

class etaCompilerLangEtaTreeCallback implements etaBnfTreeTraverseCallback
{
	/**
	 * @var etaCompilerContext
	 */
	protected $oContext;

	/**
	 * @var etaCompilerLangEtaFuncTable
	 */
	protected $oFuncTable;

	/**
	 * @var array
	 */
	protected $aInstNodes = array();

	/**
	 * @var array
	 */
	protected $aFuncNodes = array();

	/**
	 * @param etaCompilerContext $oContext
	 * @return void
	 */
	public function __construct(
		etaCompilerContext $oContext,
		etaCompilerLangEtaFuncTable $oFuncTable
	)
	{
		$this->oContext = $oContext;

		$this->oFuncTable = $oFuncTable;
	}

	/**
	 * @see etaBnfTreeTraverseCallback::handleNodePre()
	 */
	public function handleNodePre(etaBnfTreeNode $oNode, $iDepth)
	{
		if($oNode instanceof etaBnfTreeNodeBranch)
		{
			switch($oNode->getName())
			{
				case 'main':
				case 'instList1Opt':
				case 'instList1':
				case 'inst1':
				case 'separatedInst1':

					return true;

				case 'funcDef':

					$this->aFuncNodes[] = $oNode;

					$oContext = new etaCompilerLangEtaFuncContext(
						$oNode->getSubNode(0)->getRawValue(),
						$this->oContext->getRefPointProxy()
					);

					$this->oFuncTable->addContext($oContext);

					if($oNode->getSubNode(1)->hasSubNode(0))
					{
						$oParamListNode = $oNode->getSubNode(1)->getSubNode(0);

						$iParamCount = $oParamListNode->getSubNodeCount();

						$oContext->setParamCount($iParamCount);
					}

					break;

				case 'return':
				case 'if':
				case 'whilePre':
				case 'whilePost':
				case 'foreach':
				case 'skip':
				case 'readMulti':
				case 'readOne':
				case 'write':
				case 'break':
				case 'continue':
				case 'assignment':
				case 'expInst':

					$this->aInstNodes[] = $oNode;

					break;
			}
		}

		return false;
	}

	/**
	 * @see etaBnfTreeTraverseCallback::handleNodePost()
	 */
	public function handleNodePost(etaBnfTreeNode $oNode, $iDepth)
	{
	}

	/**
	 * @return array
	 */
	public function getInstNodes()
	{
		return $this->aInstNodes;
	}

	/**
	 * @return array
	 */
	public function getFuncNodes()
	{
		return $this->aFuncNodes;
	}
}

class etaCompilerLangEtaNodeCompiler
{
	/**
	 * @var etaCompilerContext
	 */
	protected $oContext;

	/**
	 * @var etaCompilerLangEtaFuncTable
	 */
	protected $oFuncTable;

	/**
	 * @param etaCompilerContext $oContext
	 * @param etaCompilerLangEtaFuncTable $oFuncTable
	 * @return void
	 */
	public function __construct(
		etaCompilerContext $oContext,
		etaCompilerLangEtaFuncTable $oFuncTable
	)
	{
		$this->oContext = $oContext;

		$this->oFuncTable = $oFuncTable;
	}

	/**
	 * @param array $aNodes
	 * @return void
	 */
	public function compileNodes(array $aNodes)
	{
		foreach($aNodes as $oNode)
		{
			$this->compileNode($oNode);
		}
	}

	/**
	 * @param etaBnfTreeNodeBranch $oNode
	 * @return void
	 */
	protected function compileNode(etaBnfTreeNodeBranch $oNode)
	{
		switch($oNode->getName())
		{
			case 'instList2Opt':
			case 'separatedInst2':
			case 'inst2':
			case 'block':

				if($oNode->hasSubNode(0))
				{
					$this->compileNode($oNode->getSubNode(0));
				}

				break;

			case 'instList2':

				$this->compileNode($oNode->getSubNode(0));

				$iCount = $oNode->getSubNodeCount();

				for($i=1;$i<$iCount;++$i)
				{
					$this->compileNode($oNode->getSubNode($i)->getSubNode(0));
				}

				break;

			case 'funcDef':

				$oContext = $this->oFuncTable->getContext(
					$oNode->getSubNode(0)->getRawValue()
				);

				$oContext->getStartRefPoint()->setRefPoint(
					$this->oContext->getNextInstRefPoint()
				);

				$this->oFuncTable->setCurrentContext($oContext);

				if($oNode->getSubNode(1)->hasSubNode(0))
				{
					$oParamListNode = $oNode->getSubNode(1)->getSubNode(0);

					$iParamCount = $oParamListNode->getSubNodeCount();

					$oContext->addLocal(
						$oParamListNode->getSubNode(0)->getRawValue()
					);

					for($i=1;$i<$iParamCount;++$i)
					{
						$oContext->addLocal(
							$oParamListNode->getSubNode($i)->getRawValue()
						);
					}
				}

				if($oNode->getSubNode(2)->getSubNode(0)->hasSubNode(0))
				{
					$oLocalListNode = $oNode->getSubNode(2)->getSubNode(0)->
						getSubNode(0)->getSubNode(0);

					$iLocalCount = $oLocalListNode->getSubNodeCount();

					for($i=0;$i<$iLocalCount;++$i)
					{
						$oLocalNode = $oLocalListNode->getSubNode($i);

						if($oLocalNode->getName() == 'separatedLocal')
						{
							$oLocalNode = $oLocalNode->getSubNode(0);
						}

						$oContext->addLocal(
							$oLocalNode->getSubNode(0)->getRawValue()
						);

						if($oLocalNode->hasSubNode(1))
						{
							$this->pushExp(
								$oLocalNode->getSubNode(1)->getSubNode(0)
							);
						}
						else
						{
							$this->oContext->addInst(new etaCompilerInst(
								etaOpcode::OP_PUSHNIL, new etaCompilerAddrNone
							));
						}
					}
				}

				$this->compileNode($oNode->getSubNode(2)->getSubNode(1));

				$bHasReturn = false;

				$oInstListNode = $oNode->getSubNode(2)->getSubNode(1);

				if($oInstListNode->hasSubNode(0))
				{
					$oInstListNode = $oInstListNode->getSubNode(0);

					$iCount = $oInstListNode->getSubNodeCount();

					for($i=0;$i<$iCount;++$i)
					{
						$oInst = $oInstListNode->getSubNode($i);

						if($oInst->getName() == 'separatedInst2')
						{
							$oInst = $oInst->getSubNode(0);
						}

						if($oInst->getSubNode(0)->getName() == 'return')
						{
							$bHasReturn = true;

							break;
						}
					}
				}

				if(!$bHasReturn)
				{
					$this->oContext->addInst(new etaCompilerInst(
						etaOpcode::OP_PUSHNIL, new etaCompilerAddrNone
					));

					$this->oContext->addInst(new etaCompilerInst(
						etaOpcode::OP_RET, new etaCompilerAddrNone
					));
				}

				break;

			case 'return':

				$bValid = false;

				$oParent = $oNode->getParentNode();

				while($oParent !== null)
				{
					if($oParent->getName() == 'funcDef')
					{
						$bValid = true;
						break;
					}

					$oParent = $oParent->getParentNode();
				}

				if($bValid)
				{
					if($oNode->hasSubNode(0))
					{
						$this->pushExp($oNode->getSubNode(0));
					}
					else
					{
						$this->oContext->addInst(new etaCompilerInst(
							etaOpcode::OP_PUSHNIL, new etaCompilerAddrNone
						));
					}

					$this->oContext->addInst(new etaCompilerInst(
						etaOpcode::OP_RET, new etaCompilerAddrNone
					));
				}
				else
				{
					throw new Exception('cannot use return outside function');
				}

				break;

			case 'if':

				$oEndRefPoint = $this->oContext->getRefPointProxy();

				$this->pushExp($oNode->getSubNode(0));

				$this->oContext->addInst(new etaCompilerInst(
					etaOpcode::OP_JF, new etaCompilerAddrRefPoint(
						$oEndRefPoint, false
					)
				));

				$this->compileNode($oNode->getSubNode(1));

				if($oNode->hasSubNode(2))
				{
					$oOldEndRefPoint = $oEndRefPoint;

					$oEndRefPoint = $this->oContext->getRefPointProxy();

					$this->oContext->addInst(new etaCompilerInst(
						etaOpcode::OP_JMP, new etaCompilerAddrRefPoint(
							$oEndRefPoint, false
						)
					));

					$oOldEndRefPoint->setRefPoint(
						$this->oContext->getNextInstRefPoint()
					);

					$this->compileNode($oNode->getSubNode(2)->getSubNode(0));
				}

				$oEndRefPoint->setRefPoint(
					$this->oContext->getNextInstRefPoint()
				);

				break;

			case 'whilePre':

				$oEndRefPoint = $this->oContext->getRefPointProxy();
				$oStartRefPoint = $this->oContext->getNextInstRefPoint();

				$oRefPointSet = new etaCompilerRefPointSet();
				$oRefPointSet->addRefPoint($oStartRefPoint);
				$oRefPointSet->addRefPoint($oEndRefPoint);

				$oNode->setProcessedValue($oRefPointSet);

				$this->pushExp($oNode->getSubNode(0));

				$this->oContext->addInst(new etaCompilerInst(
					etaOpcode::OP_JF, new etaCompilerAddrRefPoint(
						$oEndRefPoint, false
					)
				));

				$this->compileNode($oNode->getSubNode(1));

				$this->oContext->addInst(new etaCompilerInst(
					etaOpcode::OP_JMP, new etaCompilerAddrRefPoint(
						$oStartRefPoint, false
					)
				));

				$oEndRefPoint->setRefPoint(
					$this->oContext->getNextInstRefPoint()
				);

				break;

			case 'whilePost':

				$oStartRefPoint = $this->oContext->getNextInstRefPoint();
				$oEndRefPoint = $this->oContext->getRefPointProxy();
				$oConditionRefPoint = $this->oContext->getRefPointProxy();

				$oRefPointSet = new etaCompilerRefPointSet();
				$oRefPointSet->addRefPoint($oStartRefPoint);
				$oRefPointSet->addRefPoint($oEndRefPoint);
				$oRefPointSet->addRefPoint($oConditionRefPoint);

				$oNode->setProcessedValue($oRefPointSet);

				$this->compileNode($oNode->getSubNode(0));

				$oConditionRefPoint->setRefPoint(
					$this->oContext->getNextInstRefPoint()
				);

				$this->pushExp($oNode->getSubNode(1));

				$this->oContext->addInst(new etaCompilerInst(
					etaOpcode::OP_JT, new etaCompilerAddrRefPoint(
						$oStartRefPoint, false
					)
				));

				$oEndRefPoint->setRefPoint(
					$this->oContext->getNextInstRefPoint()
				);

				break;

			case 'foreach':

				$this->pushExp($oNode->getSubNode(2));

				$this->oContext->addInst(new etaCompilerInst(
					etaOpcode::OP_RES, new etaCompilerAddrIndirect(-1)
				));

				$oStartRefPoint = $this->oContext->addInstRefPoint(
					new etaCompilerInst(
						etaOpcode::OP_NXT, new etaCompilerAddrIndirect(-1)
					)
				);

				$this->oContext->addInst(new etaCompilerInst(
					etaOpcode::OP_PUSH, new etaCompilerAddrIndirect(-2)
				));

				$this->writeTo($oNode->getSubNode(0));
				$this->writeTo($oNode->getSubNode(1));

				$this->oContext->addInst(new etaCompilerInst(
					etaOpcode::OP_PUSHNIL, new etaCompilerAddrNone
				));

				$this->oContext->addInst(new etaCompilerInst(
					etaOpcode::OP_EQ, new etaCompilerAddrNone
				));

				$oEndRefPoint = $this->oContext->getRefPointProxy();

				$this->oContext->addInst(new etaCompilerInst(
					etaOpcode::OP_JT, new etaCompilerAddrRefPoint(
						$oEndRefPoint, false
					)
				));

				$oRefPointSet = new etaCompilerRefPointSet();
				$oRefPointSet->addRefPoint($oStartRefPoint);
				$oRefPointSet->addRefPoint($oEndRefPoint);

				$oNode->setProcessedValue($oRefPointSet);

				$this->compileNode($oNode->getSubNode(3));

				$this->oContext->addInst(new etaCompilerInst(
					etaOpcode::OP_JMP, new etaCompilerAddrRefPoint(
						$oStartRefPoint, false
					)
				));

				$oEndRefPoint->setRefPoint($this->oContext->addInstRefPoint(
					new etaCompilerInst(
						etaOpcode::OP_POP, new etaCompilerAddrDirect(1)
					)
				));

				break;

			case 'skip':

				$this->pushExp($oNode->getSubNode(0));

				$oEndRefPoint = $this->oContext->getRefPointProxy();

				$oStartRefPoint = $this->oContext->addInstRefPoint(
					new etaCompilerInst(
						etaOpcode::OP_PUSH, new etaCompilerAddrDirect(0)
					)
				);

				$this->oContext->addInst(new etaCompilerInst(
					etaOpcode::OP_PUSH, new etaCompilerAddrIndirect(-2)
				));

				$this->oContext->addInst(new etaCompilerInst(
					etaOpcode::OP_LESS, new etaCompilerAddrNone
				));

				$this->oContext->addInst(new etaCompilerInst(
					etaOpcode::OP_JF, new etaCompilerAddrRefPoint(
						$oEndRefPoint, false
					)
				));

				$this->oContext->addInst(new etaCompilerInst(
					etaOpcode::OP_PUSH, new etaCompilerAddrDirect(1)
				));

				$this->oContext->addInst(new etaCompilerInst(
					etaOpcode::OP_SUB, new etaCompilerAddrNone
				));

				$this->oContext->addInst(new etaCompilerInst(
					etaOpcode::OP_IN, new etaCompilerAddrNone
				));

				$this->oContext->addInst(new etaCompilerInst(
					etaOpcode::OP_PUSHNIL, new etaCompilerAddrNone
				));

				$this->oContext->addInst(new etaCompilerInst(
					etaOpcode::OP_EQ, new etaCompilerAddrNone
				));

				$this->oContext->addInst(new etaCompilerInst(
					etaOpcode::OP_JF, new etaCompilerAddrRefPoint(
						$oStartRefPoint, false
					)
				));

				$oEndRefPoint->setRefPoint($this->oContext->addInstRefPoint(
					new etaCompilerInst(
						etaOpcode::OP_POP, new etaCompilerAddrDirect(1)
					)
				));

				break;

			case 'readMulti':

				$oStartRefPoint = $this->oContext->addInstRefPoint(
					new etaCompilerInst(
						etaOpcode::OP_IN, new etaCompilerAddrNone
					)
				);

				$this->oContext->addInst(new etaCompilerInst(
					etaOpcode::OP_PUSH, new etaCompilerAddrIndirect(-1)
				));

				$this->oContext->addInst(new etaCompilerInst(
					etaOpcode::OP_PUSHNIL, new etaCompilerAddrNone
				));

				$this->oContext->addInst(new etaCompilerInst(
					etaOpcode::OP_EQ, new etaCompilerAddrNone
				));

				$this->oContext->addInst(new etaCompilerInst(
					etaOpcode::OP_PUSH, new etaCompilerAddrIndirect(-2)
				));

				$this->writeTo($oNode->getSubNode(0));

				$this->oContext->addInst(new etaCompilerInst(
					etaOpcode::OP_RPL, new etaCompilerAddrDirect(-2)
				));

				$oEndRefPoint = $this->oContext->getRefPointProxy();

				$this->oContext->addInst(new etaCompilerInst(
					etaOpcode::OP_JT, new etaCompilerAddrRefPoint(
						$oEndRefPoint, false
					)
				));

				$oRefPointSet = new etaCompilerRefPointSet();
				$oRefPointSet->addRefPoint($oStartRefPoint);
				$oRefPointSet->addRefPoint($oEndRefPoint);

				$oNode->setProcessedValue($oRefPointSet);

				$this->compileNode($oNode->getSubNode(1));

				$this->oContext->addInst(new etaCompilerInst(
					etaOpcode::OP_JMP, new etaCompilerAddrRefPoint(
						$oStartRefPoint, false
					)
				));

				$oEndRefPoint->setRefPoint(
					$this->oContext->getNextInstRefPoint()
				);

				break;

			case 'readOne':

				$this->oContext->addInst(new etaCompilerInst(
					etaOpcode::OP_IN, new etaCompilerAddrNone
				));

				$this->writeTo($oNode->getSubNode(0));

				break;

			case 'write':

				$oAddr = $this->getExpAddr($oNode->getSubNode(0));

				if($oAddr instanceof etaCompilerAddr)
				{
					$this->oContext->addInst(new etaCompilerInst(
						etaOpcode::OP_OUT, $oAddr
					));
				}
				else
				{
					$this->pushExp($oNode->getSubNode(0));

					$this->oContext->addInst(new etaCompilerInst(
						etaOpcode::OP_OUT, new etaCompilerAddrIndirect(-1)
					));

					$this->oContext->addInst(new etaCompilerInst(
						etaOpcode::OP_POP, new etaCompilerAddrDirect(1)
					));
				}

				break;

			case 'break':

				$oRefPoint = null;

				$oParent = $oNode->getParentNode();

				while($oParent !== null)
				{
					$sName = $oParent->getName();

					if($sName == 'whilePre' || $sName == 'whilePost'
						|| $sName == 'readMulti' || $sName == 'foreach')
					{
						$oRefPoint = $oParent->getProcessedValue()->
							getRefPoint(1);
					}

					$oParent = $oParent->getParentNode();
				}

				if($oRefPoint !== null)
				{
					$this->oContext->addInst(new etaCompilerInst(
						etaOpcode::OP_JMP, new etaCompilerAddrRefPoint(
							$oRefPoint, false
						)
					));
				}
				else
				{
					throw new Exception('cannot use break outside loop');
				}

				break;

			case 'continue':

				$oRefPoint = null;

				$oParent = $oNode->getParentNode();

				while($oParent !== null)
				{
					$sName = $oParent->getName();

					if($sName == 'whilePre' || $sName == 'readMulti'
						|| $sName == 'foreach')
					{
						$oRefPoint = $oParent->getProcessedValue()->
							getRefPoint(0);
					}
					elseif($sName == 'whilePost')
					{
						$oRefPoint = $oParent->getProcessedValue()->
							getRefPoint(2);
					}

					$oParent = $oParent->getParentNode();
				}

				if($oRefPoint !== null)
				{
					$this->oContext->addInst(new etaCompilerInst(
						etaOpcode::OP_JMP, new etaCompilerAddrRefPoint(
							$oRefPoint, false
						)
					));
				}
				else
				{
					throw new Exception('cannot use continue outside loop');
				}

				break;

			case 'assignment':

				$this->pushExp($oNode->getSubNode(1));
				$this->writeTo($oNode->getSubNode(0));

				break;

			case 'expInst':

				$this->pushExp($oNode->getSubNode(0));

				$this->oContext->addInst(new etaCompilerInst(
					etaOpcode::OP_POP, new etaCompilerAddrDirect(1)
				));

				break;
		}
	}

	/**
	 * @param etaBnfTreeNodeBranch $oNode
	 * @return void
	 */
	protected function writeTo(etaBnfTreeNodeBranch $oNode)
	{
		$iCount = $oNode->getSubNodeCount();

		$sName = $oNode->getSubNode(0)->getRawValue();

		$bIsLocal = false;
		$oLocalAddr = null;

		if($this->oFuncTable->hasCurrentContext())
		{
			$oFuncContext = $this->oFuncTable->getCurrentContext();

			if($oFuncContext->hasLocal($sName))
			{
				$bIsLocal = true;
				$oLocalAddr = $oFuncContext->getLocalAddr($sName);
			}
		}

		if($iCount > 1)
		{
			if($bIsLocal)
			{
				if($oLocalAddr instanceof etaCompilerAddrType)
				{
					$oLocalAddr->setIsIndirect(true);
					$oLocalAddr->setIsRelative(true);
				}

				$this->oContext->addInst(new etaCompilerInst(
					etaOpcode::OP_PUSH, $oLocalAddr
				));
			}
			else
			{
				$oRefPoint = $this->oContext->getRefPointVar($sName);

				$this->oContext->addInst(new etaCompilerInst(
					etaOpcode::OP_PUSH, new etaCompilerAddrRefPoint(
						$oRefPoint, true
					)
				));
			}

			for($i=1;$i<$iCount-1;++$i)
			{
				$this->pushExp($oNode->getSubNode($i)->getSubNode(0));

				$this->oContext->addInst(new etaCompilerInst(
					etaOpcode::OP_GET, new etaCompilerAddrIndirect(-2)
				));

				$this->oContext->addInst(new etaCompilerInst(
					etaOpcode::OP_RPL, new etaCompilerAddrDirect(-2)
				));
			}

			$this->pushExp($oNode->getSubNode($iCount - 1)->getSubNode(0));

			$this->oContext->addInst(new etaCompilerInst(
				etaOpcode::OP_PUSH, new etaCompilerAddrIndirect(-3)
			));

			$this->oContext->addInst(new etaCompilerInst(
				etaOpcode::OP_PUT, new etaCompilerAddrIndirect(-3)
			));

			$this->oContext->addInst(new etaCompilerInst(
				etaOpcode::OP_POP, new etaCompilerAddrDirect(2)
			));
		}
		else
		{
			if($bIsLocal)
			{
				if($oLocalAddr instanceof etaCompilerAddrType)
				{
					$oLocalAddr->setIsIndirect(false);
					$oLocalAddr->setIsRelative(true);
				}

				$this->oContext->addInst(new etaCompilerInst(
					etaOpcode::OP_RPL, $oLocalAddr
				));
			}
			else
			{
				$oRefPoint = $this->oContext->getRefPointVar($sName);

				$this->oContext->addInst(new etaCompilerInst(
					etaOpcode::OP_RPL, new etaCompilerAddrRefPoint(
						$oRefPoint, false
					)
				));
			}
		}
	}

	/**
	 * @param etaBnfTreeNode $oNode
	 * @return void
	 */
	protected function pushExp(etaBnfTreeNode $oNode)
	{
		if(!($oNode instanceof etaBnfTreeNodeBranch))
		{
			return;
		}

		$sName = $oNode->getName();

		switch($sName)
		{
			case 'exp':
			case 'operand':
			case 'separatedExp':
			case 'parenthesisExp':
			case 'orExpTail':
			case 'andExpTail':
				$this->pushExp($oNode->getSubNode(0));
				break;

			case 'negatedOperand':

				$this->pushExp($oNode->getSubNode(0));

				$this->oContext->addInst(new etaCompilerInst(
					etaOpcode::OP_PUSH, new etaCompilerAddrDirect(0)
				));

				$this->oContext->addInst(new etaCompilerInst(
					etaOpcode::OP_EQ, new etaCompilerAddrNone
				));

				break;

			case 'negativeOperand':

				$this->pushExp($oNode->getSubNode(0));

				$this->oContext->addInst(new etaCompilerInst(
					etaOpcode::OP_PUSH, new etaCompilerAddrDirect(-1)
				));

				$this->oContext->addInst(new etaCompilerInst(
					etaOpcode::OP_MUL, new etaCompilerAddrNone
				));

				break;

			case 'sizeOperand':

				$this->pushExp($oNode->getSubNode(0));

				$this->oContext->addInst(new etaCompilerInst(
					etaOpcode::OP_SIZE, new etaCompilerAddrIndirect(-1)
				));

				$this->oContext->addInst(new etaCompilerInst(
					etaOpcode::OP_RPL, new etaCompilerAddrDirect(-2)
				));

				break;

			case 'orExp':
			case 'andExp':

				if($sName == 'orExp')
				{
					$iStartVal = 1;
					$iEndVal = 0;
					$iJmpOpcode = etaOpcode::OP_JT;
				}
				else
				{
					$iStartVal = 0;
					$iEndVal = 1;
					$iJmpOpcode = etaOpcode::OP_JF;
				}

				$iCount = $oNode->getSubNodeCount();

				if($iCount > 1)
				{
					$oEndRefPoint = $this->oContext->getRefPointProxy();

					$this->oContext->addInst(new etaCompilerInst(
						etaOpcode::OP_PUSH, new etaCompilerAddrDirect(
							$iStartVal
						)
					));

					for($i=0;$i<$iCount;++$i)
					{
						$this->pushExp($oNode->getSubNode($i));

						$this->oContext->addInst(new etaCompilerInst(
							$iJmpOpcode, new etaCompilerAddrRefPoint(
								$oEndRefPoint, false
							)
						));
					}

					$this->oContext->addInst(new etaCompilerInst(
						etaOpcode::OP_POP, new etaCompilerAddrDirect(1)
					));

					$this->oContext->addInst(new etaCompilerInst(
						etaOpcode::OP_PUSH, new etaCompilerAddrDirect($iEndVal)
					));

					$oEndRefPoint->setRefPoint(
						$this->oContext->getNextInstRefPoint()
					);
				}
				else
				{
					$this->pushExp($oNode->getSubNode(0));
				}

				break;

			case 'cmpExp1':

				$this->pushExp($oNode->getSubNode(0));

				$iCount = $oNode->getSubNodeCount();

				if($iCount > 1)
				{
					for($i=1;$i<$iCount;++$i)
					{
						$this->pushExp(
							$oNode->getSubNode($i)->getSubNode(1)
						);

						$this->oContext->addInst(new etaCompilerInst(
							etaOpcode::OP_EQ, new etaCompilerAddrNone
						));

						$sOpName = $oNode->getSubNode($i)->getSubNode(0)->
							getName();

						if($sOpName == 'cmpOpUneq')
						{
							$this->oContext->addInst(new etaCompilerInst(
								etaOpcode::OP_PUSH, new etaCompilerAddrDirect(0)
							));

							$this->oContext->addInst(new etaCompilerInst(
								etaOpcode::OP_EQ, new etaCompilerAddrNone
							));
						}
					}
				}

				break;

			case 'cmpExp2':

				$this->pushExp($oNode->getSubNode(0));

				$iCount = $oNode->getSubNodeCount();

				if($iCount > 1)
				{
					for($i=1;$i<$iCount;++$i)
					{
						$this->pushExp(
							$oNode->getSubNode($i)->getSubNode(1)
						);

						$sOpName = $oNode->getSubNode($i)->getSubNode(0)->
							getName();

						switch($sOpName)
						{
							case 'cmpOpLess':

								$this->oContext->addInst(new etaCompilerInst(
								etaOpcode::OP_LESS, new etaCompilerAddrNone
								));

								break;

							case 'cmpOpLessEq':

								/**
								 * a <= b == !(b < a)
								 *
								 * push   -1
								 * push   -3
								 * less
								 * push  #0
								 * eq
								 * rpl   #-3
								 * pop   #1
								 */

								$this->oContext->addInst(new etaCompilerInst(
								etaOpcode::OP_PUSH,
								new etaCompilerAddrIndirect(-1)
								));

								$this->oContext->addInst(new etaCompilerInst(
									etaOpcode::OP_PUSH,
									new etaCompilerAddrIndirect(-3)
								));

								$this->oContext->addInst(new etaCompilerInst(
									etaOpcode::OP_LESS, new etaCompilerAddrNone
								));

								$this->oContext->addInst(new etaCompilerInst(
									etaOpcode::OP_PUSH,
									new etaCompilerAddrDirect(0)
								));

								$this->oContext->addInst(new etaCompilerInst(
									etaOpcode::OP_EQ, new etaCompilerAddrNone
								));

								$this->oContext->addInst(new etaCompilerInst(
									etaOpcode::OP_RPL,
									new etaCompilerAddrDirect(-3)
								));

								$this->oContext->addInst(new etaCompilerInst(
									etaOpcode::OP_POP,
									new etaCompilerAddrDirect(1)
								));

								break;

							case 'cmpOpGreater':

								/**
								 * a > b == b < a
								 *
								 * push   -1
								 * push   -3
								 * less
								 * rpl   #-3
								 * pop   #1
								 */

								$this->oContext->addInst(new etaCompilerInst(
								etaOpcode::OP_PUSH,
								new etaCompilerAddrIndirect(-1)
								));

								$this->oContext->addInst(new etaCompilerInst(
									etaOpcode::OP_PUSH,
									new etaCompilerAddrIndirect(-3)
								));

								$this->oContext->addInst(new etaCompilerInst(
									etaOpcode::OP_LESS, new etaCompilerAddrNone
								));

								$this->oContext->addInst(new etaCompilerInst(
									etaOpcode::OP_RPL,
									new etaCompilerAddrDirect(-3)
								));

								$this->oContext->addInst(new etaCompilerInst(
									etaOpcode::OP_POP,
									new etaCompilerAddrDirect(1)
								));

								break;

							case 'cmpOpGreaterEq':

								/**
								 * a >= b == !(a < b)
								 *
								 * less
								 * push  #0
								 * eq
								 */

								$this->oContext->addInst(new etaCompilerInst(
								etaOpcode::OP_LESS, new etaCompilerAddrNone
								));

								$this->oContext->addInst(new etaCompilerInst(
									etaOpcode::OP_PUSH,
									new etaCompilerAddrDirect(0)
								));

								$this->oContext->addInst(new etaCompilerInst(
									etaOpcode::OP_EQ, new etaCompilerAddrNone
								));

								break;
						}
					}
				}

				break;

			case 'mathExp1':
			case 'mathExp2':

				$aMapping = array(
					'mathOpAdd' => etaOpcode::OP_ADD,
					'mathOpSub' => etaOpcode::OP_SUB,
					'mathOpMul' => etaOpcode::OP_MUL,
					'mathOpDiv' => etaOpcode::OP_DIV
				);

				$this->pushExp($oNode->getSubNode(0));

				$iCount = $oNode->getSubNodeCount();

				if($iCount > 1)
				{
					for($i=1;$i<$iCount;++$i)
					{
						$this->pushExp(
							$oNode->getSubNode($i)->getSubNode(1)
						);

						$sOpName = $oNode->getSubNode($i)->getSubNode(0)->
							getName();

						$this->oContext->addInst(new etaCompilerInst(
							$aMapping[$sOpName], new etaCompilerAddrNone
						));
					}
				}

				break;

			case 'concatExp':

				$this->pushExp($oNode->getSubNode(0));

				$iCount = $oNode->getSubNodeCount();

				if($iCount > 1)
				{
					for($i=1;$i<$iCount;++$i)
					{
						$this->pushExp(
							$oNode->getSubNode($i)->getSubNode(0)
						);
					}

					$this->oContext->addInst(new etaCompilerInst(
						etaOpcode::OP_CONCAT, new etaCompilerAddrDirect($iCount)
					));
				}

				break;

			case 'doubleString':
			case 'singleString':
			case 'longString':
			case 'bool':
			case 'floatNumber':
			case 'hexIntNumber':
			case 'binIntNumber':
			case 'octIntNumber':
			case 'decIntNumber':

				$this->oContext->addInst(new etaCompilerInst(
					etaOpcode::OP_PUSH, $this->getExpAddr($oNode)
				));

				break;

			case 'table':

				$iCount = $this->pushExpList($oNode->getSubNode(0));

				$this->oContext->addInst(new etaCompilerInst(
					etaOpcode::OP_TAB, new etaCompilerAddrDirect($iCount)
				));

				break;

			case 'funcCall':

				$sName = $oNode->getSubNode(0)->getRawValue();

				if($this->oFuncTable->hasContext($sName))
				{
					$oFunc = $this->oFuncTable->getContext($sName);

					$iParamCount = $oFunc->getParamCount();

					$iCount = $this->pushExpList(
						$oNode->getSubNode(1), $iParamCount
					);

					for($i=$iCount;$i<$iParamCount;++$i)
					{
						$this->oContext->addInst(new etaCompilerInst(
							etaOpcode::OP_PUSHNIL, new etaCompilerAddrNone
						));
					}

					$this->oContext->addInst(new etaCompilerInst(
						etaOpcode::OP_PUSH, new etaCompilerAddrRefPoint(
							$oFunc->getStartRefPoint(), false
						)
					));

					$this->oContext->addInst(new etaCompilerInst(
						etaOpcode::OP_CALL, new etaCompilerAddrDirect(
							$iParamCount
						)
					));
				}
				else
				{
					$iCount = $this->pushExpList($oNode->getSubNode(1));

					$oRefPoint = $this->oContext->getRefPointVal($sName);

					$this->oContext->addInst(new etaCompilerInst(
						etaOpcode::OP_PUSH, new etaCompilerAddrRefPoint(
							$oRefPoint, true
						)
					));

					$this->oContext->addInst(new etaCompilerInst(
						etaOpcode::OP_SYS, new etaCompilerAddrDirect($iCount)
					));
				}

				break;

			case 'variable':

				$this->oContext->addInst(new etaCompilerInst(
					etaOpcode::OP_PUSH, $this->getExpAddr($oNode, true)
				));

				$iCount = $oNode->getSubNodeCount();

				if($iCount > 1)
				{
					for($i=1;$i<$iCount;++$i)
					{
						$this->pushExp($oNode->getSubNode($i)->getSubNode(0));

						$this->oContext->addInst(new etaCompilerInst(
							etaOpcode::OP_GET, new etaCompilerAddrIndirect(-2)
						));

						$this->oContext->addInst(new etaCompilerInst(
							etaOpcode::OP_RPL, new etaCompilerAddrDirect(-2)
						));
					}
				}

				break;

			default:

				$this->oContext->addInst(new etaCompilerInst(
					etaOpcode::OP_PUSHNIL, new etaCompilerAddrNone
				));

				break;
		}
	}

	/**
	 * @param etaBnfTreeNode $oNode
	 * @param bool $bIndexedVar
	 * @return etaCompilerAddr
	 */
	protected function getExpAddr(etaBnfTreeNode $oNode, $bIndexedVar = false)
	{
		if(!($oNode instanceof etaBnfTreeNodeBranch))
		{
			return null;
		}

		switch($oNode->getName())
		{
			case 'exp':
			case 'operand':
			case 'parenthesisExp':

				return $this->getExpAddr($oNode->getSubNode(0));

			case 'orExp':
			case 'andExp':
			case 'cmpExp1':
			case 'cmpExp2':
			case 'mathExp1':
			case 'mathExp2':
			case 'concatExp':

				if($oNode->getSubNodeCount() == 1)
				{
					return $this->getExpAddr($oNode->getSubNode(0));
				}

				break;

			case 'floatNumber':

				return new etaCompilerAddrRefPoint(
					$this->oContext->getRefPointVal((float) sprintf(
						'%s.%s',
						$oNode->getSubNode(0)->getRawValue(),
						$oNode->getSubNode(1)->getRawValue()
					)),
					true
				);

			case 'hexIntNumber':

				return new etaCompilerAddrDirect(
					(int) hexdec($oNode->getRawValue())
				);

			case 'binIntNumber':

				return new etaCompilerAddrDirect(
					(int) bindec($oNode->getRawValue())
				);

			case 'octIntNumber':

				return new etaCompilerAddrDirect(
					(int) octdec($oNode->getRawValue())
				);

			case 'decIntNumber':

				return new etaCompilerAddrDirect((int) $oNode->getRawValue());

			case 'doubleString':
			case 'singleString':
			case 'longString':

				return new etaCompilerAddrRefPoint(
					$this->oContext->getRefPointVal(
						$this->replaceEscapeSequences($oNode->getRawValue())
					), true
				);

				break;

			case 'bool':

				return  new etaCompilerAddrDirect(
					$oNode->getSubNode(0)->getName() == 'true' ? 1 : 0
				);

			case 'variable':

				if($oNode->getSubNodeCount() == 1 || $bIndexedVar)
				{
					$sName = $oNode->getSubNode(0)->getRawValue();

					if($this->oFuncTable->hasCurrentContext())
					{
						$oFuncContext = $this->oFuncTable->getCurrentContext();

						if($oFuncContext->hasLocal($sName))
						{
							$bIsLocal = true;

							return $oFuncContext->getLocalAddr($sName, true);
						}
					}

					return new etaCompilerAddrRefPoint(
						$this->oContext->getRefPointVar($sName), true
					);
				}

				break;
		}

		return null;
	}

	/**
	 * @param etaBnfTreeNodeBranch $oNode
	 * @param int $iMax
	 * @return int
	 */
	protected function pushExpList(etaBnfTreeNodeBranch $oNode, $iMax = 0)
	{
		if($oNode->getName() == 'expListOpt')
		{
			if($oNode->getSubNodeCount() == 0)
			{
				return 0;
			}

			$oNode = $oNode->getSubNode(0);
		}

		$iCount = $oNode->getSubNodeCount();

		if($iMax > 0)
		{
			$iCount = $iCount < $iMax ? $iCount : $iMax;
		}

		for($i=0;$i<$iCount;++$i)
		{
			$this->pushExp($oNode->getSubNode($i));
		}

		return $iCount;
	}

	/**
	 * @param string $sStr
	 * @return string
	 */
	protected function replaceEscapeSequences($sStr)
	{
		$sStr = str_replace(
			array('\r', '\n', '\t'),
			array("\r", "\n", "\t"),
			$sStr
		);

		while(preg_match('@\\\\x([\\da-fA-F]{2})@', $sStr, $aMatches, PREG_OFFSET_CAPTURE) > 0)
		{
			$sStr = substr_replace($sStr, chr(hexdec($aMatches[1][0])), $aMatches[0][1], strlen($aMatches[0][0]));
		}

		return $sStr;
	}
}

class etaCompilerLangEta extends etaCompilerLangDefault
{
	/**
	 * @see etaCompilerLangEta::getBnf()
	 */
	public function getBnf()
	{
		return '

main = <spaceOpt>n <instList1Opt> <spaceOpt>n

inst1 = <return if whilePre whilePost foreach skip readMulti readOne write break
		continue assignment funcDef expInst>

instList1 = <inst1> <separatedInst1>*
instList1Opt = <instList1>?
separatedInst1 = <spaceOpt>n <inst1>


inst2 = <return if whilePre whilePost foreach skip readMulti readOne write break
		continue assignment expInst>

instList2 = <inst2> <separatedInst2>*
instList2Opt = <instList2>?
separatedInst2 = <spaceOpt>n <inst2>

funcDef = <token> <spaceOpt>n "("n <spaceOpt>n <tokenListOpt> <spaceOpt>n ")"n
			<spaceOpt>n <funcBlock>

funcBlock = "{"n <spaceOpt>n <localDefOpt> <spaceOpt>n <instList2Opt>
			<spaceOpt>n "}"n

localDef = "local"n <space>n <localList>
localDefOpt = <localDef>?

local = <token> <localInit>?
localInit = <spaceOpt>n "="n <spaceOpt>n <exp>
localList = <local> <separatedLocal>*
separatedLocal = <spaceOpt>n ","n <spaceOpt>n <local>

block = "{"n <spaceOpt>n <instList2Opt> <spaceOpt>n "}"n

return = "return"n <spaceOpt>n <exp>?

if = "if"n <spaceOpt>n <exp> <spaceOpt>n <block> <else>?
else = <spaceOpt>n "else"n <spaceOpt>n <block>

whilePre = "while"n <spaceOpt>n <exp> <spaceOpt>n <block>
whilePost = <block> <spaceOpt>n "while"n <spaceOpt>n <exp>

foreach = "foreach"n <spaceOpt>n <variable> <spaceOpt>n ","n <spaceOpt>n
			<variable> <spaceOpt>n "in"n <spaceOpt>n <exp> <spaceOpt>n <block>

skip = "skip"n <spaceOpt>n <exp>

read = <readMulti readOne>
readMulti = "read"n <spaceOpt>n <variable> <spaceOpt>n <block>
readOne = "read"n <spaceOpt>n <variable>

write = "write"n <spaceOpt>n <exp>

break = "break"n

continue = "continue"n

assignment = <variable> <spaceOpt>n "="n <spaceOpt>n <exp>

expInst = <exp>

exp = <orExp>
parenthesisExp = "("n <spaceOpt>n <exp> <spaceOpt>n ")"n

expList = <exp> <separatedExp>*
expListOpt = <expList>?
separatedExp = <spaceOpt>n ","n <spaceOpt>n <exp>

orExp = <andExp> <orExpTail>*
orExpTail = <spaceOpt>n "||"n <spaceOpt>n <andExp>

andExp = <cmpExp1> <andExpTail>*
andExpTail = <spaceOpt>n "&&"n <spaceOpt>n <cmpExp1>

cmpExp1 = <cmpExp2> <cmpExp1Tail>*
cmpExp1Tail = <spaceOpt>n <cmpOpEq cmpOpUneq> <spaceOpt>n <cmpExp2>

cmpOpEq = "=="n
cmpOpUneq = "!="n

cmpExp2 = <mathExp1> <cmpExp2Tail>*
cmpExp2Tail = <spaceOpt>n <cmpOpLessEq cmpOpLess cmpOpGreaterEq cmpOpGreater>
				<spaceOpt>n <mathExp1>

cmpOpLessEq = "<="n
cmpOpLess = "<"n
cmpOpGreaterEq = ">="n
cmpOpGreater = ">"n

mathExp1 = <mathExp2> <mathExp1Tail>*
mathExp1Tail = <spaceOpt>n <mathOpAdd mathOpSub> <spaceOpt>n <mathExp2>

mathOpAdd = "+"n
mathOpSub = "-"n

mathExp2 = <concatExp> <mathExp2Tail>*
mathExp2Tail = <spaceOpt>n <mathOpMul mathOpDiv> <spaceOpt>n <concatExp>

concatExp = <operand> <concatExpTail>*
concatExpTail = <spaceOpt>n "~"n <spaceOpt>n <operand>

mathOpMul = "*"n
mathOpDiv = "/"n

operand = <floatNumber hexIntNumber binIntNumber octIntNumber decIntNumber
			doubleString singleString longString bool nil table funcCall
			variable negatedOperand negativeOperand sizeOperand parenthesisExp>

negatedOperand = "!"n <spaceOpt>n <operand>
negativeOperand = "-"n <spaceOpt>n <operand>
sizeOperand = "#"n <spaceOpt>n <operand>

floatNumber = [0-9]+ "."n [0-9]+
hexIntNumber = "0"n [Xx]n [0-9a-fA-F]+
binIntNumber = "0"n [Bb]n [01]+
octIntNumber = "0"n [0-7]+
decIntNumber = [0-9]+

doubleString = ["]n !["]* ["]n
singleString = [\']n ![\']* [\']n
longString = "<%"n !"%>"? "%>"n

bool = <true false>
true = "true"n
false = "false"n

nil = "nil"n

table = "["n <spaceOpt>n <expListOpt> <spaceOpt>n "]"n

funcCall = <token> <spaceOpt>n "("n <spaceOpt>n <expListOpt> <spaceOpt>n ")"n

variable = <token> <variableIndex>*
variableIndex = <spaceOpt>n "["n <spaceOpt>n <exp> <spaceOpt>n "]"n

space = <whitespace comment>+
spaceOpt = <space>?n
whitespace = [\\t\\x20\\r\\n]+n
comment = <singleComment multiComment>n
singleComment = "//" ![\r\n]+n
multiComment = "/*" !"*/"? "*/"n

token = [a-zA-Z_] [a-zA-Z0-9_]*
tokenList = <token> <separatedToken>*
tokenListOpt = <tokenList>?
separatedToken = <spaceOpt>n ","n <spaceOpt>n <token>

none = ""n

';
	}

	/**
	 * @see etaCompilerLangEta::processContext()
	 */
	public function processContext(etaCompilerContext $oContext)
	{
		$oFuncTable = new etaCompilerLangEtaFuncTable;

		$oCallback = new etaCompilerLangEtaTreeCallback(
			$oContext, $oFuncTable
		);

		$oContext->getSyntaxTree()->traverse($oCallback);

		$oNodeCompiler = new etaCompilerLangEtaNodeCompiler(
			$oContext, $oFuncTable
		);

		$oNodeCompiler->compileNodes($oCallback->getInstNodes());

		$oContext->addInst(new etaCompilerInst(
			etaOpcode::OP_HLT, new etaCompilerAddrNone
		));

		$oNodeCompiler->compileNodes($oCallback->getFuncNodes());
	}
}

?>
