<?php
/**
 * The MIT License (MIT)
 *
 * Copyright (c) 2014 - 2017 0x6d72 <0x6d72@gmail.com>
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

class etaCompilerLangAsmTreeCallback implements etaBnfTreeTraverseCallback
{
	/**
	 * @var etaCompilerContext
	 */
	protected $oContext;

	/**
	 * @var array
	 */
	static protected $aOpcodeMapping = array(
		// generic
		'NOP' => etaOpcode::OP_NOP,
		'HLT' => etaOpcode::OP_HLT,
		'SYS' => etaOpcode::OP_SYS,
		'SIZE' => etaOpcode::OP_SIZE,

		// math
		'ADD' => etaOpcode::OP_ADD,
		'SUB' => etaOpcode::OP_SUB,
		'MUL' => etaOpcode::OP_MUL,
		'DIV' => etaOpcode::OP_DIV,

		// comparison
		'EQ' => etaOpcode::OP_EQ,
		'LESS' => etaOpcode::OP_LESS,
		'AND' => etaOpcode::OP_AND,
		'OR' => etaOpcode::OP_OR,

		// stack
		'PUSH' => etaOpcode::OP_PUSH,
		'PUSHNIL' => etaOpcode::OP_PUSHNIL,
		'POP' => etaOpcode::OP_POP,
		'RPL' => etaOpcode::OP_RPL,

		// jumps
		'JMP' => etaOpcode::OP_JMP,
		'JT' => etaOpcode::OP_JT,
		'JF' => etaOpcode::OP_JF,
		'CALL' => etaOpcode::OP_CALL,
		'RET' => etaOpcode::OP_RET,

		// i/o
		'IN' => etaOpcode::OP_IN,
		'OUT' => etaOpcode::OP_OUT,
		'IOC' => etaOpcode::OP_IOC,

		// table
		'TAB' => etaOpcode::OP_TAB,
		'GET' => etaOpcode::OP_GET,
		'PUT' => etaOpcode::OP_PUT,
		'DEL' => etaOpcode::OP_DEL,
		'NXT' => etaOpcode::OP_NXT,
		'RES' => etaOpcode::OP_RES,

		// string
		'CONCAT' => etaOpcode::OP_CONCAT
	);

	/**
	 * @param etaCompilerContext $oContext
	 * @return void
	 */
	public function __construct(etaCompilerContext $oContext)
	{
		$this->oContext = $oContext;
	}

	/**
	 * @see etaBnfTreeTraverseCallback::handleNodePre()
	 */
	public function handleNodePre(etaBnfTreeNode $oNode, $iDepth)
	{
	}

	/**
	 * @see etaBnfTreeTraverseCallback::handleNodePost()
	 */
	public function handleNodePost(etaBnfTreeNode $oNode, $iDepth)
	{
		if(!($oNode instanceof etaBnfTreeNodeBranch))
		{
			return;
		}

		switch($oNode->getName())
		{
			case 'eqInst':

				$sName = $this->getName($oNode->getSubNode(0));

				$this->defineRefPoint($sName, new etaCompilerRefPointInst(
					$sName, $this->getValue($oNode->getSubNode(1))
				));

				break;

			case 'slotInst':

				$this->defineRefPoint(
					$this->getName($oNode->getSubNode(0)),
					$this->oContext->getRefPointVal(
						$this->getValue($oNode->getSubNode(1))
					)
				);

				break;

			case 'opcodeInst':

				$oInst = new etaCompilerInst(
					$this->getOpcode($oNode->getSubNode(1)),
					$this->getAddr($oNode->getSubNode(2))
				);

				if($oNode->getSubNode(0)->hasSubNode(0))
				{
					$this->defineRefPoint(
						$this->getName($oNode->getSubNode(0)->getSubNode(0)),
						$this->oContext->addInstRefPoint($oInst)
					);
				}
				else
				{
					$this->oContext->addInst($oInst);
				}

				break;
		}
	}

	/**
	 * @param etaBnfTreeNodeBranch $oNode
	 * @return string
	 */
	protected function getName(etaBnfTreeNodeBranch $oNode)
	{
		return $oNode->getRawValue();
	}

	/**
	 * @param etaBnfTreeNodeBranch $oNode
	 * @return mixed
	 */
	protected function getValue(etaBnfTreeNodeBranch $oNode)
	{
		switch($oNode->getName())
		{
			case 'value':
			case 'string':
			case 'number':
			case 'positiveFloatNumber':
			case 'positiveIntNumber':
			case 'intNumberRepresentation':

				return $this->getValue($oNode->getSubNode(0));

			case 'doubleString':
			case 'singleString':

				return $oNode->getRawValue();

			case 'floatNumberRepresentation':

				return (float) sprintf(
					'%s.%s',
					$oNode->getSubNode(0)->getRawValue(),
					$oNode->getSubNode(1)->getRawValue()
				);

			case 'negativeFloatNumber':

				return -1.0 * $this->getValue($oNode->getSubNode(0));

			case 'negativeIntNumber':

				return -1 * $this->getValue($oNode->getSubNode(0));

			case 'hexIntNumber':

				return hexdec($oNode->getSubNode(0)->getRawValue());

			case 'binIntNumber':

				return bindec($oNode->getSubNode(0)->getRawValue());

			case 'octIntNumber':

				return octdec($oNode->getSubNode(0)->getRawValue());

			case 'decIntNumber':

				return (int) $oNode->getRawValue();
		}

		return null;
	}

	/**
	 * @param etaBnfTreeNodeBranch $oNode
	 * @return int
	 */
	protected function getOpcode(etaBnfTreeNodeBranch $oNode)
	{
		$sOpcode = strtoupper($oNode->getRawValue());

		if(isset(self::$aOpcodeMapping[$sOpcode]))
		{
			return self::$aOpcodeMapping[$sOpcode];
		}

		throw new Exception(sprintf(
			'invalid instruction opcode "%s"', $sOpcode
		));
	}

	/**
	 * @param etaBnfTreeNodeBranch $oNode
	 * @return etaCompilerAddr
	 */
	protected function getAddr(etaBnfTreeNodeBranch $oNode)
	{
		if($oNode->getName() == 'optOpcodeInstAddr')
		{
			if(!$oNode->hasSubNode(0))
			{
				return new etaCompilerAddrNone;
			}

			$oNode = $oNode->getSubNode(0);
		}

		$oNode = $oNode->getSubNode(0);

		switch($oNode->getName())
		{
			case 'relativeDirectAddr':

				$bRelative = true;
				$bIndirect = false;

				break;

			case 'relativeIndirectAddr':

				$bRelative = true;
				$bIndirect = true;

				break;

			case 'absoluteDirectAddr':

				$bRelative = false;
				$bIndirect = false;

				break;

			case 'absoluteIndirectAddr':

				$bRelative = false;
				$bIndirect = true;

				break;
		}

		$oNode = $oNode->getSubNode(0)->getSubNode(0);

		switch($oNode->getName())
		{
			case 'token':

				return new etaCompilerAddrRefPoint(
					$this->createRefPoint($this->getName($oNode)),
					$bIndirect,
					$bRelative
				);

			case 'value':

				$mValue = $this->getValue($oNode);

				if(is_int($mValue))
				{
					return new etaCompilerAddrValue(
						$mValue, $bIndirect, $bRelative
					);
				}
				else
				{
					return new etaCompilerAddrRefPoint(
						$this->oContext->getRefPointVal($mValue),
						$bIndirect,
						$bRelative
					);
				}
		}
	}

	/**
	 * @param string $sName
	 * @return etaCompilerRefPoint
	 */
	protected function createRefPoint($sName)
	{
		try
		{
			$oRefPointProxy = $this->oContext->getRefPoint($sName);
		}
		catch(Exception $oError)
		{
			$oRefPointProxy = new etaCompilerRefPointProxy($sName);

			$this->oContext->addRefPoint($oRefPointProxy);
		}

		return $oRefPointProxy;
	}

	/**
	 * @param string $sName
	 * @param etaCompilerRefPoint $oRefPoint
	 * @return void
	 */
	protected function defineRefPoint($sName, etaCompilerRefPoint $oRefPoint)
	{
		$oRefPointProxy = $this->createRefPoint($sName);

		if($oRefPointProxy->isEmpty())
		{
			$oRefPointProxy->setRefPoint($oRefPoint);
		}
		else
		{
			throw new Exception(sprintf(
				'reference point "%s" was already defined', $sName
			));
		}
	}
}

class etaCompilerLangAsm extends etaCompilerLangDefault
{
	/**
	 * @see etaCompilerLang::getBnf()
	 */
	public function getBnf()
	{
		return '
main = <lines>

lines = <line> <separatedLine>*
separatedLine = <newLine>n <line>

line = <comment inst space emptyLine>
emptyLine = ""n

comment = <space>?n ";"n ![\r\n]*n

inst = <eqInst slotInst opcodeInst> <comment>?n

eqInst = <token> <space>n ".eq"n <space>n <negativeIntNumber positiveIntNumber>

slotInst = <token> <space>n ".slot"n <space>n <value nil>

opcodeInst = <tokenOpt> <space>n <token> <optOpcodeInstAddr>
opcodeInstAddr = <space>n <relativeDirectAddr relativeIndirectAddr
					absoluteDirectAddr absoluteIndirectAddr>
optOpcodeInstAddr = <opcodeInstAddr>?

relativeDirectAddr = "("n <space>?n "#"n <space>?n <addr> <space>?n ")"n
relativeIndirectAddr = "("n <space>?n <addr> <space>?n ")"n

absoluteDirectAddr = "#"n <space>?n <addr>
absoluteIndirectAddr = <addr>

addr = <token value>

token = [a-zA-Z] [a-zA-Z0-9_]*
tokenOpt = <token>?

space = [ \t]+n
newLine = [\r\n]+n

value = <doubleString singleString negativeFloatNumber positiveFloatNumber
		negativeIntNumber positiveIntNumber>

nil = "nil"n

doubleString = ["]n !["]* ["]n
singleString = [\']n ![\']* [\']n

negativeFloatNumber = "-"n <space>?n <floatNumberRepresentation>
positiveFloatNumber = <floatNumberRepresentation>
floatNumberRepresentation = [0-9]+ "."n [0-9]+

negativeIntNumber = "-"n <space>?n <intNumberRepresentation>
positiveIntNumber = <intNumberRepresentation>
intNumberRepresentation = <hexIntNumber binIntNumber octIntNumber decIntNumber>
hexIntNumber = "0"n [Xx]n [0-9a-fA-F]+
binIntNumber = "0"n [Bb]n [01]+
octIntNumber = "0"n [0-7]+
decIntNumber = [0-9]+
';
	}

	/**
	 * @see etaCompilerLang::processContext()
	 */
	public function processContext(etaCompilerContext $oContext)
	{
		$oContext->getSyntaxTree()->traverse(
			new etaCompilerLangAsmTreeCallback($oContext)
		);
	}
}
